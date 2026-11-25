@extends('layouts.app', ['title' => 'Dashboard Keuangan'])

@section('content')
<div class="space-y-6">

    <!-- Executive Greeting & Unit Selector Bar -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/90 shadow-sm relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 relative z-10">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-50 text-[#0B3B6F] border border-teal-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                        Tahun Ajaran 2026/2027
                    </span>
                    <span class="text-xs text-slate-400 font-medium">&bull; {{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                    Dashboard Ringkasan Keuangan
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Assalamu'alaikum, <strong class="text-slate-800">{{ auth()->user()->name }}</strong> &mdash; {{ auth()->user()->displayRoleDescription() }}
                </p>
            </div>

            <!-- Status Tutup Buku & Filter Unit -->
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="px-3.5 py-2 rounded-xl border text-xs font-bold flex items-center gap-2 {{ $isPeriodClosed ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800' }}">
                    <span class="w-2 h-2 rounded-full shrink-0 {{ $isPeriodClosed ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                    <span>Periode {{ now()->translatedFormat('F Y') }}: <strong>{{ $isPeriodClosed ? 'Terkunci (Closed)' : 'Aktif' }}</strong></span>
                </div>

                @if(!auth()->user()->unit_id)
                    <form method="GET" action="{{ route('dashboard') }}">
                        <select name="unit_id" onchange="this.form.submit()" class="text-xs font-bold rounded-xl border border-slate-300 bg-slate-50 hover:bg-white px-3.5 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer transition">
                            <option value="">Seluruh Yayasan (Konsolidasi 4 Unit)</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ (string)$unitId === (string)$u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->code->value }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Stat Cards Bento Grid (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- 1. Total Saldo Kas & Bank -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Saldo Kas &amp; Bank</span>
                <div class="w-9 h-9 rounded-xl bg-[#0B3B6F]/10 text-[#0B3B6F] flex items-center justify-center font-extrabold text-xs shrink-0">
                    Rp
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 tracking-tight font-mono tabular-nums">
                {{ format_rupiah($totalBalance) }}
            </div>
            <div class="text-[11px] text-teal-700 mt-2 font-semibold flex items-center gap-1.5">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $cashAccounts->count() }} Rekening &amp; Kas Aktif</span>
            </div>
        </div>

        <!-- 2. Pemasukan Bulan Ini -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Penerimaan (Bulan Ini)</span>
                <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-teal-700 tracking-tight font-mono tabular-nums">
                {{ format_rupiah($monthlyIncome) }}
            </div>
            <div class="text-[11px] text-slate-500 mt-2 flex items-center justify-between gap-2">
                <span>{{ now()->translatedFormat('F Y') }}</span>
                <span class="font-bold text-teal-700">+{{ format_rupiah($todayIncome, false) }} hari ini</span>
            </div>
        </div>

        <!-- 3. Pengeluaran Bulan Ini -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pengeluaran (Bulan Ini)</span>
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-rose-700 tracking-tight font-mono tabular-nums">
                {{ format_rupiah($monthlyExpense) }}
            </div>
            <div class="text-[11px] text-slate-500 mt-2 flex items-center justify-between gap-2">
                <span>Realisasi Cair</span>
                <span class="font-bold text-rose-600">-{{ format_rupiah($todayExpense, false) }} hari ini</span>
            </div>
        </div>

        <!-- 4. Total Tunggakan SPP -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tunggakan SPP</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-amber-700 tracking-tight font-mono tabular-nums">
                {{ format_rupiah($totalArrears) }}
            </div>
            <div class="text-[11px] text-slate-500 mt-2 flex items-center justify-between gap-2">
                <span>{{ $arrearsStudentsCount }} Siswa</span>
                <a href="{{ route('reports.arrears') }}" class="font-bold text-[#0B3B6F] hover:text-teal-700 hover:underline">Lihat Rekap &rarr;</a>
            </div>
        </div>

    </div>

    <!-- Action Shortcuts Launchpad -->
    <div class="bg-gradient-to-r from-[#062547] via-[#0B3B6F] to-[#0D6E7A] rounded-2xl p-5 sm:p-6 text-white shadow-lg flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-white/15 text-teal-200 uppercase tracking-wider">
                    Layanan Cepat SmartKas
                </span>
            </div>
            <h3 class="font-bold text-base sm:text-lg text-white">Kasir Pembayaran Siswa, Generate Tagihan &amp; Pengajuan Belanja</h3>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            @can('receive-payments')
                <a href="{{ route('payments.cashier') }}" class="px-4 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold text-xs shadow-md transition inline-flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Buka Kasir SPP</span>
                </a>
            @endcan
            @can('generate-invoices')
                <a href="{{ route('invoices.create') }}" class="px-4 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold text-xs border border-white/15 transition">
                    + Generate Tagihan
                </a>
            @endcan
            @can('submit-expense')
                <a href="{{ route('expenses.create') }}" class="px-4 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold text-xs border border-white/15 transition">
                    + Pengajuan Dana
                </a>
            @endcan
            <a href="{{ route('reports.cash-ledger') }}" class="px-4 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold text-xs border border-white/15 transition">
                Buku Kas Umum
            </a>
        </div>
    </div>

    <!-- School Units Summary Cards -->
    <div>
        <div class="flex items-center justify-between mb-3 px-0.5">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">
                    {{ count($unitSummaries) > 1 ? 'Ringkasan Keuangan 4 Unit Sekolah' : 'Ringkasan Keuangan Unit' }}
                </h3>
                <p class="text-xs text-slate-500">Kolektibilitas tagihan bulan ini, penerimaan, dan tunggakan siswa aktif</p>
            </div>
            @if(!auth()->user()->unit_id && $unitId)
                <a href="{{ route('dashboard') }}" class="text-xs font-bold text-[#0B3B6F] hover:text-teal-700 hover:underline">
                    Tampilkan Semua Unit &rarr;
                </a>
            @endif
        </div>

        <div class="grid grid-cols-1 {{ count($unitSummaries) > 1 ? 'md:grid-cols-2 lg:grid-cols-4' : 'max-w-md' }} gap-4">
            @foreach($unitSummaries as $summary)
                @php
                    $u = $summary['unit'];
                    $rate = $summary['collection_rate'];
                    $rateColor = $rate >= 80 ? 'bg-teal-500' : ($rate >= 50 ? 'bg-amber-500' : 'bg-rose-500');
                    $badgeStyle = match($u->code->value) {
                        'TKIT' => 'bg-amber-50 text-amber-800 border-amber-200',
                        'SDIT' => 'bg-teal-50 text-teal-800 border-teal-200',
                        'SMPIT' => 'bg-sky-50 text-sky-800 border-sky-200',
                        'SMAIT' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                        default => 'bg-slate-50 text-slate-800 border-slate-200',
                    };
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border {{ $badgeStyle }}">
                                {{ $u->code->value }}
                            </span>
                            <span class="text-[11px] font-bold text-slate-400">
                                {{ $summary['student_count'] }} Siswa Aktif
                            </span>
                        </div>
                        <h4 class="font-extrabold text-slate-900 text-sm">{{ $u->name }}</h4>
                        <div class="text-[11px] text-slate-500 mt-1 space-y-0.5">
                            <div>Kepsek: <strong class="text-slate-700">{{ $u->principal_name ?? '-' }}</strong></div>
                            <div>Admin: <strong class="text-slate-700">{{ $summary['admin_name'] }}</strong></div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mt-4 pt-3 border-t border-slate-100">
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <span class="text-slate-500 font-semibold">Pelunasan Bulan Ini</span>
                                <span class="font-extrabold text-slate-800 font-mono">{{ $rate }}%</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full {{ $rateColor }}" style="width: {{ $rate }}%"></div>
                            </div>
                        </div>

                        <!-- Financial Stats -->
                        <div class="mt-3.5 space-y-1 text-xs">
                            <div class="flex items-center justify-between text-slate-500">
                                <span>Penerimaan:</span>
                                <span class="font-bold text-teal-700 font-mono tabular-nums">{{ format_rupiah($summary['monthly_paid']) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-500">
                                <span>Tunggakan:</span>
                                <span class="font-bold text-amber-700 font-mono tabular-nums">{{ format_rupiah($summary['arrears']) }}</span>
                            </div>
                        </div>
                    </div>

                    @if(!auth()->user()->unit_id && count($unitSummaries) > 1)
                        <div class="mt-4 pt-3 border-t border-slate-100">
                            <a href="{{ route('dashboard', ['unit_id' => $u->id]) }}" class="text-[11px] font-bold text-[#0B3B6F] hover:text-teal-700 flex items-center justify-between">
                                <span>Detail Unit {{ $u->code->value }}</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Main Content Grid: Recent Cash Ledgers & Account Balances / Approvals -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left (8 Cols): Buku Kas Mutasi Terakhir -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-extrabold text-base text-slate-900">Arus Kas &amp; Mutasi Terakhir</h3>
                    <p class="text-xs text-slate-500 mt-0.5">8 transaksi terbaru pada Buku Kas Umum</p>
                </div>
                <a href="{{ route('reports.cash-ledger') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition inline-flex items-center gap-1">
                    <span>Semua Mutasi</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-2">Waktu</th>
                            <th class="py-3 px-2">Unit &amp; Akun</th>
                            <th class="py-3 px-2">Keterangan</th>
                            <th class="py-3 px-2 text-right">Nominal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentLedgers as $l)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-2 text-slate-500 font-mono whitespace-nowrap">
                                    {{ $l->transaction_date->format('d/m/Y') }}
                                    <div class="text-[10px] text-slate-400">{{ $l->transaction_date->format('H:i') }} WIB</div>
                                </td>
                                <td class="py-3 px-2">
                                    <div class="font-bold text-slate-800">{{ $l->unit?->code->value ?? 'Yayasan' }}</div>
                                    <div class="text-[10px] text-slate-400 font-medium truncate max-w-[140px]">{{ $l->cashAccount->name }}</div>
                                </td>
                                <td class="py-3 px-2 text-slate-700">
                                    <div class="font-medium line-clamp-1">{{ $l->description }}</div>
                                </td>
                                <td class="py-3 px-2 text-right font-extrabold font-mono tabular-nums whitespace-nowrap {{ $l->type->value === 'in' ? 'text-teal-700' : 'text-rose-700' }}">
                                    {{ $l->type->value === 'in' ? '+' : '-' }}{{ format_rupiah($l->amount, false) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-10 text-center text-slate-400">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Belum ada mutasi kas tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right (4 Cols): Account Balances & Pending Approvals -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Card: Saldo Rekening Kas & Bank -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3.5">
                    <div>
                        <h4 class="font-extrabold text-sm text-slate-900">Rekening Kas &amp; Bank</h4>
                        <p class="text-[11px] text-slate-400">Saldo aktif real-time</p>
                    </div>
                    <span class="w-6 h-6 rounded-full bg-teal-50 text-teal-800 border border-teal-200 text-xs font-extrabold flex items-center justify-center shrink-0">
                        {{ $cashAccounts->count() }}
                    </span>
                </div>

                <div class="space-y-2">
                    @foreach($cashAccounts as $ca)
                        <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/70 hover:bg-slate-100/80 transition flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-slate-800 truncate">{{ $ca->name }}</div>
                                <div class="text-[10px] text-slate-400 truncate">
                                    {{ $ca->bank_name ?? 'Kas Tunai' }} {{ $ca->account_number ? '• ' . $ca->account_number : '' }}
                                </div>
                            </div>
                            <div class="text-xs font-extrabold text-slate-900 font-mono tabular-nums shrink-0">
                                {{ format_rupiah($ca->current_balance) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Card: Antrean Persetujuan Dana -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3.5">
                    <div>
                        <h4 class="font-extrabold text-sm text-slate-900">Antrean Persetujuan Dana</h4>
                        <p class="text-[11px] text-slate-400">Menunggu approval Kepsek / Bendahara</p>
                    </div>
                    <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-800 text-xs font-extrabold flex items-center justify-center shrink-0">
                        {{ $pendingExpenses->count() }}
                    </span>
                </div>

                <div class="space-y-2.5">
                    @forelse($pendingExpenses as $pe)
                        <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/70 hover:bg-slate-100 transition">
                            <div class="flex items-start justify-between gap-2">
                                <div class="font-bold text-xs text-slate-800 line-clamp-1">{{ $pe->title }}</div>
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800 shrink-0">
                                    {{ $pe->status->label() }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between mt-2 text-xs">
                                <span class="text-slate-500 font-medium truncate">{{ $pe->unit->code->value }} &bull; {{ $pe->requestedBy->name }}</span>
                                <span class="font-extrabold text-slate-900 font-mono tabular-nums shrink-0">{{ format_rupiah($pe->requested_amount) }}</span>
                            </div>
                            <div class="mt-2 text-right">
                                <a href="{{ route('expenses.show', $pe) }}" class="text-[11px] font-extrabold text-[#0B3B6F] hover:text-teal-700 hover:underline">
                                    Periksa &amp; Proses &rarr;
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-slate-400">
                            Tidak ada pengajuan dana yang tertunda.
                        </div>
                    @endforelse
                </div>

                <div class="pt-3 border-t border-slate-100 mt-3 text-center">
                    <a href="{{ route('expenses.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">
                        Lihat Semua Pengajuan Belanja &rarr;
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
