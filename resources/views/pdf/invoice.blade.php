<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tagihan Siswa - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 24px 28px;
            font-size: 11px;
            line-height: 1.45;
        }
        .header {
            border-bottom: 2.5px solid #0B3B6F;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .kop-table {
            width: 100%;
            border-collapse: collapse;
        }
        .logo-cell {
            width: 135px;
            vertical-align: middle;
            padding-right: 12px;
        }
        .logo-img {
            max-height: 56px;
            max-width: 130px;
            width: auto;
        }
        .foundation-name {
            font-size: 11px;
            font-weight: bold;
            color: #0B3B6F;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .unit-name {
            font-size: 15px;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .unit-address {
            font-size: 9.5px;
            color: #475569;
            margin-top: 2px;
        }
        .doc-title-cell {
            text-align: right;
            vertical-align: middle;
            width: 190px;
        }
        .doc-badge {
            font-size: 15px;
            font-weight: bold;
            color: #0B3B6F;
            letter-spacing: 0.8px;
        }
        .doc-number {
            font-size: 10px;
            color: #334155;
            font-family: monospace;
            font-weight: bold;
            margin-top: 3px;
        }
        .status-pill {
            display: inline-block;
            margin-top: 5px;
            padding: 2px 8px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .status-paid { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .status-partial { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .status-unpaid { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .status-void { background: #e2e8f0; color: #475569; border: 1px solid #cbd5e1; }
        .meta-table {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 3px 4px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .items-table th {
            background-color: #f1f5f9;
            color: #0B3B6F;
            text-align: left;
            padding: 8px 10px;
            border-top: 1.5px solid #0B3B6F;
            border-bottom: 1.5px solid #cbd5e1;
            font-size: 10px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .summary-table {
            width: 48%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .summary-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .summary-total td {
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #0B3B6F;
            border-bottom: 2px solid #0B3B6F;
            background-color: #f0fdfa;
            color: #0B3B6F;
        }
        .payment-info-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #0f766e;
            padding: 10px 14px;
            margin-bottom: 18px;
        }
        .footer-table {
            width: 100%;
            margin-top: 18px;
        }
    </style>
</head>
<body>
    @php
        $logoUri = app_setting('show_foundation_logo_on_pdf', '1') === '1'
            ? logo_data_uri(app_setting('foundation_logo_path', 'images/yayasan-logo.png'))
            : null;
        $foundationName = app_setting('foundation_name', 'Yayasan Generasi Robbani Sumatera Selatan');
        $foundationAddress = app_setting('foundation_address', 'Jl. Sarjana Blok A No. 25 Timbangan, Indralaya Utara, Ogan Ilir');
        $foundationPhone = $unit->phone ?: app_setting('foundation_phone', '0711-820123');
        $statusClass = match($invoice->status->value) {
            'paid' => 'status-paid',
            'partial' => 'status-partial',
            'void' => 'status-void',
            default => 'status-unpaid',
        };
    @endphp

    <div class="header">
        <table class="kop-table">
            <tr>
                @if($logoUri)
                    <td class="logo-cell">
                        <img src="{{ $logoUri }}" alt="Logo Yayasan" class="logo-img">
                    </td>
                @endif
                <td style="vertical-align: middle;">
                    <div class="foundation-name">{{ $foundationName }}</div>
                    <div class="unit-name">{{ $unit->name }}</div>
                    <div class="unit-address">
                        {{ $foundationAddress }}
                        @if($foundationPhone) | Telp: {{ $foundationPhone }} @endif
                    </div>
                </td>
                <td class="doc-title-cell">
                    <div class="doc-badge">INVOICE TAGIHAN</div>
                    <div class="doc-number">{{ $invoice->invoice_number }}</div>
                    <div style="font-size: 9px; color: #64748b; margin-top: 2px;">
                        Jatuh Tempo: {{ $invoice->due_date->translatedFormat('d F Y') }}
                    </div>
                    <div>
                        <span class="status-pill {{ $statusClass }}">{{ $invoice->status->label() }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 16%; color: #64748b;">Nama Siswa</td>
            <td style="width: 34%; font-weight: bold;">: {{ $student?->name ?? 'Siswa Arsip' }}</td>
            <td style="width: 18%; color: #64748b;">Tahun Ajaran</td>
            <td style="width: 32%; font-weight: bold;">: {{ $invoice->academicYear?->name ?? app_setting('academic_year_label', '2026/2027') }}</td>
        </tr>
        <tr>
            <td style="color: #64748b;">NIS / NISN</td>
            <td>: {{ $student?->nis ?? '-' }}{{ $student?->nisn ? ' / ' . $student->nisn : '' }}</td>
            <td style="color: #64748b;">Periode Tagihan</td>
            <td>: {{ $invoice->billing_month ? "Bulan {$invoice->billing_month}/{$invoice->billing_year}" : 'Tagihan Insidentil / Tahunan' }}</td>
        </tr>
        <tr>
            <td style="color: #64748b;">Kelas / Unit</td>
            <td>: {{ $student?->classroom?->name ?? '-' }} ({{ $unit?->code?->value ?? '-' }})</td>
            <td style="color: #64748b;">Orang Tua / Wali</td>
            <td>: {{ $student?->parent_name ?? '-' }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th style="width: 44%;">Komponen Tagihan & Keterangan</th>
                <th style="width: 18%; text-align: right;">Tagihan Awal</th>
                <th style="width: 14%; text-align: right;">Potongan</th>
                <th style="width: 18%; text-align: right;">Total Net</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>
                    <strong>{{ $invoice->feeComponent?->name ?? '-' }}</strong>
                    @if($invoice->notes)
                        <div style="font-size: 9.5px; color: #64748b; margin-top: 2px;">{{ $invoice->notes }}</div>
                    @endif
                </td>
                <td style="text-align: right;">{{ format_rupiah($invoice->gross_amount) }}</td>
                <td style="text-align: right; color: #0f766e;">{{ $invoice->discount_amount > 0 ? '-' . format_rupiah($invoice->discount_amount) : '-' }}</td>
                <td style="text-align: right; font-weight: bold;">{{ format_rupiah($invoice->net_amount) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td style="color: #475569;">Total Tagihan Net</td>
            <td style="text-align: right; font-weight: bold;">{{ format_rupiah($invoice->net_amount) }}</td>
        </tr>
        <tr>
            <td style="color: #0f766e;">Sudah Dibayar / Cicilan</td>
            <td style="text-align: right; color: #0f766e; font-weight: bold;">{{ format_rupiah($invoice->paid_amount) }}</td>
        </tr>
        <tr class="summary-total">
            <td>SISA YANG HARUS DIBAYAR</td>
            <td style="text-align: right;">{{ format_rupiah($invoice->remaining_amount) }}</td>
        </tr>
    </table>

    @if($invoice->remaining_amount > 0 && $bankAccounts->isNotEmpty())
        <div class="payment-info-box">
            <div style="font-weight: bold; color: #0B3B6F; margin-bottom: 4px; text-transform: uppercase; font-size: 10px;">
                Informasi Pembayaran Resmi
            </div>
            <div style="font-size: 10px; color: #334155;">
                Pembayaran dapat dilakukan secara langsung di Kasir Yayasan/Unit atau melalui transfer ke rekening resmi:
                @foreach($bankAccounts as $bank)
                    <div style="margin-top: 3px;">
                        &bull; <strong>{{ $bank->bank_name ?? $bank->name }}</strong> &mdash; No. Rek: <strong style="font-family: monospace;">{{ $bank->account_number ?? '-' }}</strong> a.n. {{ $foundationName }}
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <table class="footer-table">
        <tr>
            <td style="width: 62%; vertical-align: top; font-size: 9px; color: #64748b;">
                <strong>Catatan:</strong><br>
                - {{ app_setting('invoice_footer_note', 'Dokumen resmi sistem keuangan Yayasan Generasi Robbani Sumatera Selatan.') }}<br>
                - Dicetak pada {{ now()->translatedFormat('d F Y H:i') }} WIB.
            </td>
            <td style="width: 38%; text-align: center; vertical-align: bottom;">
                <div style="font-size: 10px; color: #475569; margin-bottom: 42px;">
                    Ogan Ilir, {{ now()->translatedFormat('d F Y') }}<br>
                    Bagian Keuangan {{ $unit->code->value }},
                </div>
                <div style="font-weight: bold; border-bottom: 1px solid #1e293b; display: inline-block; min-width: 150px; padding-bottom: 2px;">
                    {{ $invoice->creator?->name ?? 'Admin Keuangan' }}
                </div>
                <div style="font-size: 9px; color: #64748b; margin-top: 2px;">
                    {{ app_setting('receipt_signer_title', 'Staf Keuangan Yayasan') }}
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
