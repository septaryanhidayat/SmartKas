<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Enums\InvoiceStatus;
use App\Enums\LedgerType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Jobs\SendWhatsAppReceiptJob;
use App\Models\CashAccount;
use App\Models\CashLedger;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Student;
use App\Services\ClosingPeriodService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecordStudentPaymentAction
{
    public function __construct(
        protected ClosingPeriodService $closingPeriodService
    ) {}

    /**
     * Record a student payment (full, partial/installment, or multi-invoice checkout).
     *
     * @param  array<array{invoice_id: int, amount: int}>  $items
     */
    public function execute(
        int $studentId,
        int $cashAccountId,
        PaymentMethod $paymentMethod,
        array $items,
        ?Carbon $paymentDate = null,
        ?string $referenceNumber = null,
        ?string $proofPath = null,
        ?int $createdByUserId = null
    ): Payment {
        if (empty($items)) {
            throw new InvalidArgumentException('Minimal harus memilih 1 tagihan untuk dibayar.');
        }

        $date = $paymentDate ?? Carbon::now();
        $student = Student::with('unit')->findOrFail($studentId);
        $creatorId = $createdByUserId ?? auth()->id() ?? 1;

        // 1. Validate closing period
        $this->closingPeriodService->ensurePeriodIsOpen($student->unit_id, $date);

        return DB::transaction(function () use (
            $student,
            $cashAccountId,
            $paymentMethod,
            $items,
            $date,
            $referenceNumber,
            $proofPath,
            $creatorId
        ) {
            // 2. Lock CashAccount
            $cashAccount = CashAccount::query()
                ->withoutGlobalScope('unit_scope')
                ->where('id', $cashAccountId)
                ->lockForUpdate()
                ->firstOrFail();

            $totalAmount = 0;
            $validatedAllocations = [];

            // 3. Lock & validate all invoices
            foreach ($items as $item) {
                $invoiceId = (int) $item['invoice_id'];
                $amount = (int) $item['amount'];

                if ($amount <= 0) {
                    throw new InvalidArgumentException("Nominal pembayaran untuk tagihan ID {$invoiceId} harus lebih besar dari 0.");
                }

                $invoice = Invoice::query()
                    ->withoutGlobalScope('unit_scope')
                    ->where('id', $invoiceId)
                    ->where('student_id', $student->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($invoice->status === InvoiceStatus::PAID) {
                    throw new DomainException("Tagihan [{$invoice->invoice_number}] sudah lunas.");
                }

                if ($invoice->status === InvoiceStatus::VOID) {
                    throw new DomainException("Tagihan [{$invoice->invoice_number}] telah dibatalkan (void).");
                }

                $remaining = (int) $invoice->net_amount - (int) $invoice->paid_amount;
                if ($amount > $remaining) {
                    throw new DomainException(
                        "Nominal pembayaran (Rp " . number_format($amount, 0, ',', '.') . ") melebihi sisa tagihan [{$invoice->invoice_number}] (Rp " . number_format($remaining, 0, ',', '.') . ")."
                    );
                }

                $totalAmount += $amount;
                $validatedAllocations[] = [
                    'invoice' => $invoice,
                    'amount' => $amount,
                ];
            }

            // 4. Generate unique receipt number: KW/{UNIT_CODE}/{YYYYMM}/{SEQ}
            $unitCode = $student->unit->code->value;
            $yearMonth = $date->format('Ym');
            $prefix = "KW/{$unitCode}/{$yearMonth}";
            $countThisMonth = Payment::query()
                ->withoutGlobalScope('unit_scope')
                ->where('receipt_number', 'like', "{$prefix}%")
                ->count() + 1;
            $receiptNumber = sprintf('%s/%04d', $prefix, $countThisMonth);

            // 5. Create Payment record
            $payment = Payment::create([
                'unit_id' => $student->unit_id,
                'student_id' => $student->id,
                'cash_account_id' => $cashAccount->id,
                'receipt_number' => $receiptNumber,
                'payment_date' => $date,
                'payment_method' => $paymentMethod,
                'total_amount' => $totalAmount,
                'reference_number' => $referenceNumber,
                'proof_path' => $proofPath,
                'status' => PaymentStatus::COMPLETED,
                'created_by' => $creatorId,
            ]);

            // 6. Create PaymentItems and update Invoices
            foreach ($validatedAllocations as $alloc) {
                /** @var Invoice $invoice */
                $invoice = $alloc['invoice'];
                $allocationAmount = $alloc['amount'];

                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $allocationAmount,
                ]);

                $newPaidAmount = (int) $invoice->paid_amount + $allocationAmount;
                $newStatus = $newPaidAmount >= (int) $invoice->net_amount
                    ? InvoiceStatus::PAID
                    : InvoiceStatus::PARTIAL;

                $invoice->update([
                    'paid_amount' => $newPaidAmount,
                    'status' => $newStatus,
                ]);
            }

            // 7. Update CashAccount balance
            $balanceBefore = (int) $cashAccount->current_balance;
            $balanceAfter = $balanceBefore + $totalAmount;
            $cashAccount->update(['current_balance' => $balanceAfter]);

            // 8. Record in CashLedger (Buku Kas Umum)
            CashLedger::create([
                'unit_id' => $student->unit_id,
                'cash_account_id' => $cashAccount->id,
                'transaction_date' => $date,
                'type' => LedgerType::IN,
                'amount' => $totalAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => Payment::class,
                'reference_id' => $payment->id,
                'description' => "Penerimaan Pembayaran Kwitansi {$receiptNumber} - Siswa: {$student->name} (NIS: {$student->nis})",
                'created_by' => $creatorId,
            ]);

            // 9. Dispatch WhatsApp notification job if student has parent whatsapp
            if (! empty($student->parent_whatsapp)) {
                dispatch(new SendWhatsAppReceiptJob($payment->id));
            }

            return $payment;
        });
    }
}
