@extends('layouts.app', ['title' => 'Dashboard Keuangan'])

@section('content')
<div class="space-y-6">

    <!-- Header & Unit Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Dashboard Ringkasan Keuangan</h2>
            <p class="text-xs text-slate-500 mt-0.5">Arus kas, penerimaan pembayaran SPP, realisasi belanja & tunggakan.</p>
        </div>

        @if(!auth()->user()->unit_id)
            <!-- Unit Filter for Superadmin & Treasurer -->
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">Unit Sekolah:</label>
                <select name="unit_id" onchange="this.form.submit()" class="text-xs font-medium rounded-xl border border-slate-200 bg-white px-3 py-2 focus:ring-2 focus:ring-emerald-500 shadow-2xs">
                    <option value="">-- Seluruh Yayasan (Konsolidasi) --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ (string)$unitId === (string)$u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->code->value }})
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Saldo Kas/Bank -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Saldo Kas & Bank</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    Rp
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900">{{ format_rupiah($totalBalance) }}</div>
            <div class="text-[11px] text-emerald-600 mt-2 font-semibold">Tersedia di brankas & rekening bank</div>
        </div>

        <!-- 2. Pemasukan Bulan Ini -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Penerimaan (Bulan Ini)</span>
                <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-emerald-700">{{ format_rupiah($monthlyIncome) }}</div>
            <div class="text-[11px] text-slate-500 mt-2">Periode {{ now()->translatedFormat('F Y') }}</div>
        </div>

        <!-- 3. Pengeluaran Bulan Ini -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pengeluaran (Bulan Ini)</span>
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-rose-700">{{ format_rupiah($monthlyExpense) }}</div>
            <div class="text-[11px] text-slate-500 mt-2">Operasional rutin & pencairan dana</div>
        </div>

        <!-- 4. Total Tunggakan SPP -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Tunggakan SPP</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-amber-700">{{ format_rupiah($totalArrears) }}</div>
            <div class="text-[11px] text-slate-500 mt-2">
                <a href="{{ route('reports.arrears') }}" class="text-amber-800 font-bold hover:underline">Lihat Detail Tunggakan &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Action Shortcuts -->
    <div class="bg-gradient-to-r from-emerald-800 to-teal-900 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="font-extrabold text-lg text-white">Aksi Cepat Keuangan Hari Ini</h3>
            <p class="text-xs text-emerald-200 mt-0.5">Buka kasir pembayaran SPP siswa, generate tagihan bulanan baru, atau rekam pengeluaran.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('payments.cashier') }}" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-md transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Buka Kasir SPP
            </a>
            <a href="{{ route('invoices.create') }}" class="px-4 py-2.5 rounded-xl bg-white/20 hover:bg-white/30 text-white font-bold text-xs border border-white/20 transition">
                + Generate Tagihan
            </a>
            <a href="{{ route('expenses.create') }}" class="px-4 py-2.5 rounded-xl bg-white/20 hover:bg-white/30 text-white font-bold text-xs border border-white/20 transition">
                + Catat Belanja / Pengajuan
            </a>
        </div>
    </div>

    <!-- Main Grid: Recent Transactions & Pending Approvals -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left (2 Cols): Buku Kas Mutasi Terakhir -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-base text-slate-900">Arus Kas & Mutasi Terakhir</h3>
                    <p class="text-xs text-slate-500">8 transaksi penerimaan dan pengeluaran terbaru di Buku Kas</p>
                </div>
                <a href="{{ route('reports.cash-ledger') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">Lihat Semua &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-2.5">Waktu</th>
                            <th class="py-2.5">Unit / Akun</th>
                            <th class="py-2.5">Keterangan</th>
                            <th class="py-2.5 text-right">Nominal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentLedgers as $l)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3 text-slate-500 font-mono">{{ $l->transaction_date->format('d/m H:i') }}</td>
                                <td class="py-3">
                                    <div class="font-bold text-slate-800">{{ $l->unit?->code->value ?? 'Pusat' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $l->cashAccount->name }}</div>
                                </td>
                                <td class="py-3 text-slate-700 max-w-xs truncate">{{ $l->description }}</td>
                                <td class="py-3 text-right font-bold {{ $l->type->value === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $l->type->value === 'in' ? '+' : '-' }}{{ format_rupiah($l->amount, false) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">Belum ada mutasi kas tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right (1 Col): Pengajuan Menunggu Approval -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">Antrean Persetujuan Dana</h3>
                        <p class="text-xs text-slate-500">Menunggu approval Kepsek/Bendahara</p>
                    </div>
                    <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-800 text-xs font-bold flex items-center justify-center">
                        {{ $pendingExpenses->count() }}
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($pendingExpenses as $pe)
                        <div class="p-3 rounded-xl border border-slate-100 bg-slate-50 hover:bg-slate-100/80 transition">
                            <div class="flex items-start justify-between gap-2">
                                <div class="font-bold text-xs text-slate-800 line-clamp-1">{{ $pe->title }}</div>
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-100 text-amber-800 shrink-0">
                                    {{ $pe->status->label() }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between mt-2 text-xs">
                                <span class="text-slate-500 font-medium">{{ $pe->unit->code->value }} • {{ $pe->requestedBy->name }}</span>
                                <span class="font-bold text-slate-900">{{ format_rupiah($pe->requested_amount) }}</span>
                            </div>
                            <div class="mt-2 text-right">
                                <a href="{{ route('expenses.show', $pe) }}" class="text-[11px] font-bold text-emerald-600 hover:underline">Periksa & Proses &rarr;</a>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400">
                            Tidak ada pengajuan dana yang tertunda saat ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 mt-4 text-center">
                <a href="{{ route('expenses.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">
                    Lihat Semua Pengajuan Belanja &rarr;
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
