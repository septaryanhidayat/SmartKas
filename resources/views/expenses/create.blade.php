@extends('layouts.app', ['title' => 'Input Pengeluaran / Pengajuan Dana'])

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Form Pengeluaran & Pengajuan Dana</h2>
            <p class="text-xs text-slate-500 mt-0.5">Catat pengeluaran langsung operasional atau ajukan proposal RAB berjenjang.</p>
        </div>
        <a href="{{ route('expenses.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6" x-data="{ isDirect: true }">
        <form action="{{ route('expenses.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Mode Selection: Langsung vs Pengajuan Approval -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                <div>
                    <div class="font-bold text-slate-800">Metode Pencatatan:</div>
                    <div class="text-slate-500" x-text="isDirect ? 'Biaya operasional langsung dipotong dari saldo kas/bank saat ini' : 'Pengajuan dana membutuhkan approval Kepala Sekolah dan Bendahara Yayasan'"></div>
                </div>
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-1.5 cursor-pointer font-bold text-slate-700">
                        <input type="radio" name="is_direct" :value="1" x-model="isDirect" class="text-emerald-600 focus:ring-emerald-500">
                        Belanja Langsung
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer font-bold text-slate-700">
                        <input type="radio" name="is_direct" :value="0" x-model="isDirect" class="text-emerald-600 focus:ring-emerald-500">
                        Pengajuan (Approval)
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Unit Sekolah *</label>
                    <select name="unit_id" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ (string)$unitId === (string)$u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->code->value }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori Biaya *</label>
                    <select name="expense_category_id" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Judul / Keperluan Pengeluaran *</label>
                    <input type="text" name="title" required placeholder="Misal: Pembayaran Listrik & Internet Bulan Ini / Pengadaan ATK Ujian" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenis Belanja *</label>
                    <select name="expense_type" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                        <option value="operational">Biaya Operasional Rutin</option>
                        <option value="petty_cash">Kas Kecil (Petty Cash)</option>
                        <option value="cash_advance">Pengajuan Dana / RAB Kegiatan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nominal Biaya (Rp) *</label>
                    <input type="number" name="amount" required min="1000" placeholder="Contoh: 1500000" class="w-full text-xs font-bold rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Transaksi</label>
                    <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Akun Kas / Bank Pemotong *</label>
                    <select name="cash_account_id" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                        <option value="">-- Tentukan Akun Kas / Bank --</option>
                        @foreach($cashAccounts as $ca)
                            <option value="{{ $ca->id }}">{{ $ca->name }} (Saldo: {{ format_rupiah($ca->current_balance) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan Tambahan / Rincian</label>
                    <textarea name="description" rows="3" placeholder="Rincian item belanja..." class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 p-3"></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition">
                    Simpan Pengeluaran &rarr;
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
