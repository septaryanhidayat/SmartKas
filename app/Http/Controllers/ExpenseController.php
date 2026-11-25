<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Expense\ApproveExpenseRequestAction;
use App\Actions\Expense\DisburseExpenseAction;
use App\Actions\Expense\RecordDirectExpenseAction;
use App\Actions\Expense\SubmitExpenseRequestAction;
use App\Enums\ExpenseType;
use App\Models\CashAccount;
use App\Models\ExpenseCategory;
use App\Models\ExpenseRequest;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $unitId = $user->unit_id ?? ($request->query('unit_id') ? (int) $request->query('unit_id') : null);
        $status = $request->query('status');

        $units = Unit::where('is_active', true)->get();

        $query = ExpenseRequest::with(['unit', 'expenseCategory', 'cashAccount', 'requestedBy'])
            ->orderBy('created_at', 'desc');

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $expenses = $query->paginate(15)->withQueryString();

        return view('expenses.index', compact('expenses', 'units', 'unitId', 'status'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $unitId = $user->unit_id;

        $units = Unit::where('is_active', true)
            ->when($unitId, fn ($q) => $q->where('id', $unitId))
            ->when(! $unitId, fn ($q) => $q->where('level', '!=', 'foundation'))
            ->orderBy('id')
            ->get();
        $categories = ExpenseCategory::where('is_active', true)->get();

        $cashAccountsQuery = CashAccount::where('is_active', true);
        if ($unitId) {
            $cashAccountsQuery->where('unit_id', $unitId);
        }
        $cashAccounts = $cashAccountsQuery->get();

        return view('expenses.create', compact('units', 'categories', 'cashAccounts', 'unitId'));
    }

    public function store(
        Request $request,
        SubmitExpenseRequestAction $submitAction,
        RecordDirectExpenseAction $directAction
    ): RedirectResponse {
        $validated = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'expense_type' => ['required', 'string'],
            'amount' => ['required', 'integer', 'min:1000'],
            'transaction_date' => ['nullable', 'date'],
            'cash_account_id' => ['required_if:is_direct,1', 'nullable', 'exists:cash_accounts,id'],
            'is_direct' => ['nullable', 'boolean'],
        ]);

        abort_unless(auth()->user()->canAccessUnit((int) $validated['unit_id']), 403, 'Anda tidak memiliki akses untuk unit sekolah tersebut.');

        $date = ! empty($validated['transaction_date']) ? Carbon::parse($validated['transaction_date']) : Carbon::now();
        $expenseType = ExpenseType::from($validated['expense_type']);

        try {
            // Direct expense recording (immediately disbursed) vs Multi-level proposal
            if (! empty($validated['is_direct']) && ! empty($validated['cash_account_id'])) {
                $expense = $directAction->execute(
                    unitId: (int) $validated['unit_id'],
                    expenseCategoryId: (int) $validated['expense_category_id'],
                    cashAccountId: (int) $validated['cash_account_id'],
                    title: $validated['title'],
                    amount: (int) $validated['amount'],
                    expenseType: $expenseType,
                    description: $validated['description'] ?? null,
                    transactionDate: $date,
                    userId: auth()->id()
                );

                return redirect()->route('expenses.show', $expense)->with('success', 'Biaya operasional langsung berhasil dicatat dan dipotong dari kas!');
            }

            $expense = $submitAction->execute(
                unitId: (int) $validated['unit_id'],
                expenseCategoryId: (int) $validated['expense_category_id'],
                title: $validated['title'],
                requestedAmount: (int) $validated['amount'],
                expenseType: $expenseType,
                description: $validated['description'] ?? null,
                transactionDate: $date,
                cashAccountId: ! empty($validated['cash_account_id']) ? (int) $validated['cash_account_id'] : null,
                submitImmediately: true,
                requestedByUserId: auth()->id()
            );

            return redirect()->route('expenses.show', $expense)->with('success', 'Pengajuan dana berhasil diajukan untuk proses approval!');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(ExpenseRequest $expense): View
    {
        $expense->load([
            'unit',
            'expenseCategory',
            'cashAccount',
            'requestedBy',
            'principalApprovedBy',
            'treasurerApprovedBy',
            'disbursedBy',
        ]);

        $cashAccounts = CashAccount::where('is_active', true)->get();

        return view('expenses.show', compact('expense', 'cashAccounts'));
    }

    public function approvePrincipal(ExpenseRequest $expense, ApproveExpenseRequestAction $action): RedirectResponse
    {
        abort_unless(auth()->user()->can('approve-expense-principal'), 403);

        try {
            $action->approveByPrincipal($expense, auth()->id());

            return back()->with('success', 'Pengajuan dana telah disetujui Kepala Sekolah (Approval Tahap 1)!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function approveTreasurer(Request $request, ExpenseRequest $expense, ApproveExpenseRequestAction $action): RedirectResponse
    {
        abort_unless(auth()->user()->can('approve-expense-treasurer'), 403);

        $validated = $request->validate([
            'approved_amount' => ['nullable', 'integer', 'min:1'],
            'cash_account_id' => ['nullable', 'exists:cash_accounts,id'],
        ]);

        try {
            $action->approveByTreasurer(
                request: $expense,
                approvedAmount: ! empty($validated['approved_amount']) ? (int) $validated['approved_amount'] : null,
                cashAccountId: ! empty($validated['cash_account_id']) ? (int) $validated['cash_account_id'] : null,
                approverUserId: auth()->id()
            );

            return back()->with('success', 'Pengajuan dana telah disetujui Bendahara Yayasan (Approval Tahap 2)!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function disburse(Request $request, ExpenseRequest $expense, DisburseExpenseAction $action): RedirectResponse
    {
        abort_unless(auth()->user()->can('disburse-expense'), 403);

        $validated = $request->validate([
            'cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'disbursement_date' => ['nullable', 'date'],
        ]);

        $date = ! empty($validated['disbursement_date']) ? Carbon::parse($validated['disbursement_date']) : Carbon::now();

        try {
            $action->execute(
                request: $expense,
                cashAccountId: (int) $validated['cash_account_id'],
                disbursementDate: $date,
                disbursedByUserId: auth()->id()
            );

            return back()->with('success', 'Dana pengeluaran telah berhasil dicairkan dan dibukukan di Kas Umum!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, ExpenseRequest $expense, ApproveExpenseRequestAction $action): RedirectResponse
    {
        abort_unless(
            auth()->user()->can('approve-expense-principal') || auth()->user()->can('approve-expense-treasurer'),
            403
        );
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        try {
            $action->reject($expense, $validated['rejection_reason']);

            return back()->with('success', 'Pengajuan dana telah ditolak.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
