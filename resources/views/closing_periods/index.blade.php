@extends('layouts.app', ['title' => 'Tutup Buku Bulanan'])

@section('content')
<div class="space-y-6 max-w-4xl">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Tutup Buku Periode Keuangan</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kunci transaksi keuangan per bulan/tahun agar bersifat permanen (*immutable*) dan tidak dapat dimanipulasi.</p>
        </div>

        <!-- Form Close Period Modal / Button (Bendahara & Superadmin) -->
        <div x-data="{ open: false }">
            <button @click="open = true" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                + Kunci / Tutup Buku Periode
            </button>

            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
                <div @click.away="open = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
                    <h3 class="text-lg font-bold text-slate-900">Tutup Buku Periode Bulanan</h3>
                    <p class="text-xs text-slate-500">
                        Setelah periode ditutup, tidak ada pengguna (termasuk admin unit) yang dapat menambah, mengubah, atau membatalkan transaksi keuangan pada periode tersebut.
                    </p>

                    <form action="{{ route('closing-periods.close') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Bulan *</label>
                                <select name="month" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 p-2.5">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>Bulan {{ $m }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tahun *</label>
                                <input type="number" name="year" value="{{ date('Y') }}" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 p-2.5">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Cakupan Unit</label>
                            <select name="unit_id" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 p-2.5">
                                <option value="">-- Seluruh Yayasan (Semua Unit) --</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Penutupan Buku</label>
                            <textarea name="notes" rows="2" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 p-2.5" placeholder="Misal: Tutup buku rutin bulanan oleh Bendahara"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="open = false" class="px-3 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs">Batal</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs shadow-md">Tutup Buku Sekarang</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Closed Periods Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Periode</th>
                        <th class="py-3 px-4">Unit Sekolah</th>
                        <th class="py-3 px-4">Status Kunci</th>
                        <th class="py-3 px-4">Ditutup Oleh</th>
                        <th class="py-3 px-4">Waktu Ditutup</th>
                        <th class="py-3 px-4">Catatan</th>
                        <th class="py-3 px-4 text-center">Aksi Buka Kembali</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($periods as $per)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900 text-sm">
                                Bulan {{ $per->month }}/{{ $per->year }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $per->unit?->name ?? 'Seluruh Yayasan (Konsolidasi)' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $per->status->value === 'closed' ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $per->status->label() }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-700">{{ $per->closedBy?->name ?? '-' }}</td>
                            <td class="py-3 px-4 text-slate-500 font-mono">{{ $per->closed_at ? $per->closed_at->translatedFormat('d/m/Y H:i') : '-' }}</td>
                            <td class="py-3 px-4 text-slate-600 max-w-xs truncate">{{ $per->notes ?? '-' }}</td>
                            <td class="py-3 px-4 text-center">
                                @if($per->status->value === 'closed')
                                    <div x-data="{ openReopen: false }">
                                        <button @click="openReopen = true" class="px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-[10px] transition">
                                            Buka Kunci
                                        </button>
                                        <!-- Modal Reopen -->
                                        <div x-show="openReopen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
                                            <div @click.away="openReopen = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4 text-left">
                                                <h3 class="text-base font-bold text-slate-900">Buka Kembali Tutup Buku</h3>
                                                <form action="{{ route('closing-periods.reopen') }}" method="POST" class="space-y-4">
                                                    @csrf
                                                    <input type="hidden" name="year" value="{{ $per->year }}">
                                                    <input type="hidden" name="month" value="{{ $per->month }}">
                                                    <input type="hidden" name="unit_id" value="{{ $per->unit_id }}">
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alasan Wajib Membuka Kembali *</label>
                                                        <textarea name="reason" required rows="3" class="w-full text-xs rounded-xl border border-slate-200 p-2.5" placeholder="Tuliskan justifikasi pembukaan kembali..."></textarea>
                                                    </div>
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button" @click="openReopen = false" class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-600 text-xs">Batal</button>
                                                        <button type="submit" class="px-4 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs">Buka Kembali</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[10px]">Terbuka</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Belum ada periode yang ditutup buku.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
