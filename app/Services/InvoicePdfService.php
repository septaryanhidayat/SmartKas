<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CashAccount;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoicePdfService
{
    public function generate(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load([
            'unit',
            'student.classroom',
            'feeComponent',
            'academicYear',
            'creator',
            'paymentItems.payment',
        ]);

        $bankAccounts = CashAccount::query()
            ->withoutGlobalScope('unit_scope')
            ->where('type', 'bank')
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        return Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'unit' => $invoice->unit,
            'student' => $invoice->student,
            'bankAccounts' => $bankAccounts,
        ])->setPaper('a4', 'portrait');
    }

    public function download(Invoice $invoice): Response
    {
        $pdf = $this->generate($invoice);
        $filename = 'Tagihan-'.str_replace(['/', '\\'], '-', $invoice->invoice_number).'.pdf';

        return $pdf->download($filename);
    }
}
