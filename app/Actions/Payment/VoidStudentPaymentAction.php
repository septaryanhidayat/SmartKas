<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Enums\InvoiceStatus;
use App\Enums\LedgerType;
use App\Enums\PaymentStatus;
use App\Models\CashAccount;
use App\Models\CashLedger;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\ClosingPeriodService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;

class VoidStudentPaymentAction
{
    public function __construct(
        protected ClosingPeriodService $closingPeriodService
    ) {}

    /**
     * Cancel/Void a completed payment, restoring invoice balances and recording reversal in cash ledger.
     */
    public function execute(
        int $paymentId,
        string $voidReason,
        ?int $voidedByUserId = null
    ): Payment {
        $now = Carbon::now();
        $voidUserId = $voidedByUserId ?? auth()->id() ?? 1;

        $payment = Payment::with(['items.invoice', 'student', 'cashAccount'])
            ->withoutGlobalScope('unit_scope')
            ->findOrFail($paymentId);

        if ($payment->status === PaymentStatus::VOID) {
            throw new DomainException('Kwitansi pembayaran ini sudah dibatalkan sebelumnya.');
        }

        // Validate closing period for original payment date & current reversal date
        $this->closingPeriodService->ensurePeriodIsOpen($payment->unit_id, $payment->payment_date);
        $this->closingPeriodService->ensurePeriodIsOpen($payment->unit_id, $now);

        return DB::transaction(function () use ($payment, $voidReason, $voidUserId, $now) {
            // 1. Lock CashAccount
            $cashAccount = CashAccount::query()
                ->withoutGlobalScope('unit_scope')
                ->where('id', $payment->cash_account_id)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Lock & reverse invoice balances
            foreach ($payment->items as $item) {
                /** @var Invoice $invoice */
                $invoice = Invoice::query()
                    ->withoutGlobalScope('unit_scope')
                    ->where('id', $item->invoice_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $restoredPaid = max(0, (int) $invoice->paid_amount - (int) $item->amount);
                $restoredStatus = $restoredPaid === 0
                    ? InvoiceStatus::UNPAID
                    : InvoiceStatus::PARTIAL;

                $invoice->update([
                    'paid_amount' => $restoredPaid,
                    'status' => $restoredStatus,
                ]);
            }

            // 3. Deduct CashAccount balance
            $balanceBefore = (int) $cashAccount->current_balance;
            $balanceAfter = $balanceBefore - (int) $payment->total_amount;
            $cashAccount->update(['current_balance' => $balanceAfter]);

            // 4. Record reversing entry in CashLedger
            CashLedger::create([
                'unit_id' => $payment->unit_id,
                'cash_account_id' => $cashAccount->id,
                'transaction_date' => $now,
                'type' => LedgerType::OUT,
                'amount' => (int) $payment->total_amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => Payment::class,
                'reference_id' => $payment->id,
                'description' => "VOID / Pembatalan Kwitansi {$payment->receipt_number}: {$voidReason}",
                'created_by' => $voidUserId,
            ]);

            // 5. Mark Payment as void
            $payment->update([
                'status' => PaymentStatus::VOID,
                'void_reason' => $voidReason,
                'voided_by' => $voidUserId,
                'voided_at' => $now,
            ]);

            return $payment;
        });
    }
}
