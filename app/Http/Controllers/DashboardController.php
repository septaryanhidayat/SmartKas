<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ExpenseStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\CashAccount;
use App\Models\CashLedger;
use App\Models\ExpenseRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Unit;
use App\Models\User;
use App\Services\ClosingPeriodService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ClosingPeriodService $closingService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $activeUnitId = $request->query('unit_id');

        // Enforce strict unit isolation for unit-bound users
        if ($user->unit_id) {
            $activeUnitId = (string) $user->unit_id;
        }

        $unitId = $activeUnitId ? (int) $activeUnitId : null;

        // Filter selector units: all school units for Yayasan-level users, or only user's own unit
        $unitsQuery = Unit::where('is_active', true)->where('level', '!=', 'foundation')->orderBy('id');
        if ($user->unit_id) {
            $unitsQuery->where('id', $user->unit_id);
        }
        $units = $unitsQuery->get();

        // 1. Total Saldo Kas & Bank
        $cashAccountsQuery = CashAccount::withoutGlobalScope('unit_scope')->where('is_active', true);
        if ($unitId) {
            $cashAccountsQuery->where('unit_id', $unitId);
        }
        $cashAccounts = $cashAccountsQuery->orderBy('type')->orderBy('id')->get();
        $totalBalance = (int) $cashAccounts->sum('current_balance');

        // 2. Total Pemasukan Bulan Ini & Hari Ini
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $startOfDay = Carbon::now()->startOfDay();
        $endOfDay = Carbon::now()->endOfDay();

        $incomeQuery = Payment::withoutGlobalScope('unit_scope')
            ->where('status', PaymentStatus::COMPLETED)
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth]);
        if ($unitId) {
            $incomeQuery->where('unit_id', $unitId);
        }
        $monthlyIncome = (int) $incomeQuery->sum('total_amount');

        $todayIncomeQuery = Payment::withoutGlobalScope('unit_scope')
            ->where('status', PaymentStatus::COMPLETED)
            ->whereBetween('payment_date', [$startOfDay, $endOfDay]);
        if ($unitId) {
            $todayIncomeQuery->where('unit_id', $unitId);
        }
        $todayIncome = (int) $todayIncomeQuery->sum('total_amount');

        // 3. Total Pengeluaran Bulan Ini & Hari Ini
        $expenseQuery = ExpenseRequest::withoutGlobalScope('unit_scope')
            ->where('status', ExpenseStatus::DISBURSED)
            ->whereBetween('disbursed_at', [$startOfMonth, $endOfMonth]);
        if ($unitId) {
            $expenseQuery->where('unit_id', $unitId);
        }
        $monthlyExpense = (int) $expenseQuery->sum('approved_amount');

        $todayExpenseQuery = ExpenseRequest::withoutGlobalScope('unit_scope')
            ->where('status', ExpenseStatus::DISBURSED)
            ->whereBetween('disbursed_at', [$startOfDay, $endOfDay]);
        if ($unitId) {
            $todayExpenseQuery->where('unit_id', $unitId);
        }
        $todayExpense = (int) $todayExpenseQuery->sum('approved_amount');

        // 4. Total Tunggakan SPP / Tagihan Belum Lunas
        $arrearsQuery = Invoice::withoutGlobalScope('unit_scope')
            ->whereIn('status', [InvoiceStatus::UNPAID, InvoiceStatus::PARTIAL]);
        if ($unitId) {
            $arrearsQuery->where('unit_id', $unitId);
        }
        $totalArrears = (int) $arrearsQuery->selectRaw('SUM(net_amount - paid_amount) as total')->value('total');
        $arrearsStudentsCount = (clone $arrearsQuery)->distinct('student_id')->count('student_id');

        // 5. Total Siswa Aktif
        $studentsCountQuery = Student::withoutGlobalScope('unit_scope')->where('status', 'active');
        if ($unitId) {
            $studentsCountQuery->where('unit_id', $unitId);
        }
        $activeStudentsCount = $studentsCountQuery->count();

        // 6. Mutasi Transaksi Terakhir
        $recentLedgersQuery = CashLedger::withoutGlobalScope('unit_scope')
            ->with(['unit', 'cashAccount', 'creator'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(8);
        if ($unitId) {
            $recentLedgersQuery->where('unit_id', $unitId);
        }
        $recentLedgers = $recentLedgersQuery->get();

        // 7. Pengajuan Dana Menunggu Persetujuan
        $pendingExpensesQuery = ExpenseRequest::withoutGlobalScope('unit_scope')
            ->with(['unit', 'expenseCategory', 'requestedBy'])
            ->whereIn('status', [ExpenseStatus::SUBMITTED, ExpenseStatus::APPROVED_PRINCIPAL])
            ->orderBy('created_at', 'desc')
            ->limit(5);
        if ($unitId) {
            $pendingExpensesQuery->where('unit_id', $unitId);
        }
        $pendingExpenses = $pendingExpensesQuery->get();

        // 8. Breakdown per Unit Sekolah (strictly scoped for unit users)
        $unitSummaries = [];
        $summaryUnits = $unitId ? $units->where('id', $unitId) : $units;

        $unitAdminsMap = User::whereNotNull('unit_id')
            ->role(UserRole::FINANCE_ADMIN->value)
            ->pluck('name', 'unit_id');

        foreach ($summaryUnits as $u) {
            $uStudentCount = Student::withoutGlobalScope('unit_scope')->where('unit_id', $u->id)->where('status', 'active')->count();

            $uMonthlyBilled = (int) Invoice::withoutGlobalScope('unit_scope')
                ->where('unit_id', $u->id)
                ->where('billing_month', Carbon::now()->month)
                ->where('billing_year', Carbon::now()->year)
                ->sum('net_amount');

            $uMonthlyPaid = (int) Payment::withoutGlobalScope('unit_scope')
                ->where('unit_id', $u->id)
                ->where('status', PaymentStatus::COMPLETED)
                ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
                ->sum('total_amount');

            $uArrears = (int) Invoice::withoutGlobalScope('unit_scope')
                ->where('unit_id', $u->id)
                ->whereIn('status', [InvoiceStatus::UNPAID, InvoiceStatus::PARTIAL])
                ->selectRaw('SUM(net_amount - paid_amount) as total')
                ->value('total');

            $collectionRate = $uMonthlyBilled > 0 ? min(100.0, round(($uMonthlyPaid / $uMonthlyBilled) * 100, 1)) : 0.0;

            $unitSummaries[] = [
                'unit' => $u,
                'admin_name' => $unitAdminsMap[$u->id] ?? 'Admin Unit',
                'student_count' => $uStudentCount,
                'monthly_billed' => $uMonthlyBilled,
                'monthly_paid' => $uMonthlyPaid,
                'arrears' => $uArrears,
                'collection_rate' => $collectionRate,
            ];
        }

        // 9. Status Tutup Buku Bulan Ini
        $isPeriodClosed = $this->closingService->isPeriodClosed($unitId, Carbon::now());

        return view('dashboard.index', compact(
            'units',
            'unitId',
            'totalBalance',
            'monthlyIncome',
            'monthlyExpense',
            'todayIncome',
            'todayExpense',
            'totalArrears',
            'arrearsStudentsCount',
            'activeStudentsCount',
            'recentLedgers',
            'pendingExpenses',
            'cashAccounts',
            'unitSummaries',
            'isPeriodClosed'
        ));
    }
}
