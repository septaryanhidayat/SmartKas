<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReceiptPdfService
{
    /**
     * Generate PDF instance for a payment receipt.
     */
    public function generate(Payment $payment): \Barryvdh\DomPDF\PDF
    {
        $payment->load([
            'unit',
            'student.classroom',
            'cashAccount',
            'creator',
            'items.invoice.feeComponent',
        ]);

        return Pdf::loadView('pdf.receipt', [
            'payment' => $payment,
            'unit' => $payment->unit,
            'student' => $payment->student,
            'items' => $payment->items,
        ])->setPaper('a5', 'landscape');
    }

    /**
     * Stream or download PDF in HTTP response.
     */
    public function download(Payment $payment): Response
    {
        $pdf = $this->generate($payment);
        $filename = 'Kwitansi-' . str_replace('/', '-', $payment->receipt_number) . '.pdf';

        return $pdf->download($filename);
    }
}
