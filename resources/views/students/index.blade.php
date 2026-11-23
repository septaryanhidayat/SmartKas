@extends('layouts.app', ['title' => 'Data Siswa & Kelas'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Data Siswa 4 Unit SIT</h2>
            <p class="text-xs text-slate-500 mt-0.5">Daftar siswa KB/TKIT, SDIT, SMPIT, SMAIT dan penerima beasiswa/diskon.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('students.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
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

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Cari Nama / NIS / No. WA</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik kata kunci..." class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                    Filter Data
                </button>
                <a href="{{ route('students.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">NIS / NISN</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">Unit / Kelas</th>
                        <th class="py-3 px-4">Orang Tua / Wali</th>
                        <th class="py-3 px-4">Diskon / Beasiswa</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $st)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-mono font-semibold text-slate-900">{{ $st->nis }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $st->name }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ $st->unit->code->value }}
                                </span>
                                <div class="text-slate-600 font-medium mt-0.5">{{ $st->classroom?->name ?? 'Belum ada kelas' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-800">{{ $st->parent_name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $st->parent_whatsapp }}</div>
                            </td>
                            <td class="py-3 px-4">
                                @forelse($st->studentDiscounts as $sd)
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 mb-1">
                                        {{ $sd->discount->name }}
                                    </span>
                                @empty
                                    <span class="text-slate-400 text-[11px]">Normal (Tanpa Potongan)</span>
                                @endforelse
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $st->status->value === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $st->status->label() }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('students.show', $st) }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-emerald-100 text-slate-600 hover:text-emerald-700 transition" title="Lihat Profil & Tagihan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('payments.cashier', ['student_id' => $st->id]) }}" class="px-2 py-1 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-[10px] transition" title="Buka Kasir">
                                        Bayar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Tidak ada data siswa yang cocok dengan filter pencarian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $students->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
