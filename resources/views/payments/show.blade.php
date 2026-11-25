@extends('layouts.app', ['title' => 'Kwitansi ' . $payment->receipt_number])

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Detail Pembayaran</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $payment->status->value === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    {{ $payment->status->label() }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">No. Kwitansi: <span class="font-mono font-bold text-slate-800">{{ $payment->receipt_number }}</span></p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('receipts.download', $payment) }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh Kwitansi PDF
            </a>
            <a href="{{ route('payments.index') }}" class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Payment Detail Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-6">

        <!-- Official Kop Surat Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b-2 border-[#0B3B6F]">
            <div class="flex items-center gap-4">
                <img src="{{ app_asset_url(app_setting('foundation_logo_path', 'images/yayasan-logo.png'), 'images/yayasan-logo.png') }}" alt="Logo Yayasan" class="h-12 w-auto object-contain shrink-0">
                <div>
                    <div class="text-[11px] font-extrabold text-[#0B3B6F] uppercase tracking-wide">
                        {{ app_setting('foundation_name', 'Yayasan Generasi Robbani Sumatera Selatan') }}
                    </div>
                    <div class="text-sm font-black text-teal-700 uppercase">{{ $payment->unit->name }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">
                        {{ app_setting('foundation_address', 'Jl. Sarjana Blok A No. 25 Timbangan, Indralaya Utara, Ogan Ilir') }}
                    </div>
                </div>
            </div>
            <div class="sm:text-right shrink-0">
                <div class="text-xs font-extrabold text-[#0B3B6F] tracking-widest uppercase">KWITANSI RESMI</div>
                <div class="mt-1"><x-code-badge :code="$payment->receipt_number" /></div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-6 border-b border-slate-100 text-xs">
            <div>
                <span class="text-slate-400 block mb-1">Informasi Siswa</span>
                <div class="font-bold text-base text-slate-900">{{ $payment->student->name }}</div>
                <div class="text-slate-500 mt-0.5">NIS: {{ $payment->student->nis }} | Kelas: {{ $payment->student->classroom?->name }} ({{ $payment->unit->name }})</div>
                <div class="text-slate-500">Wali: {{ $payment->student->parent_name }} (WA: {{ $payment->student->parent_whatsapp }})</div>
            </div>

            <div>
                <span class="text-slate-400 block mb-1">Informasi Transaksi</span>
                <div class="font-bold text-slate-800">Tanggal: {{ $payment->payment_date->translatedFormat('d F Y H:i:s') }}</div>
                <div class="text-slate-600">Metode: <strong class="text-slate-800">{{ $payment->payment_method->label() }}</strong></div>
                <div class="text-slate-600">Akun Kas: <strong class="text-slate-800">{{ $payment->cashAccount->name }}</strong></div>
                <div class="text-slate-400 mt-1">Dicatat oleh: {{ $payment->creator->name ?? 'Admin Keuangan' }}</div>
            </div>
        </div>

        <!-- Items Table -->
        <div>
            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-500 mb-3">Rincian Alokasi Tagihan</h4>
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold">
                            <th class="py-2.5 px-4">No. Tagihan</th>
                            <th class="py-2.5 px-4">Komponen Biaya</th>
                            <th class="py-2.5 px-4 text-right">Nominal Tagihan</th>
                            <th class="py-2.5 px-4 text-right">Jumlah Dibayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($payment->items as $item)
                            <tr>
                                <td class="py-3 px-4 whitespace-nowrap"><x-code-badge :code="$item->invoice->invoice_number" /></td>
                                <td class="py-3 px-4 font-semibold text-slate-900">
                                    {{ $item->invoice->feeComponent->name }}
                                    @if($item->invoice->billing_month)
                                        <span class="text-slate-400 text-[11px] font-normal">(Bulan {{ $item->invoice->billing_month }}/{{ $item->invoice->billing_year }})</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-medium text-slate-600 font-mono tabular-nums whitespace-nowrap">{{ format_rupiah($item->invoice->net_amount) }}</td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-700 font-mono tabular-nums whitespace-nowrap">{{ format_rupiah($item->amount) }}</td>
                            </tr>
                        @endforeach
                        <tr class="bg-slate-50/80 font-bold text-sm">
                            <td colspan="3" class="py-3 px-4 text-right">TOTAL DIBAYAR:</td>
                            <td class="py-3 px-4 text-right text-emerald-700 font-mono tabular-nums whitespace-nowrap">{{ format_rupiah($payment->total_amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Terbilang Box -->
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900">
            <strong>Terbilang:</strong> {{ ucwords(terbilang($payment->total_amount)) }} Rupiah.
        </div>

        @if($payment->status->value === 'void')
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 space-y-1">
                <div class="font-bold text-rose-900 uppercase tracking-wider">Kwitansi Ini Dibatalkan (Void)</div>
                <div>Alasan: {{ $payment->void_reason }}</div>
                <div class="text-[11px] text-rose-600">Dibatalkan oleh {{ $payment->voidedBy->name ?? 'Petugas' }} pada {{ $payment->voided_at?->translatedFormat('d F Y H:i') }}</div>
            </div>
        @elseif(auth()->user()->can('void-payments'))
            <!-- Void Cancellation Form (Only for Treasurer / Superadmin) -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                <div class="text-xs text-slate-400">
                    Salah input transaksi pembayaran? Lakukan pembatalan (void) dengan catatan alasan wajib.
                </div>

                <div x-data="{ open: false }">
                    <button @click="open = true" class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition">
                        Batalkan Kwitansi (Void)
                    </button>

                    <!-- Modal Void -->
                    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
                        <div @click.away="open = false" class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4">
                            <h3 class="text-lg font-bold text-slate-900">Konfirmasi Pembatalan Kwitansi (Void)</h3>
                            <p class="text-xs text-slate-500">
                                Pembatalan kwitansi akan otomatis memulihkan saldo sisa tagihan invoice siswa dan membuat jurnal pembalik di Buku Kas Umum.
                            </p>

                            <form action="{{ route('payments.void', $payment) }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alasan Pembatalan *</label>
                                    <textarea name="void_reason" required rows="3" class="w-full text-xs rounded-xl border border-slate-200 p-3 focus:ring-2 focus:ring-rose-500" placeholder="Jelaskan alasan pembatalan (misal: salah input nominal)..."></textarea>
                                </div>

                                <div class="flex justify-end gap-2 pt-2">
                                    <button type="button" @click="open = false" class="px-3 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold">
                                        Tutup
                                    </button>
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md">
                                        Proses Void Sekarang
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>

</div>
@endsection
