<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi Pembayaran - {{ $payment->receipt_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 20px;
            font-size: 11px;
            line-height: 1.4;
        }
        .header {
            border-bottom: 2px solid #0f766e;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .kop-table {
            width: 100%;
        }
        .unit-name {
            font-size: 16px;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
        }
        .foundation-name {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
        }
        .unit-address {
            font-size: 9px;
            color: #475569;
        }
        .receipt-title {
            text-align: right;
        }
        .receipt-badge {
            font-size: 14px;
            font-weight: bold;
            color: #0f766e;
            letter-spacing: 1px;
        }
        .receipt-number {
            font-size: 11px;
            color: #334155;
            font-family: monospace;
            font-weight: bold;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
        }
        .meta-table td {
            vertical-align: top;
            padding: 2px 4px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .items-table th {
            background-color: #f1f5f9;
            color: #0f766e;
            text-align: left;
            padding: 6px 8px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            font-size: 10px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .total-row td {
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #0f766e;
            border-bottom: 2px solid #0f766e;
            background-color: #f8fafc;
        }
        .terbilang-box {
            background-color: #f0fdfa;
            border-left: 4px solid #0f766e;
            padding: 8px 12px;
            font-style: italic;
            color: #134e4a;
            margin-bottom: 15px;
            border-radius: 2px;
        }
        .footer-table {
            width: 100%;
            margin-top: 10px;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .status-void {
            color: #dc2626;
            border: 2px dashed #dc2626;
            padding: 4px 10px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="header">
        @php
            $logoUri = app_setting('show_foundation_logo_on_pdf', '1') === '1'
                ? logo_data_uri(app_setting('foundation_logo_path', 'images/yayasan-logo.png'))
                : null;
            $foundationName = app_setting('foundation_name', 'Yayasan Generasi Robbani Sumatera Selatan');
            $foundationAddress = app_setting('foundation_address', 'Jl. Sarjana Blok A No. 25 Timbangan, Indralaya Utara, Ogan Ilir');
            $foundationPhone = $unit->phone ?: app_setting('foundation_phone', '0711-820123');
        @endphp
        <table class="kop-table">
            <tr>
                @if($logoUri)
                    <td style="width: 118px; vertical-align: middle; padding-right: 10px;">
                        <img src="{{ $logoUri }}" alt="Logo Yayasan" style="max-height: 48px; max-width: 112px; width: auto;">
                    </td>
                @endif
                <td style="vertical-align: middle;">
                    <div class="foundation-name">{{ strtoupper($foundationName) }}</div>
                    <div class="unit-name">{{ $unit->name }}</div>
                    <div class="unit-address">{{ $foundationAddress }} | Telp: {{ $foundationPhone }}</div>
                </td>
                <td class="receipt-title" style="width: 175px; vertical-align: middle;">
                    <div class="receipt-badge">KWITANSI RESMI</div>
                    <div class="receipt-number">{{ $payment->receipt_number }}</div>
                    <div style="font-size: 9px; color: #64748b;">Tanggal: {{ $payment->payment_date->translatedFormat('d F Y H:i') }}</div>
                    @if($payment->status->value === 'void')
                        <div style="margin-top: 4px;"><span class="status-void">DIBATALKAN / VOID</span></div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%; color: #64748b;">Nama Siswa</td>
            <td style="width: 35%; font-weight: bold;">: {{ $student->name }}</td>
            <td style="width: 18%; color: #64748b;">Metode Bayar</td>
            <td style="width: 32%; font-weight: bold;">: {{ $payment->payment_method->label() }}</td>
        </tr>
        <tr>
            <td style="color: #64748b;">NIS / NISN</td>
            <td>: {{ $student->nis }} {{ $student->nisn ? '/ ' . $student->nisn : '' }}</td>
            <td style="color: #64748b;">Akun Penerima</td>
            <td>: {{ $payment->cashAccount->name }}</td>
        </tr>
        <tr>
            <td style="color: #64748b;">Kelas / Unit</td>
            <td>: {{ $student->classroom?->name ?? '-' }} ({{ $unit->code->value }})</td>
            <td style="color: #64748b;">No. Referensi</td>
            <td>: {{ $payment->reference_number ?? '-' }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 30%;">No. Tagihan</th>
                <th style="width: 45%;">Rincian Komponen Biaya</th>
                <th style="width: 20%; text-align: right;">Jumlah Dibayar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="font-family: monospace;">{{ $item->invoice->invoice_number }}</td>
                    <td>
                        <strong>{{ $item->invoice->feeComponent->name }}</strong>
                        @if($item->invoice->billing_month)
                            <span style="color: #64748b;">(Bulan {{ $item->invoice->billing_month }}/{{ $item->invoice->billing_year }})</span>
                        @endif
                        @if($item->invoice->discount_amount > 0)
                            <div style="font-size: 9px; color: #0f766e;">Potongan Beasiswa: {{ format_rupiah($item->invoice->discount_amount) }}</div>
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: 600;">{{ format_rupiah($item->amount) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3" style="text-align: right; padding-right: 15px;">TOTAL PEMBAYARAN:</td>
                <td style="text-align: right; color: #0f766e;">{{ format_rupiah($payment->total_amount) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="terbilang-box">
        <strong>Terbilang:</strong> {{ ucwords(terbilang($payment->total_amount)) }} Rupiah.
    </div>

    <table class="footer-table">
        <tr>
            <td style="width: 60%; vertical-align: top; font-size: 9px; color: #64748b;">
                <strong>Catatan:</strong><br>
                - {{ app_setting('invoice_footer_note', 'Kwitansi ini merupakan bukti pembayaran sah yang dikeluarkan oleh SmartKas Yayasan Generasi Robbani Sumatera Selatan.') }}<br>
                - Harap disimpan sebagai bukti transaksi.<br>
                - Dicetak oleh: {{ $payment->creator->name ?? 'Admin Keuangan' }} pada {{ now()->translatedFormat('d F Y H:i:s') }}.
            </td>
            <td style="width: 40%; text-align: right; vertical-align: bottom;">
                <div class="signature-box" style="float: right;">
                    <div style="font-size: 10px; color: #475569; margin-bottom: 45px;">
                        Kasir / Admin Keuangan,
                    </div>
                    <div style="font-weight: bold; border-bottom: 1px solid #1e293b; padding-bottom: 2px;">
                        {{ $payment->creator->name ?? 'Admin Keuangan Unit' }}
                    </div>
                    <div style="font-size: 9px; color: #64748b;">{{ app_setting('receipt_signer_title', 'Staf Keuangan Yayasan') }}</div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
