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
    <div class="header">
        <div class="title">BUKU KAS UMUM (MUTASI KAS)</div>
        <div class="meta">
            Yayasan Generasi Robbani Sumatera Selatan | {{ $report['cash_account']->name ?? 'Seluruh Akun Kas' }}
            <br>Periode: {{ $startDate->translatedFormat('d F Y') }} s/d {{ $endDate->translatedFormat('d F Y') }}
        </div>
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
