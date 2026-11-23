<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Enums\ExpenseStatus;
use App\Enums\ExpenseType;
use App\Enums\LedgerType;
use App\Models\CashAccount;
use App\Models\CashLedger;
use App\Models\ExpenseRequest;
use App\Models\Unit;
use App\Services\ClosingPeriodService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class RecordDirectExpenseAction
{
    public function __construct(
        protected ClosingPeriodService $closingPeriodService
    ) {}

    /**
     * Record a direct operational or petty cash expense that is immediately disbursed and booked.
     */
    public function execute(
        int $unitId,
        int $expenseCategoryId,
        int $cashAccountId,
        string $title,
        int $amount,
        ExpenseType $expenseType = ExpenseType::OPERATIONAL,
        ?string $description = null,
        ?Carbon $transactionDate = null,
        ?string $attachmentPath = null,
        ?int $userId = null
    ): ExpenseRequest {
        $date = $transactionDate ?? Carbon::now();
        $unit = Unit::findOrFail($unitId);
        $creatorId = $userId ?? auth()->id() ?? 1;

        $this->closingPeriodService->ensurePeriodIsOpen($unitId, $date);

        return DB::transaction(function () use (
            $unit,
            $expenseCategoryId,
            $cashAccountId,
            $title,
            $description,
            $expenseType,
            $amount,
            $date,
            $attachmentPath,
            $creatorId
        ) {
            $cashAccount = CashAccount::query()
                ->withoutGlobalScope('unit_scope')
                ->where('id', $cashAccountId)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $cashAccount->current_balance < $amount) {
                throw new DomainException(
                    "Saldo akun [{$cashAccount->name}] tidak mencukupi untuk pengeluaran ini. Tersedia: ".format_rupiah($cashAccount->current_balance)
                );
            }

            $prefix = sprintf('EXP/%s/%s', $unit->code->value, $date->format('Ym'));
            $sequence = ExpenseRequest::query()
                ->withoutGlobalScope('unit_scope')
                ->where('request_number', 'like', "{$prefix}%")
                ->count() + 1;
            $requestNumber = sprintf('%s/%04d', $prefix, $sequence);

            $expenseRequest = ExpenseRequest::create([
                'unit_id' => $unit->id,
                'expense_category_id' => $expenseCategoryId,
                'cash_account_id' => $cashAccount->id,
                'request_number' => $requestNumber,
                'title' => $title,
                'description' => $description,
                'expense_type' => $expenseType,
                'requested_amount' => $amount,
                'approved_amount' => $amount,
                'transaction_date' => $date->toDateString(),
                'status' => ExpenseStatus::DISBURSED,
                'attachment_path' => $attachmentPath,
                'requested_by' => $creatorId,
                'principal_approved_by' => $creatorId,
                'principal_approved_at' => $date,
                'treasurer_approved_by' => $creatorId,
                'treasurer_approved_at' => $date,
                'disbursed_by' => $creatorId,
                'disbursed_at' => $date,
            ]);

            // Deduct cash account balance
            $balanceBefore = (int) $cashAccount->current_balance;
            $balanceAfter = $balanceBefore - $amount;
            $cashAccount->update(['current_balance' => $balanceAfter]);

            // Create cash ledger entry
            CashLedger::create([
                'unit_id' => $unit->id,
                'cash_account_id' => $cashAccount->id,
                'transaction_date' => $date,
                'type' => LedgerType::OUT,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => ExpenseRequest::class,
                'reference_id' => $expenseRequest->id,
                'description' => "Pengeluaran [{$requestNumber}]: {$title}",
                'created_by' => $creatorId,
            ]);

            return $expenseRequest;
        });
    }
}
