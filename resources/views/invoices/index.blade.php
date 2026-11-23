@extends('layouts.app', ['title' => 'Daftar Tagihan Siswa'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Tagihan & Billing Siswa</h2>
            <p class="text-xs text-slate-500 mt-0.5">Daftar tagihan rutin SPP bulanan dan insidentil per unit sekolah.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('invoices.create') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Generate Tagihan Baru
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
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
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Status Pembayaran</label>
                <select name="status" class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                    <option value="">-- Semua Status --</option>
                    <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Belum Dibayar (Unpaid)</option>
                    <option value="partial" {{ $status === 'partial' ? 'selected' : '' }}>Cicilan (Partial)</option>
                    <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                    <option value="void" {{ $status === 'void' ? 'selected' : '' }}>Batal (Void)</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Cari No. Tagihan / Siswa</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="No. invoice, nama, atau NIS..." class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                    Filter Tagihan
                </button>
                <a href="{{ route('invoices.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">No. Tagihan</th>
                        <th class="py-3 px-4">Siswa & Unit</th>
                        <th class="py-3 px-4">Komponen Biaya</th>
                        <th class="py-3 px-4">Periode / Tempo</th>
                        <th class="py-3 px-4 text-right">Nominal Tagihan</th>
                        <th class="py-3 px-4 text-right">Dibayar</th>
                        <th class="py-3 px-4 text-right">Sisa Tagihan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $inv->invoice_number }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $inv->student->name }}</div>
                                <div class="text-[11px] text-slate-500 font-medium">
                                    {{ $inv->unit->code->value }} • {{ $inv->student->classroom?->name ?? 'NIS: ' . $inv->student->nis }}
                                </div>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $inv->feeComponent->name }}
                                @if($inv->discount_amount > 0)
                                    <div class="text-[10px] text-emerald-600 font-medium">Potongan: {{ format_rupiah($inv->discount_amount) }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-700">{{ $inv->billing_month ? "Bulan {$inv->billing_month}/{$inv->billing_year}" : 'Insidentil' }}</div>
                                <div class="text-[10px] text-slate-400">Tempo: {{ $inv->due_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="py-3 px-4 text-right font-medium text-slate-900">{{ format_rupiah($inv->net_amount) }}</td>
                            <td class="py-3 px-4 text-right font-medium text-emerald-600">{{ format_rupiah($inv->paid_amount) }}</td>
                            <td class="py-3 px-4 text-right font-bold {{ $inv->remaining_amount > 0 ? 'text-amber-700' : 'text-slate-400' }}">
                                {{ format_rupiah($inv->remaining_amount) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold
                                    {{ $inv->status->value === 'paid' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $inv->status->value === 'partial' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $inv->status->value === 'unpaid' ? 'bg-rose-100 text-rose-800' : '' }}
                                    {{ $inv->status->value === 'void' ? 'bg-slate-200 text-slate-600' : '' }}
                                ">
                                    {{ $inv->status->label() }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($inv->status->value !== 'paid' && $inv->status->value !== 'void')
                                    <a href="{{ route('payments.cashier', ['student_id' => $inv->student_id]) }}" class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-[10px] transition">
                                        Bayar
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[10px]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">Belum ada data tagihan yang sesuai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
