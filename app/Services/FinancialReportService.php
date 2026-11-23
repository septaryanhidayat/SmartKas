<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ExpenseStatus;
use App\Enums\InvoiceStatus;
use App\Enums\LedgerType;
use App\Enums\PaymentStatus;
use App\Models\CashAccount;
use App\Models\CashLedger;
use App\Models\FeeComponent;
use App\Models\Invoice;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    /**
     * Buku Kas Umum (Cash Flow & Mutation Ledger).
     *
     * @return array{
     *     cash_account: ?CashAccount,
     *     opening_balance: int,
     *     total_in: int,
     *     total_out: int,
     *     closing_balance: int,
     *     mutations: Collection<int, CashLedger>
     * }
     */
    public function getCashLedgerReport(
        ?int $cashAccountId,
        ?int $unitId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $account = $cashAccountId ? CashAccount::withoutGlobalScope('unit_scope')->find($cashAccountId) : null;

        // Base query for ledgers in period
        $query = CashLedger::withoutGlobalScope('unit_scope')
            ->whereBetween('transaction_date', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');

        if ($cashAccountId) {
            $query->where('cash_account_id', $cashAccountId);
        } elseif ($unitId) {
            $query->where('unit_id', $unitId);
        }

        $mutations = $query->with(['cashAccount', 'unit', 'creator'])->get();

        // Calculate opening balance before startDate
        $openingBalanceQuery = CashLedger::withoutGlobalScope('unit_scope')
            ->where('transaction_date', '<', $startDate->copy()->startOfDay());

        if ($cashAccountId) {
            $openingBalanceQuery->where('cash_account_id', $cashAccountId);
        } elseif ($unitId) {
            $openingBalanceQuery->where('unit_id', $unitId);
        }

        $priorIn = (int) (clone $openingBalanceQuery)->where('type', LedgerType::IN)->sum('amount');
        $priorOut = (int) (clone $openingBalanceQuery)->where('type', LedgerType::OUT)->sum('amount');
        $openingBalance = $priorIn - $priorOut;

        $totalIn = (int) $mutations->where('type', LedgerType::IN)->sum('amount');
        $totalOut = (int) $mutations->where('type', LedgerType::OUT)->sum('amount');
        $closingBalance = $openingBalance + $totalIn - $totalOut;

        return [
            'cash_account' => $account,
            'opening_balance' => $openingBalance,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'closing_balance' => $closingBalance,
            'mutations' => $mutations,
        ];
    }

    /**
     * Laporan Surplus / Defisit (Laba Rugi).
     *
     * @return array{
     *     unit: ?Unit,
     *     start_date: Carbon,
     *     end_date: Carbon,
     *     incomes: Collection,
     *     total_income: int,
     *     expenses: Collection,
     *     total_expense: int,
     *     surplus_deficit: int
     * }
     */
    public function getIncomeExpenseReport(
        ?int $unitId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $unit = $unitId ? Unit::find($unitId) : null;

        // Incomes: group payments received by fee component
        $incomesQuery = DB::table('payment_items')
            ->join('payments', 'payments.id', '=', 'payment_items.payment_id')
            ->join('invoices', 'invoices.id', '=', 'payment_items.invoice_id')
            ->join('fee_components', 'fee_components.id', '=', 'invoices.fee_component_id')
            ->where('payments.status', PaymentStatus::COMPLETED->value)
            ->whereNull('payments.deleted_at')
            ->whereBetween('payments.payment_date', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->select(
                'fee_components.id',
                'fee_components.code',
                'fee_components.name',
                DB::raw('SUM(payment_items.amount) as total_amount'),
                DB::raw('COUNT(DISTINCT payments.id) as payment_count')
            )
            ->groupBy('fee_components.id', 'fee_components.code', 'fee_components.name')
            ->orderBy('total_amount', 'desc');

        if ($unitId) {
            $incomesQuery->where('payments.unit_id', $unitId);
        }

        $incomes = $incomesQuery->get();
        $totalIncome = (int) $incomes->sum('total_amount');

        // Expenses: group disbursed expenses by category
        $expensesQuery = DB::table('expense_requests')
            ->join('expense_categories', 'expense_categories.id', '=', 'expense_requests.expense_category_id')
            ->where('expense_requests.status', ExpenseStatus::DISBURSED->value)
            ->whereNull('expense_requests.deleted_at')
            ->whereBetween('expense_requests.disbursed_at', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->select(
                'expense_categories.id',
                'expense_categories.code',
                'expense_categories.name',
                DB::raw('SUM(expense_requests.approved_amount) as total_amount'),
                DB::raw('COUNT(expense_requests.id) as request_count')
            )
            ->groupBy('expense_categories.id', 'expense_categories.code', 'expense_categories.name')
            ->orderBy('total_amount', 'desc');

        if ($unitId) {
            $expensesQuery->where('expense_requests.unit_id', $unitId);
        }

        $expenses = $expensesQuery->get();
        $totalExpense = (int) $expenses->sum('total_amount');

        return [
            'unit' => $unit,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'incomes' => $incomes,
            'total_income' => $totalIncome,
            'expenses' => $expenses,
            'total_expense' => $totalExpense,
            'surplus_deficit' => $totalIncome - $totalExpense,
        ];
    }

    /**
     * Rekap Penerimaan per Komponen Biaya (Account Receivable summary).
     */
    public function getFeeRecapReport(
        ?int $unitId,
        ?int $academicYearId,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        $query = FeeComponent::withoutGlobalScope('unit_scope')
            ->with(['unit'])
            ->withCount([
                'invoices as total_invoices_count',
                'invoices as paid_invoices_count' => fn ($q) => $q->where('status', InvoiceStatus::PAID),
                'invoices as unpaid_invoices_count' => fn ($q) => $q->whereIn('status', [InvoiceStatus::UNPAID, InvoiceStatus::PARTIAL]),
            ])
            ->withSum('invoices as total_billed', 'net_amount')
            ->withSum('invoices as total_paid', 'paid_amount');

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return $query->get()->map(function (FeeComponent $comp) {
            $billed = (int) ($comp->total_billed ?? 0);
            $paid = (int) ($comp->total_paid ?? 0);
            $outstanding = max(0, $billed - $paid);
            $percentage = $billed > 0 ? round(($paid / $billed) * 100, 1) : 0;

            $comp->total_outstanding = $outstanding;
            $comp->collection_rate = $percentage;

            return $comp;
        });
    }

    /**
     * Dashboard & Laporan Tunggakan Siswa (Arrears / Piutang SPP).
     */
    public function getArrearsList(
        ?int $unitId = null,
        ?int $classroomId = null,
        ?int $feeComponentId = null,
        ?int $minMonthsOverdue = null
    ): Collection {
        $query = Invoice::withoutGlobalScope('unit_scope')
            ->whereIn('status', [InvoiceStatus::UNPAID, InvoiceStatus::PARTIAL])
            ->with(['student.classroom', 'feeComponent', 'unit'])
            ->orderBy('due_date', 'asc');

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        if ($feeComponentId) {
            $query->where('fee_component_id', $feeComponentId);
        }

        if ($classroomId) {
            $query->whereHas('student', fn ($q) => $q->where('classroom_id', $classroomId));
        }

        return $query->get();
    }
}
