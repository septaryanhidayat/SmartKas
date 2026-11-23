@extends('layouts.app', ['title' => 'Profil Siswa: ' . $student->name])

@section('content')
<div class="space-y-6">

    <!-- Header & Student Summary -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ $student->name }}</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                    {{ $student->unit->code->value }} • {{ $student->classroom?->name }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">NIS: <span class="font-mono font-bold text-slate-800">{{ $student->nis }}</span> | Orang Tua: <span class="font-bold text-slate-800">{{ $student->parent_name }}</span> (WA: {{ $student->parent_whatsapp }})</p>
        </div>

        <div>
            <a href="{{ route('payments.cashier', ['student_id' => $student->id]) }}" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-md transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Buka Kasir Pembayaran Siswa
            </a>
        </div>
    </div>

    <!-- Active Discounts Section -->
    <div class="bg-emerald-50/60 p-4 rounded-2xl border border-emerald-200 text-xs">
        <h3 class="font-bold text-emerald-950 mb-1">Potongan / Beasiswa Aktif:</h3>
        @forelse($student->studentDiscounts as $sd)
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white rounded-lg border border-emerald-300 text-emerald-800 font-semibold mr-2 my-1">
                <span>{{ $sd->discount->name }}</span>
                <span class="text-[10px] text-emerald-600">({{ $sd->valid_from->format('d/m/Y') }} s/d {{ $sd->valid_until ? $sd->valid_until->format('d/m/Y') : 'Seterusnya' }})</span>
            </div>
        @empty
            <p class="text-emerald-700">Siswa ini membayar tagihan dengan tarif reguler penuh (tanpa beasiswa).</p>
        @endforelse
    </div>

    <!-- Invoices List -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h3 class="font-bold text-base text-slate-900">Daftar Tagihan Siswa (Semua Status)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">No. Tagihan</th>
                        <th class="py-3 px-4">Komponen Biaya</th>
                        <th class="py-3 px-4">Bulan/Tahun</th>
                        <th class="py-3 px-4">Jatuh Tempo</th>
                        <th class="py-3 px-4 text-right">Tagihan Net</th>
                        <th class="py-3 px-4 text-right">Sudah Dibayar</th>
                        <th class="py-3 px-4 text-right">Sisa Tagihan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($student->invoices as $inv)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $inv->invoice_number }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-900">{{ $inv->feeComponent->name }}</td>
                            <td class="py-3 px-4">{{ $inv->billing_month ? "{$inv->billing_month}/{$inv->billing_year}" : '-' }}</td>
                            <td class="py-3 px-4">{{ $inv->due_date->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-right font-medium">{{ format_rupiah($inv->net_amount) }}</td>
                            <td class="py-3 px-4 text-right font-medium text-emerald-600">{{ format_rupiah($inv->paid_amount) }}</td>
                            <td class="py-3 px-4 text-right font-bold {{ $inv->remaining_amount > 0 ? 'text-amber-700' : 'text-slate-400' }}">
                                {{ format_rupiah($inv->remaining_amount) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                    {{ $inv->status->value === 'paid' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $inv->status->value === 'partial' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $inv->status->value === 'unpaid' ? 'bg-rose-100 text-rose-800' : '' }}
                                    {{ $inv->status->value === 'void' ? 'bg-slate-200 text-slate-600' : '' }}
                                ">
                                    {{ $inv->status->label() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400">Belum ada tagihan untuk siswa ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
