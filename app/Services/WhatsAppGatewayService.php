<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppGatewayService
{
    /**
     * Send payment receipt message to student's parent WhatsApp.
     */
    public function sendReceipt(Payment $payment): bool
    {
        $student = $payment->student;
        $unit = $payment->unit;
        $phone = $student->parent_whatsapp;

        if (empty($phone)) {
            Log::info("WA Receipt Skipped: Siswa {$student->name} tidak memiliki nomor WhatsApp orang tua.");

            return false;
        }

        // Standardize Indonesian phone number to international format 628...
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62'.substr($cleanPhone, 1);
        }

        $receiptUrl = route('receipts.download', ['payment' => $payment->id]);
        $itemsList = '';
        foreach ($payment->items as $idx => $item) {
            $itemsList .= sprintf("\n%d. %s: %s", $idx + 1, $item->invoice->feeComponent->name, format_rupiah($item->amount));
        }

        $foundationName = app_setting('foundation_name', 'Yayasan Generasi Robbani Sumatera Selatan');
        $appName = app_setting('app_name', 'SmartKas');

        $message = <<<TEXT
*BUKTI PEMBAYARAN - {$unit->name}*
{$foundationName}
---------------------------------------------
Assalamu'alaikum Wr. Wb.
Yth. Orang Tua / Wali dari:
*Nama Siswa:* {$student->name}
*NIS:* {$student->nis}
*Kelas:* {$student->classroom?->name}

Alhamdulillah, pembayaran telah kami terima:
*No. Kwitansi:* `{$payment->receipt_number}`
*Tanggal:* {$payment->payment_date->translatedFormat('d F Y H:i')}
*Metode:* {$payment->payment_method->label()}

*Rincian Pembayaran:*{$itemsList}

*TOTAL PEMBAYARAN:* *{$payment->total_amount_rupiah}*
({$payment->terbilang_amount} Rupiah)

Unduh e-Kwitansi PDF resmi:
{$receiptUrl}

Jazakumullahu Khairan Katsiran atas kepercayaannya menitipkan pendidikan putra/putri di sekolah kami.
_Pesan otomatis {$appName} • {$foundationName}_
TEXT;

        // Check if an external WhatsApp API provider is configured (Fonnte/Wablas/WA Gateway)
        $apiUrl = config('services.whatsapp.api_url');
        $apiKey = config('services.whatsapp.api_key');

        if ($apiUrl && $apiKey) {
            try {
                $response = Http::timeout(10)->withHeaders([
                    'Authorization' => $apiKey,
                ])->post($apiUrl, [
                    'target' => $cleanPhone,
                    'message' => $message,
                ]);

                $success = $response->successful();
                if ($success) {
                    $payment->update(['whatsapp_sent_at' => now()]);
                }

                return $success;
            } catch (\Throwable $e) {
                Log::error('WA Gateway Error: '.$e->getMessage(), ['payment_id' => $payment->id]);

                return false;
            }
        }

        // Development/Local mock fallback: Log to Laravel log
        Log::info("WA Receipt [MOCK SENT] to {$cleanPhone}:\n".$message);
        $payment->update(['whatsapp_sent_at' => now()]);

        return true;
    }
}
