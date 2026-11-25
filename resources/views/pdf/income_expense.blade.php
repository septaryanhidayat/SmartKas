<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Surplus Defisit</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #1e293b; padding: 15px; }
        .header { border-bottom: 2px solid #0f766e; padding-bottom: 8px; margin-bottom: 12px; }
        .title { font-size: 15px; font-weight: bold; color: #0f766e; }
        .meta { font-size: 10px; color: #64748b; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f1f5f9; color: #0f766e; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; }
        td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        .section-header td { background-color: #f8fafc; font-weight: bold; color: #0f766e; font-size: 11px; }
        .subtotal-row td { font-weight: bold; border-top: 1px solid #0f766e; border-bottom: 1px solid #0f766e; background-color: #f0fdfa; }
        .grand-total td { font-weight: bold; font-size: 12px; border-top: 2px solid #0f766e; border-bottom: 2px solid #0f766e; background-color: #ccfbf1; }
    </style>
</head>
<body>
    @php
        $logoUri = app_setting('show_foundation_logo_on_pdf', '1') === '1'
            ? logo_data_uri(app_setting('foundation_logo_path', 'images/yayasan-logo.png'))
            : null;
        $foundationName = app_setting('foundation_name', 'Yayasan Generasi Robbani Sumatera Selatan');
        $foundationAddress = app_setting('foundation_address', 'Jl. Sarjana Blok A No. 25 Timbangan, Indralaya Utara, Ogan Ilir');
    @endphp
    <div class="header">
        <table style="width: 100%; margin: 0; border-collapse: collapse;">
            <tr>
                @if($logoUri)
                    <td style="width: 115px; border: none; padding: 0 10px 0 0; vertical-align: middle;">
                        <img src="{{ $logoUri }}" alt="Logo Yayasan" style="max-height: 46px; max-width: 110px; width: auto;">
                    </td>
                @endif
                <td style="border: none; padding: 0; vertical-align: middle;">
                    <div style="font-size: 10px; font-weight: bold; color: #0B3B6F; text-transform: uppercase;">{{ $foundationName }}</div>
                    <div class="title">LAPORAN SURPLUS / DEFISIT (LABA RUGI)</div>
                    <div class="meta">
                        {{ $foundationAddress }}<br>
                        {{ $report['unit'] ? $report['unit']->name : 'Konsolidasi Seluruh Unit' }} | Periode: {{ $report['start_date']->translatedFormat('d F Y') }} s/d {{ $report['end_date']->translatedFormat('d F Y') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Kode Pos</th>
                <th style="width: 55%;">Uraian Pos Keuangan</th>
                <th style="width: 25%; text-align: right;">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-header">
                <td colspan="3">1. PENDAPATAN OPERASIONAL & PENDIDIKAN</td>
            </tr>
            @forelse($report['incomes'] as $inc)
                <tr>
                    <td>{{ $inc->code }}</td>
                    <td>{{ $inc->name }} ({{ $inc->payment_count }} transaksi)</td>
                    <td style="text-align: right;">{{ format_rupiah($inc->total_amount, false) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align: center; color: #94a3b8;">Belum ada pendapatan tercatat pada periode ini.</td></tr>
            @endforelse
            <tr class="subtotal-row">
                <td colspan="2">TOTAL PENDAPATAN:</td>
                <td style="text-align: right;">{{ format_rupiah($report['total_income']) }}</td>
            </tr>

            <tr class="section-header">
                <td colspan="3" style="padding-top: 15px;">2. BIAYA PENGELUARAN OPERASIONAL & KEGIATAN</td>
            </tr>
            @forelse($report['expenses'] as $exp)
                <tr>
                    <td>{{ $exp->code }}</td>
                    <td>{{ $exp->name }} ({{ $exp->request_count }} pengeluaran)</td>
                    <td style="text-align: right;">{{ format_rupiah($exp->total_amount, false) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align: center; color: #94a3b8;">Belum ada pengeluaran tercatat pada periode ini.</td></tr>
            @endforelse
            <tr class="subtotal-row">
                <td colspan="2">TOTAL BIAYA PENGELUARAN:</td>
                <td style="text-align: right;">{{ format_rupiah($report['total_expense']) }}</td>
            </tr>

            <tr class="grand-total">
                <td colspan="2">SURPLUS / (DEFISIT) BERSIH:</td>
                <td style="text-align: right; color: {{ $report['surplus_deficit'] >= 0 ? '#0f766e' : '#dc2626' }};">
                    {{ format_rupiah($report['surplus_deficit']) }}
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>
