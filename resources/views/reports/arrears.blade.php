@extends('layouts.app', ['title' => 'Dashboard Tunggakan SPP'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Dashboard Tunggakan Siswa (Arrears)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Daftar tagihan yang belum lunas (jatuh tempo) beserta fitur pengingat WhatsApp orang tua.</p>
        </div>

        <a href="{{ route('reports.arrears.excel', request()->query()) }}" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export Rekap Excel
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Total Sisa Piutang / Tunggakan</span>
            <div class="text-xl font-black text-amber-700 mt-1 font-mono">{{ format_rupiah($totalArrearsAmount) }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Jumlah Tagihan Belum Lunas</span>
            <div class="text-xl font-black text-slate-900 mt-1 font-mono">{{ number_format($totalArrearsCount, 0, ',', '.') }} Lembar</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Total Siswa Memiliki Tunggakan</span>
            <div class="text-xl font-black text-[#0B3B6F] mt-1 font-mono">{{ number_format($arrearsStudentsCount, 0, ',', '.') }} Siswa</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('reports.arrears') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
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

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Kelas</label>
                <select name="classroom_id" class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($classrooms as $c)
                        <option value="{{ $c->id }}" {{ (string)$classroomId === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="{{ auth()->user()->unit_id ? 'sm:col-span-2' : '' }}">
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Cari Siswa / No. Tagihan</label>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Nama siswa, NIS, atau kode tagihan..." class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                    Filter Tunggakan
                </button>
                <a href="{{ route('reports.arrears') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Arrears Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">No. Tagihan</th>
                        <th class="py-3 px-4">Siswa / NIS</th>
                        <th class="py-3 px-4">Unit & Kelas</th>
                        <th class="py-3 px-4">Komponen Biaya</th>
                        <th class="py-3 px-4">Jatuh Tempo</th>
                        <th class="py-3 px-4 text-right">Sisa Tunggakan</th>
                        <th class="py-3 px-4 text-center">Pengingat WA</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($arrears as $arr)
                        @php
                            $stName = $arr->student?->name ?? 'Siswa Arsip';
                            $compName = $arr->feeComponent?->name ?? '-';
                            $waText = urlencode("Assalamu'alaikum Wr. Wb. Yth. Orang Tua dari {$stName}. Kami mengingatkan terdapat tagihan {$compName} (No: {$arr->invoice_number}) sebesar " . format_rupiah($arr->remaining_amount) . ' dengan jatuh tempo ' . ($arr->due_date?->format('d/m/Y') ?? '-') . '. Mohon untuk melakukan penyelesaian melalui kasir sekolah. Terima kasih.');
                            $waLink = $arr->student?->parent_whatsapp ? "https://wa.me/{$arr->student->parent_whatsapp}?text={$waText}" : null;
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <x-code-badge :code="$arr->invoice_number" />
                            </td>
                            <td class="py-3 px-4 min-w-[160px]">
                                <div class="font-bold text-slate-900 leading-snug">{{ $stName }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $arr->student?->nis ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">{{ $arr->unit?->code?->value ?? '-' }}</span>
                                <div class="text-slate-600 font-medium mt-0.5">{{ $arr->student?->classroom?->name ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800 min-w-[140px]">
                                <div class="leading-snug">{{ $compName }}</div>
                                @if($arr->billing_month)
                                    <div class="text-[10px] text-slate-400 font-normal whitespace-nowrap">Bln {{ $arr->billing_month }}/{{ $arr->billing_year }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-rose-600 font-semibold whitespace-nowrap font-mono">
                                {{ $arr->due_date?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-black text-amber-700 text-sm font-mono tabular-nums whitespace-nowrap">
                                {{ format_rupiah($arr->remaining_amount) }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if($waLink)
                                    <a href="{{ $waLink }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold text-[10px] transition">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                        Kirim WA
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[10px]">No WA kosong</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('payments.cashier', ['student_id' => $arr->student_id]) }}" class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-[10px] transition">
                                    Bayar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Tidak ada tunggakan siswa pada kategori ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($arrears->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $arrears->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
