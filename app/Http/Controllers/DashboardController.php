<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ExpenseStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\CashAccount;
use App\Models\CashLedger;
use App\Models\ExpenseRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $activeUnitId = $request->query('unit_id');

        // If user is restricted to a unit, enforce it
        if ($user->unit_id) {
            $activeUnitId = (string) $user->unit_id;
        }

        $unitId = $activeUnitId ? (int) $activeUnitId : null;
        $units = Unit::where('is_active', true)->get();

        // 1. Total Saldo Kas & Bank
        $cashAccountsQuery = CashAccount::withoutGlobalScope('unit_scope');
        if ($unitId) {
            $cashAccountsQuery->where('unit_id', $unitId);
        }
        $totalBalance = (int) $cashAccountsQuery->sum('current_balance');

        // 2. Total Pemasukan Bulan Ini
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $incomeQuery = Payment::withoutGlobalScope('unit_scope')
            ->where('status', PaymentStatus::COMPLETED)
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth]);
        if ($unitId) {
            $incomeQuery->where('unit_id', $unitId);
        }
        $monthlyIncome = (int) $incomeQuery->sum('total_amount');

        // 3. Total Pengeluaran Bulan Ini
        $expenseQuery = ExpenseRequest::withoutGlobalScope('unit_scope')
            ->where('status', ExpenseStatus::DISBURSED)
            ->whereBetween('disbursed_at', [$startOfMonth, $endOfMonth]);
        if ($unitId) {
            $expenseQuery->where('unit_id', $unitId);
        }
        $monthlyExpense = (int) $expenseQuery->sum('approved_amount');

        // 4. Total Tunggakan SPP / Tagihan Belum Lunas
        $arrearsQuery = Invoice::withoutGlobalScope('unit_scope')
            ->whereIn('status', [InvoiceStatus::UNPAID, InvoiceStatus::PARTIAL]);
        if ($unitId) {
            $arrearsQuery->where('unit_id', $unitId);
        }
        $totalArrears = (int) $arrearsQuery->selectRaw('SUM(net_amount - paid_amount) as total')->value('total');

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

        return view('dashboard.index', compact(
            'units',
            'unitId',
            'totalBalance',
            'monthlyIncome',
            'monthlyExpense',
            'totalArrears',
            'activeStudentsCount',
            'recentLedgers',
            'pendingExpenses'
        ));
    }
}
