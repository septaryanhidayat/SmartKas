@extends('layouts.app', ['title' => 'Pengeluaran & Approval Dana'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Pengeluaran & Anggaran Unit</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pengajuan dana kegiatan, biaya operasional rutin, persetujuan Kepsek/Bendahara, dan kas kecil.</p>
        </div>

        <a href="{{ route('expenses.create') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
            + Catat / Ajukan Pengeluaran
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('expenses.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
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
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Status Persetujuan</label>
                <select name="status" class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                    <option value="">-- Semua Status --</option>
                    <option value="submitted" {{ $status === 'submitted' ? 'selected' : '' }}>Diajukan (Menunggu Kepsek)</option>
                    <option value="approved_principal" {{ $status === 'approved_principal' ? 'selected' : '' }}>Disetujui Kepsek (Menunggu Bendahara)</option>
                    <option value="approved_treasurer" {{ $status === 'approved_treasurer' ? 'selected' : '' }}>Disetujui Bendahara (Siap Cair)</option>
                    <option value="disbursed" {{ $status === 'disbursed' ? 'selected' : '' }}>Sudah Cair & Terbuku</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                    Filter Pengeluaran
                </button>
                <a href="{{ route('expenses.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">No. Pengajuan</th>
                        <th class="py-3 px-4">Judul & Kategori</th>
                        <th class="py-3 px-4">Unit / Pengaju</th>
                        <th class="py-3 px-4">Jenis Belanja</th>
                        <th class="py-3 px-4 text-right">Nominal Diajukan</th>
                        <th class="py-3 px-4 text-right">Nominal Disetujui</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $exp)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 whitespace-nowrap"><x-code-badge :code="$exp->request_number" /></td>
                            <td class="py-3 px-4 min-w-[160px]">
                                <div class="font-bold text-slate-900">{{ $exp->title }}</div>
                                <div class="text-[10px] text-slate-400">{{ $exp->expenseCategory->name }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">{{ $exp->unit->code->value }}</span>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $exp->requestedBy->name }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="text-slate-600 font-medium">{{ $exp->expense_type->label() }}</span>
                            </td>
                            <td class="py-3 px-4 text-right font-medium text-slate-700 font-mono tabular-nums whitespace-nowrap">
                                {{ format_rupiah($exp->requested_amount) }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-700 font-mono tabular-nums whitespace-nowrap">
                                {{ $exp->approved_amount ? format_rupiah($exp->approved_amount) : '-' }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold
                                    {{ $exp->status->value === 'disbursed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $exp->status->value === 'approved_treasurer' ? 'bg-teal-100 text-teal-800' : '' }}
                                    {{ $exp->status->value === 'approved_principal' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $exp->status->value === 'submitted' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $exp->status->value === 'rejected' ? 'bg-rose-100 text-rose-800' : '' }}
                                    {{ $exp->status->value === 'draft' ? 'bg-slate-100 text-slate-600' : '' }}
                                ">
                                    {{ $exp->status->label() }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('expenses.show', $exp) }}" class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[11px] transition">
                                    Proses / Detail &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Belum ada data pengeluaran / pengajuan dana.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
