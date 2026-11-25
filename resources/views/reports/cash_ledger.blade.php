@extends('layouts.app', ['title' => 'Buku Kas Umum'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Buku Kas Umum (Mutasi Kas)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Catatan kronologis penerimaan, pengeluaran, saldo awal & saldo akhir per akun kas/bank.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('reports.cash-ledger.excel', request()->query()) }}" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel (.xlsx)
            </a>
            <a href="{{ route('reports.cash-ledger.pdf', request()->query()) }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export PDF
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('reports.cash-ledger') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            @if(!auth()->user()->unit_id)
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Unit Sekolah</label>
                    <select name="unit_id" class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <option value="">-- Semua Unit --</option>
                        @foreach($units->where('level', '!=', 'foundation') as $u)
                            <option value="{{ $u->id }}" {{ (string)$unitId === (string)$u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="{{ auth()->user()->unit_id ? 'sm:col-span-2' : '' }}">
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Akun Kas / Bank</label>
                <select name="cash_account_id" class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                    <option value="">-- Semua Akun Kas & Bank --</option>
                    @foreach($cashAccounts as $ca)
                        <option value="{{ $ca->id }}" {{ (string)$cashAccountId === (string)$ca->id ? 'selected' : '' }}>
                            {{ $ca->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                    Filter Laporan
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Balance Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Saldo Awal</span>
            <div class="text-lg font-bold text-slate-800 mt-1">{{ format_rupiah($report['opening_balance']) }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-emerald-600 uppercase">+ Total Penerimaan</span>
            <div class="text-lg font-bold text-emerald-700 mt-1">{{ format_rupiah($report['total_in']) }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-rose-600 uppercase">- Total Pengeluaran</span>
            <div class="text-lg font-bold text-rose-700 mt-1">{{ format_rupiah($report['total_out']) }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-emerald-300 bg-emerald-50/40 shadow-xs">
            <span class="text-[11px] font-bold text-emerald-800 uppercase">= Saldo Akhir</span>
            <div class="text-xl font-black text-emerald-800 mt-1">{{ format_rupiah($report['closing_balance']) }}</div>
        </div>
    </div>

    <!-- Mutations Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Unit</th>
                        <th class="py-3 px-4">Akun Kas / Bank</th>
                        <th class="py-3 px-4">Uraian Transaksi</th>
                        <th class="py-3 px-4 text-right">Penerimaan (Debet)</th>
                        <th class="py-3 px-4 text-right">Pengeluaran (Kredit)</th>
                        <th class="py-3 px-4 text-right">Saldo Sesudah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($report['mutations'] as $m)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 text-slate-500 font-mono whitespace-nowrap">{{ $m->transaction_date->translatedFormat('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4 font-bold text-slate-700 whitespace-nowrap">{{ $m->unit?->code->value ?? 'YAYASAN' }}</td>
                            <td class="py-3 px-4 text-slate-600 min-w-[140px]">{{ $m->cashAccount->name }}</td>
                            <td class="py-3 px-4 text-slate-800" title="{{ $m->description }}">{{ format_ledger_description($m->description) }}</td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-700 font-mono tabular-nums whitespace-nowrap">
                                {{ $m->type->value === 'in' ? format_rupiah($m->amount) : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-rose-700 font-mono tabular-nums whitespace-nowrap">
                                {{ $m->type->value === 'out' ? format_rupiah($m->amount) : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 tabular-nums whitespace-nowrap">
                                {{ format_rupiah($m->balance_after) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Tidak ada mutasi transaksi kas pada rentang tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
