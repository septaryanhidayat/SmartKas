<?php

declare(strict_types=1);

namespace App\Actions\Expense;

use App\Enums\ExpenseStatus;
use App\Enums\LedgerType;
use App\Models\CashAccount;
use App\Models\CashLedger;
use App\Models\ExpenseRequest;
use App\Services\ClosingPeriodService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class DisburseExpenseAction
{
    public function __construct(
        protected ClosingPeriodService $closingPeriodService
    ) {}

    /**
     * Disburse funds for an approved expense request and write to cash ledger.
     */
    public function execute(
        ExpenseRequest $request,
        ?int $cashAccountId = null,
        ?Carbon $disbursementDate = null,
        ?int $disbursedByUserId = null
    ): ExpenseRequest {
        if ($request->status !== ExpenseStatus::APPROVED_TREASURER) {
            throw new DomainException("Hanya pengajuan yang telah disetujui Bendahara Yayasan yang dapat dicairkan.");
        }

        $date = $disbursementDate ?? Carbon::now();
        $targetAccountId = $cashAccountId ?? $request->cash_account_id;

        if (! $targetAccountId) {
            throw new DomainException("Akun Kas/Bank pencairan dana harus ditentukan.");
        }

        $userId = $disbursedByUserId ?? auth()->id() ?? 1;

        // Validate closing period
        $this->closingPeriodService->ensurePeriodIsOpen($request->unit_id, $date);

        return DB::transaction(function () use ($request, $targetAccountId, $date, $userId) {
            $cashAccount = CashAccount::query()
                ->withoutGlobalScope('unit_scope')
                ->where('id', $targetAccountId)
                ->lockForUpdate()
                ->firstOrFail();

            $amountToDisburse = (int) ($request->approved_amount ?? $request->requested_amount);

            if ((int) $cashAccount->current_balance < $amountToDisburse) {
                throw new DomainException(
                    "Saldo akun [{$cashAccount->name}] tidak mencukupi (Tersedia: " .
                    format_rupiah($cashAccount->current_balance) .
                    ", Dibutuhkan: " .
                    format_rupiah($amountToDisburse) . ")."
                );
            }

            // Deduct cash account
            $balanceBefore = (int) $cashAccount->current_balance;
            $balanceAfter = $balanceBefore - $amountToDisburse;
            $cashAccount->update(['current_balance' => $balanceAfter]);

            // Update ExpenseRequest
            $request->update([
                'status' => ExpenseStatus::DISBURSED,
                'cash_account_id' => $cashAccount->id,
                'disbursed_by' => $userId,
                'disbursed_at' => $date,
            ]);

            // Record in CashLedger (Buku Kas Umum)
            CashLedger::create([
                'unit_id' => $request->unit_id,
                'cash_account_id' => $cashAccount->id,
                'transaction_date' => $date,
                'type' => LedgerType::OUT,
                'amount' => $amountToDisburse,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => ExpenseRequest::class,
                'reference_id' => $request->id,
                'description' => "Pengeluaran Dana [{$request->request_number}]: {$request->title}",
                'created_by' => $userId,
            ]);

            return $request;
        });
    }
}
