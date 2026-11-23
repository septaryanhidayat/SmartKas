@extends('layouts.app', ['title' => 'Detail Pengeluaran: ' . $expense->request_number])

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ $expense->title }}</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold
                    {{ $expense->status->value === 'disbursed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                    {{ $expense->status->value === 'approved_treasurer' ? 'bg-teal-100 text-teal-800' : '' }}
                    {{ $expense->status->value === 'approved_principal' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $expense->status->value === 'submitted' ? 'bg-amber-100 text-amber-800' : '' }}
                    {{ $expense->status->value === 'rejected' ? 'bg-rose-100 text-rose-800' : '' }}
                ">
                    {{ $expense->status->label() }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">No. Pengajuan: <span class="font-mono font-bold text-slate-800">{{ $expense->request_number }}</span></p>
        </div>

        <a href="{{ route('expenses.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
            &larr; Kembali
        </a>
    </div>

    <!-- Expense Details Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-6">

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pb-6 border-b border-slate-100 text-xs">
            <div>
                <span class="text-slate-400 block mb-1">Unit & Pengaju</span>
                <div class="font-bold text-slate-900 text-sm">{{ $expense->unit->name }}</div>
                <div class="text-slate-500">Oleh: {{ $expense->requestedBy->name }}</div>
                <div class="text-slate-400 mt-1">Tanggal: {{ $expense->transaction_date->format('d/m/Y') }}</div>
            </div>

            <div>
                <span class="text-slate-400 block mb-1">Kategori & Jenis</span>
                <div class="font-bold text-slate-800">{{ $expense->expenseCategory->name }}</div>
                <div class="text-slate-500">{{ $expense->expense_type->label() }}</div>
                <div class="text-slate-500 mt-1">Akun: {{ $expense->cashAccount?->name ?? 'Belum ditentukan' }}</div>
            </div>

            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                <span class="text-slate-400 block mb-0.5">Nominal Pengeluaran</span>
                <div class="text-xs text-slate-500">Diajukan: <strong>{{ format_rupiah($expense->requested_amount) }}</strong></div>
                <div class="text-base font-black text-emerald-700 mt-1">
                    Disetujui: {{ $expense->approved_amount ? format_rupiah($expense->approved_amount) : format_rupiah($expense->requested_amount) }}
                </div>
            </div>
        </div>

        @if($expense->description)
            <div>
                <h4 class="font-bold text-xs uppercase tracking-wider text-slate-500 mb-2">Deskripsi & Keperluan</h4>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700 whitespace-pre-line">
                    {{ $expense->description }}
                </div>
            </div>
        @endif

        <!-- Approval History Timeline -->
        <div>
            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-500 mb-3">Jejak Persetujuan (Approval Trail)</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div class="p-3.5 rounded-xl border {{ $expense->principalApprovedBy ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    <div class="font-bold">1. Kepala Sekolah</div>
                    <div class="text-[11px] mt-1">{{ $expense->principalApprovedBy->name ?? 'Menunggu Persetujuan' }}</div>
                    @if($expense->principal_approved_at)
                        <div class="text-[10px] text-emerald-600 mt-0.5">{{ $expense->principal_approved_at->format('d/m/Y H:i') }}</div>
                    @endif
                </div>

                <div class="p-3.5 rounded-xl border {{ $expense->treasurerApprovedBy ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    <div class="font-bold">2. Bendahara Yayasan</div>
                    <div class="text-[11px] mt-1">{{ $expense->treasurerApprovedBy->name ?? 'Menunggu Persetujuan' }}</div>
                    @if($expense->treasurer_approved_at)
                        <div class="text-[10px] text-emerald-600 mt-0.5">{{ $expense->treasurer_approved_at->format('d/m/Y H:i') }}</div>
                    @endif
                </div>

                <div class="p-3.5 rounded-xl border {{ $expense->disbursedBy ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    <div class="font-bold">3. Pencairan Kas (Disbursed)</div>
                    <div class="text-[11px] mt-1">{{ $expense->disbursedBy->name ?? 'Belum Dicairkan' }}</div>
                    @if($expense->disbursed_at)
                        <div class="text-[10px] text-emerald-600 mt-0.5">{{ $expense->disbursed_at->format('d/m/Y H:i') }}</div>
                    @endif
                </div>
            </div>
        </div>

        @if($expense->rejection_reason)
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800">
                <strong>Alasan Penolakan:</strong> {{ $expense->rejection_reason }}
            </div>
        @endif

        <!-- Approval & Disbursement Actions -->
        @if(!in_array($expense->status->value, ['disbursed', 'rejected', 'void']))
            <div class="pt-6 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs text-slate-500">
                    Aksi Persetujuan untuk Pengajuan ini:
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Action: Approve Principal (Level 1) -->
                    @if($expense->status->value === 'submitted')
                        <form action="{{ route('expenses.approve-principal', $expense) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition">
                                Setujui (Kepala Sekolah) &rarr;
                            </button>
                        </form>
                    @endif

                    <!-- Action: Approve Treasurer (Level 2) -->
                    @if(in_array($expense->status->value, ['submitted', 'approved_principal']))
                        <form action="{{ route('expenses.approve-treasurer', $expense) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            <input type="number" name="approved_amount" value="{{ $expense->requested_amount }}" class="w-32 text-xs font-bold rounded-xl border border-slate-300 p-2" title="Sesuaikan nominal jika disetujui sebagian">
                            <button type="submit" class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md transition">
                                Setujui (Bendahara) &rarr;
                            </button>
                        </form>
                    @endif

                    <!-- Action: Disburse Funds -->
                    @if($expense->status->value === 'approved_treasurer')
                        <form action="{{ route('expenses.disburse', $expense) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            <select name="cash_account_id" required class="text-xs font-medium rounded-xl border border-slate-300 p-2">
                                @foreach($cashAccounts as $ca)
                                    <option value="{{ $ca->id }}" {{ $expense->cash_account_id == $ca->id ? 'selected' : '' }}>
                                        {{ $ca->name }} ({{ format_rupiah($ca->current_balance) }})
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" onclick="return confirm('Cairkan dana dan potong dari saldo kas sekarang?')" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition">
                                Cairkan Dana Sekarang
                            </button>
                        </form>
                    @endif

                    <!-- Action: Reject Modal -->
                    <div x-data="{ open: false }">
                        <button type="button" @click="open = true" class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition">
                            Tolak
                        </button>
                        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
                            <div @click.away="open = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
                                <h3 class="text-base font-bold text-slate-900">Tolak Pengajuan Dana</h3>
                                <form action="{{ route('expenses.reject', $expense) }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alasan Penolakan *</label>
                                        <textarea name="rejection_reason" required rows="3" class="w-full text-xs rounded-xl border border-slate-200 p-3" placeholder="Tulis alasan penolakan..."></textarea>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="open = false" class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-600 text-xs">Batal</button>
                                        <button type="submit" class="px-4 py-1.5 rounded-xl bg-rose-600 text-white font-bold text-xs">Tolak Pengajuan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>

</div>
@endsection
