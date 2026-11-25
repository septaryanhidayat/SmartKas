@extends('layouts.app', ['title' => 'Laporan Surplus Defisit'])

@section('content')
<div class="space-y-6 max-w-4xl">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Laporan Surplus / Defisit (Laba Rugi)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Perbandingan total pendapatan SPP/kegiatan vs beban belanja operasional.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('reports.income-expense.excel', request()->query()) }}" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel
            </a>
            <a href="{{ route('reports.income-expense.pdf', request()->query()) }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export PDF
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('reports.income-expense') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            @if(!auth()->user()->unit_id)
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Cakupan Unit</label>
                    <select name="unit_id" class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <option value="">-- Seluruh Yayasan (Konsolidasi) --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ (string)$unitId === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                    Tampilkan Laporan
                </button>
            </div>
        </form>
    </div>

    <!-- Surplus / Defisit Financial Statement Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 bg-slate-50 border-b border-slate-200">
            <h3 class="font-bold text-base text-slate-900">
                {{ $report['unit'] ? $report['unit']->name : 'Konsolidasi Yayasan Generasi Robbani Sumatera Selatan' }}
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Periode: {{ $report['start_date']->translatedFormat('d F Y') }} s/d {{ $report['end_date']->translatedFormat('d F Y') }}</p>
        </div>

        <div class="p-6 space-y-6">

            <!-- 1. Income Section -->
            <div>
                <div class="flex items-center justify-between pb-2 border-b-2 border-emerald-600 mb-3">
                    <h4 class="font-extrabold text-xs uppercase tracking-wider text-emerald-800">1. PENDAPATAN OPERASIONAL & PENDIDIKAN</h4>
                    <span class="text-xs font-bold text-emerald-700">Subtotal</span>
                </div>
                <div class="space-y-2 text-xs">
                    @forelse($report['incomes'] as $inc)
                        <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                            <div>
                                <span class="font-bold text-slate-800">{{ $inc->name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">({{ $inc->code }} - {{ $inc->payment_count }} kwitansi)</span>
                            </div>
                            <span class="font-medium text-slate-900">{{ format_rupiah($inc->total_amount) }}</span>
                        </div>
                    @empty
                        <div class="text-center py-3 text-slate-400 italic">Belum ada pendapatan tercatat pada periode ini.</div>
                    @endforelse

                    <div class="flex items-center justify-between pt-2 font-bold text-sm text-emerald-900 bg-emerald-50/50 p-2 rounded-lg">
                        <span>TOTAL PENDAPATAN:</span>
                        <span>{{ format_rupiah($report['total_income']) }}</span>
                    </div>
                </div>
            </div>

            <!-- 2. Expense Section -->
            <div>
                <div class="flex items-center justify-between pb-2 border-b-2 border-rose-600 mb-3">
                    <h4 class="font-extrabold text-xs uppercase tracking-wider text-rose-800">2. BIAYA PENGELUARAN OPERASIONAL & ANGGARAN</h4>
                    <span class="text-xs font-bold text-rose-700">Subtotal</span>
                </div>
                <div class="space-y-2 text-xs">
                    @forelse($report['expenses'] as $exp)
                        <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                            <div>
                                <span class="font-bold text-slate-800">{{ $exp->name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">({{ $exp->code }} - {{ $exp->request_count }} transaksi)</span>
                            </div>
                            <span class="font-medium text-slate-900">{{ format_rupiah($exp->total_amount) }}</span>
                        </div>
                    @empty
                        <div class="text-center py-3 text-slate-400 italic">Belum ada pengeluaran tercatat pada periode ini.</div>
                    @endforelse

                    <div class="flex items-center justify-between pt-2 font-bold text-sm text-rose-900 bg-rose-50/50 p-2 rounded-lg">
                        <span>TOTAL BIAYA PENGELUARAN:</span>
                        <span>{{ format_rupiah($report['total_expense']) }}</span>
                    </div>
                </div>
            </div>

            <!-- 3. Bottomline Net Result: Surplus / Deficit -->
            <div class="p-5 rounded-2xl border-2 {{ $report['surplus_deficit'] >= 0 ? 'border-emerald-500 bg-emerald-50' : 'border-rose-500 bg-rose-50' }} flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider {{ $report['surplus_deficit'] >= 0 ? 'text-emerald-800' : 'text-rose-800' }}">
                        HASIL AKHIR BERSIH:
                    </span>
                    <h3 class="text-2xl font-black {{ $report['surplus_deficit'] >= 0 ? 'text-emerald-950' : 'text-rose-950' }}">
                        {{ $report['surplus_deficit'] >= 0 ? 'SURPLUS BERSIH' : 'DEFISIT' }}
                    </h3>
                </div>
                <div class="text-2xl font-black {{ $report['surplus_deficit'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ format_rupiah($report['surplus_deficit']) }}
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
