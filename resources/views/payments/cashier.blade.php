@extends('layouts.app', ['title' => 'Kasir Pembayaran SPP (POS)'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-400 text-slate-950 uppercase tracking-wide">Point of Sale</span>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Kasir Penerimaan Pembayaran SPP</h2>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Pilih siswa, centang tagihan yang akan dibayar (lunas / cicilan), dan cetak e-Kwitansi PDF resmi.</p>
        </div>
    </div>

    <!-- Step 1: Select Student -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('payments.cashier') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            @if(!auth()->user()->unit_id)
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">1. Filter Unit</label>
                    <select name="unit_id" onchange="this.form.submit()" class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                        <option value="">-- Semua Unit Sekolah --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ (string)$unitId === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="{{ auth()->user()->unit_id ? 'sm:col-span-2' : '' }}">
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">2. Pilih Siswa Pembayar *</label>
                <select name="student_id" onchange="this.form.submit()" class="w-full text-xs font-bold rounded-xl border border-emerald-400 bg-emerald-50/30 px-3 py-2.5 text-slate-900 focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Nama / NIS Siswa --</option>
                    @foreach($students as $st)
                        <option value="{{ $st->id }}" {{ $selectedStudent && $selectedStudent->id === $st->id ? 'selected' : '' }}>
                            {{ $st->name }} (NIS: {{ $st->nis }} - {{ $st->unit->code->value }} / {{ $st->classroom?->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition">
                    Cari Tagihan Siswa
                </button>
            </div>
        </form>
    </div>

    @if($selectedStudent)
        <!-- Selected Student Bio & Active Discounts -->
        <div class="bg-gradient-to-r from-emerald-900 to-teal-950 p-5 rounded-2xl text-white shadow-md flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h3 class="text-xl font-black text-white tracking-tight">{{ $selectedStudent->name }}</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-800 text-emerald-200">
                        {{ $selectedStudent->unit->code->value }} • {{ $selectedStudent->classroom?->name }}
                    </span>
                </div>
                <div class="text-xs text-emerald-300 mt-1 space-x-3">
                    <span>NIS: <strong>{{ $selectedStudent->nis }}</strong></span>
                    <span>Wali: <strong>{{ $selectedStudent->parent_name }}</strong></span>
                    <span>WA: <strong>{{ $selectedStudent->parent_whatsapp }}</strong></span>
                </div>
            </div>

            @if($selectedStudent->studentDiscounts->isNotEmpty())
                <div class="bg-white/10 px-3 py-2 rounded-xl border border-white/10 text-xs">
                    <span class="text-emerald-300 font-bold">Beasiswa:</span>
                    @foreach($selectedStudent->studentDiscounts as $sd)
                        <span class="font-semibold text-white">{{ $sd->discount->name }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        @if($unpaidInvoices->isEmpty())
            <div class="bg-white p-10 rounded-2xl border border-slate-200/80 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto text-emerald-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="text-base font-bold text-slate-800">Alhamdulillah, Tidak Ada Tunggakan!</div>
                <p class="text-xs mt-1">Seluruh tagihan siswa ini telah lunas tercatat di sistem.</p>
            </div>
        @else
            <!-- Payment Form with Cart / Allocation Table -->
            <form action="{{ route('payments.store') }}" method="POST" id="paymentForm" class="space-y-6">
                @csrf
                <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">

                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Daftar Tagihan Belum Lunas (Centang yang akan dibayar)</span>
                        <span class="text-xs text-slate-500">Mendukung pembayaran cicilan / parsial</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="py-3 px-4 w-10 text-center">Pilih</th>
                                    <th class="py-3 px-4">No. Tagihan</th>
                                    <th class="py-3 px-4">Komponen Biaya</th>
                                    <th class="py-3 px-4">Periode</th>
                                    <th class="py-3 px-4 text-right">Tagihan Net</th>
                                    <th class="py-3 px-4 text-right">Sisa Belum Dibayar</th>
                                    <th class="py-3 px-4 text-right w-44">Jumlah Dibayar (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($unpaidInvoices as $index => $inv)
                                    <tr class="hover:bg-slate-50/80 transition" x-data="{ checked: true, amount: {{ $inv->remaining_amount }} }">
                                        <td class="py-3 px-4 text-center">
                                            <input type="checkbox" x-model="checked" :name="'items[' + {{ $index }} + '][invoice_id]'" value="{{ $inv->id }}" class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer item-checkbox">
                                        </td>
                                        <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $inv->invoice_number }}</td>
                                        <td class="py-3 px-4 font-semibold text-slate-900">
                                            {{ $inv->feeComponent->name }}
                                            @if($inv->discount_amount > 0)
                                                <div class="text-[10px] text-emerald-600">Diskon: {{ format_rupiah($inv->discount_amount) }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-slate-600">{{ $inv->billing_month ? "{$inv->billing_month}/{$inv->billing_year}" : '-' }}</td>
                                        <td class="py-3 px-4 text-right font-medium text-slate-700">{{ format_rupiah($inv->net_amount) }}</td>
                                        <td class="py-3 px-4 text-right font-bold text-amber-700">{{ format_rupiah($inv->remaining_amount) }}</td>
                                        <td class="py-3 px-4 text-right">
                                            <input type="number" :name="'items[' + {{ $index }} + '][amount]'" x-model.number="amount" :disabled="!checked" max="{{ $inv->remaining_amount }}" min="1"
                                                class="w-full text-right font-bold text-xs rounded-xl border border-slate-300 bg-white px-3 py-1.5 focus:ring-2 focus:ring-emerald-500 disabled:bg-slate-100 disabled:text-slate-400">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Details Card -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
                    <h4 class="font-bold text-base text-slate-900 mb-4">Detail Transaksi Pembayaran</h4>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Akun Kas / Bank Penerima *</label>
                            <select name="cash_account_id" required class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                                @foreach($cashAccounts as $ca)
                                    <option value="{{ $ca->id }}">{{ $ca->name }} (Saldo: {{ format_rupiah($ca->current_balance) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Metode Pembayaran *</label>
                            <select name="payment_method" required class="w-full text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                                <option value="cash">Tunai (Kasir)</option>
                                <option value="transfer">Transfer Bank Manual</option>
                                <option value="qris">QRIS Yayasan</option>
                                <option value="virtual_account">Virtual Account</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. Referensi / Bukti Transfer (Opsional)</label>
                            <input type="text" name="reference_number" placeholder="Misal: TRF-123456" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-xs text-slate-500">
                            * Kwitansi PDF resmi berkop surat sekolah akan otomatis dibuat dan siap dicetak / dikirim via WhatsApp.
                        </div>

                        <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-sm shadow-lg shadow-amber-500/20 transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Simpan Pembayaran & Buat Kwitansi
                        </button>
                    </div>
                </div>

            </form>
        @endif
    @endif

</div>
@endsection
