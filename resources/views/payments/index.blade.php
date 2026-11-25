@extends('layouts.app', ['title' => 'Riwayat Pembayaran & Kwitansi'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Riwayat Pembayaran & Kwitansi</h2>
            <p class="text-xs text-slate-500 mt-0.5">Daftar transaksi penerimaan kasir, cetak PDF kwitansi, dan audit pembatalan (void).</p>
        </div>

        <a href="{{ route('payments.cashier') }}" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-md transition flex items-center gap-1.5">
            + Kasir Pembayaran Baru
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('payments.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @if(!auth()->user()->unit_id)
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Unit Sekolah</label>
                    <select name="unit_id" class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <option value="">-- Semua Unit --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ (string)$unitId === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="{{ auth()->user()->unit_id ? 'sm:col-span-2' : '' }}">
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Cari No. Kwitansi / Siswa</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik No. Kwitansi, nama siswa, atau NIS..." class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                    Cari Kwitansi
                </button>
                <a href="{{ route('payments.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">No. Kwitansi</th>
                        <th class="py-3 px-4">Waktu Transaksi</th>
                        <th class="py-3 px-4">Siswa & Unit</th>
                        <th class="py-3 px-4">Metode & Akun</th>
                        <th class="py-3 px-4 text-right">Total Bayar</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $p)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <x-code-badge :code="$p->receipt_number" />
                            </td>
                            <td class="py-3 px-4 text-slate-600 whitespace-nowrap font-mono">{{ $p->payment_date->translatedFormat('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4 min-w-[160px]">
                                <div class="font-bold text-slate-900 leading-snug">{{ $p->student?->name ?? 'Siswa Arsip' }}</div>
                                <div class="text-[11px] text-slate-400 font-medium">
                                    {{ $p->unit?->code?->value ?? '-' }} &bull; {{ $p->student?->classroom?->name ?? 'NIS: ' . ($p->student?->nis ?? '-') }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-800 whitespace-nowrap">{{ $p->payment_method->label() }}</div>
                                <div class="text-[10px] text-slate-400 leading-tight">{{ $p->cashAccount?->name ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-700 text-sm font-mono tabular-nums whitespace-nowrap">
                                {{ format_rupiah($p->total_amount) }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold inline-block {{ $p->status->value === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $p->status->label() }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('payments.show', $p) }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="Lihat Detail Transaksi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('receipts.download', $p) }}" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition" title="Unduh Kwitansi PDF">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Belum ada riwayat pembayaran yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
