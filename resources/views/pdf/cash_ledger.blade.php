<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Kas Umum</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #1e293b; padding: 15px; }
        .header { border-bottom: 2px solid #0f766e; padding-bottom: 8px; margin-bottom: 12px; }
        .title { font-size: 15px; font-weight: bold; color: #0f766e; }
        .meta { font-size: 10px; color: #64748b; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f1f5f9; color: #0f766e; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; }
        td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        .summary-box { background-color: #f8fafc; border: 1px solid #cbd5e1; padding: 8px 12px; margin-top: 12px; }
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
                    <div class="title">BUKU KAS UMUM (MUTASI KAS)</div>
                    <div class="meta">
                        {{ $foundationAddress }}<br>
                        Akun: {{ $report['cash_account']->name ?? 'Seluruh Akun Kas' }} | Periode: {{ $startDate->translatedFormat('d F Y') }} s/d {{ $endDate->translatedFormat('d F Y') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="summary-box">
        <table style="width: 100%; border: none;">
            <tr>
                <td><strong>Saldo Awal:</strong> {{ format_rupiah($report['opening_balance']) }}</td>
                <td><strong>Total Masuk (Debet):</strong> {{ format_rupiah($report['total_in']) }}</td>
                <td><strong>Total Keluar (Kredit):</strong> {{ format_rupiah($report['total_out']) }}</td>
                <td><strong>Saldo Akhir:</strong> <span style="color: #0f766e; font-weight: bold;">{{ format_rupiah($report['closing_balance']) }}</span></td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 15%;">Akun Kas</th>
                <th style="width: 35%;">Uraian Transaksi</th>
                <th style="width: 15%; text-align: right;">Masuk (Rp)</th>
                <th style="width: 15%; text-align: right;">Keluar (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['mutations'] as $idx => $m)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $m->transaction_date->translatedFormat('d/m/Y H:i') }}</td>
                    <td>{{ $m->cashAccount->name }}</td>
                    <td>{{ $m->description }}</td>
                    <td style="text-align: right;">{{ $m->type->value === 'in' ? format_rupiah($m->amount, false) : '-' }}</td>
                    <td style="text-align: right; color: #dc2626;">{{ $m->type->value === 'out' ? format_rupiah($m->amount, false) : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align: center; color: #94a3b8; padding: 15px;">Tidak ada mutasi transaksi pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
