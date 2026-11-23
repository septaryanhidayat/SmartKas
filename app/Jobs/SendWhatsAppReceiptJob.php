<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Payment;
use App\Services\WhatsAppGatewayService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendWhatsAppReceiptJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $paymentId
    ) {}

    public function handle(WhatsAppGatewayService $whatsappGateway): void
    {
        $payment = Payment::with(['student.classroom', 'unit', 'items.invoice.feeComponent'])
            ->withoutGlobalScope('unit_scope')
            ->find($this->paymentId);

        if (! $payment) {
            Log::warning("SendWhatsAppReceiptJob: Payment ID {$this->paymentId} not found.");

            return;
        }

        $whatsappGateway->sendReceipt($payment);
    }
}
