@extends('layouts.app', ['title' => 'Pengaturan Aplikasi'])

@section('content')
<div class="space-y-6" x-data="{ tab: '{{ $activeTab }}', addUserOpen: false, editUser: null }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Pengaturan Aplikasi &amp; Sistem</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola identitas yayasan, logo kop &amp; invoice, jenis &amp; ukuran huruf, data unit sekolah, serta akun admin.</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-2 shadow-xs flex flex-wrap gap-1.5">
        @php
            $tabs = [
                'general' => ['label' => 'Identitas, Alamat & Logo', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                'invoice' => ['label' => 'Invoice, Kwitansi & Kode', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                'appearance' => ['label' => 'Jenis Huruf, Ukuran & Tema', 'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'],
                'units' => ['label' => 'Unit Sekolah & Kepsek', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                'users' => ['label' => 'Akun Admin & Pengguna', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ];
        @endphp

        @foreach($tabs as $key => $meta)
            <button type="button"
                    @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'bg-[#0B3B6F] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
                    class="px-3.5 py-2.5 rounded-xl text-xs font-bold transition inline-flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['icon'] }}"/>
                </svg>
                <span>{{ $meta['label'] }}</span>
            </button>
        @endforeach
    </div>

    <!-- TAB 1: IDENTITAS, ALAMAT & LOGO -->
    <div x-show="tab === 'general'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
        <form action="{{ route('settings.update-general') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h3 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-2">Identitas Aplikasi &amp; Yayasan</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Aplikasi *</label>
                            <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 focus:bg-white focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tahun Ajaran Aktif *</label>
                            <input type="text" name="academic_year_label" value="{{ old('academic_year_label', $settings['academic_year_label']) }}" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 focus:bg-white focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Subjudul / Tagline Aplikasi *</label>
                        <input type="text" name="app_tagline" value="{{ old('app_tagline', $settings['app_tagline']) }}" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 focus:bg-white focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Resmi Yayasan *</label>
                            <input type="text" name="foundation_name" value="{{ old('foundation_name', $settings['foundation_name']) }}" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 focus:bg-white focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Singkat (Sidebar) *</label>
                            <input type="text" name="foundation_short_name" value="{{ old('foundation_short_name', $settings['foundation_short_name']) }}" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 focus:bg-white focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Resmi Kop Surat &amp; Invoice *</label>
                        <textarea name="foundation_address" rows="2" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 focus:bg-white focus:ring-2 focus:ring-teal-500">{{ old('foundation_address', $settings['foundation_address']) }}</textarea>
                        <label class="inline-flex items-center gap-2 mt-2 text-[11px] text-slate-600 cursor-pointer">
                            <input type="checkbox" name="sync_unit_addresses" value="1" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                            <span>Samakan alamat ini ke seluruh unit sekolah (TKIT, SDIT, SMPIT, SMAIT)</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Telepon Yayasan</label>
                            <input type="text" name="foundation_phone" value="{{ old('foundation_phone', $settings['foundation_phone']) }}" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Resmi</label>
                            <input type="email" name="foundation_email" value="{{ old('foundation_email', $settings['foundation_email']) }}" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                        </div>
                    </div>
                </div>

                <!-- Right Column: Brand & Foundation Logos -->
                <div class="space-y-4">
                    <h3 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-2">Logo Aplikasi &amp; Logo Kop Invoice</h3>

                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 space-y-2.5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-xs font-bold text-slate-800">Logo Utama Aplikasi (Sidebar &amp; Login)</div>
                                <div class="text-[11px] text-slate-400">Format PNG/JPG transparan atau horizontal (maks 2 MB)</div>
                            </div>
                            <div class="bg-white px-3 py-2 rounded-xl border border-slate-200 shrink-0">
                                <img src="{{ app_asset_url($settings['app_logo_path']) }}" alt="Logo Aplikasi" class="h-9 w-auto object-contain">
                            </div>
                        </div>
                        <input type="file" name="app_logo" accept="image/png,image/jpeg,image/webp" class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#0B3B6F] file:text-white hover:file:bg-[#062547] cursor-pointer">
                    </div>

                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 space-y-2.5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-xs font-bold text-slate-800">Ikon / Favicon Browser</div>
                                <div class="text-[11px] text-slate-400">Format PNG persegi (maks 1 MB)</div>
                            </div>
                            <div class="bg-white p-2 rounded-xl border border-slate-200 shrink-0">
                                <img src="{{ app_asset_url($settings['app_icon_path'], 'images/smartkas-icon.png') }}" alt="Ikon" class="h-9 w-9 object-contain">
                            </div>
                        </div>
                        <input type="file" name="app_icon" accept="image/png,image/jpeg,image/webp" class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-700 file:text-white hover:file:bg-slate-900 cursor-pointer">
                    </div>

                    <div class="p-4 rounded-xl border border-teal-200 bg-teal-50/40 space-y-2.5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-xs font-bold text-slate-900">Logo Kop Surat Yayasan (Invoice &amp; Kwitansi PDF)</div>
                                <div class="text-[11px] text-slate-500">Digunakan di header surat tagihan, kwitansi resmi &amp; laporan PDF</div>
                            </div>
                            <div class="bg-white px-3 py-2 rounded-xl border border-teal-200 shrink-0">
                                <img src="{{ app_asset_url($settings['foundation_logo_path'], 'images/yayasan-logo.png') }}" alt="Logo Yayasan" class="h-11 w-auto object-contain">
                            </div>
                        </div>
                        <input type="file" name="foundation_logo" accept="image/png,image/jpeg,image/webp" class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-teal-700 file:text-white hover:file:bg-teal-800 cursor-pointer">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md transition cursor-pointer">
                    Simpan Identitas &amp; Logo
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 2: INVOICE, KWITANSI & FORMAT KODE -->
    <div x-show="tab === 'invoice'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-6">
        <form action="{{ route('settings.update-invoice') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Prefix Nomor Invoice Baru *</label>
                    <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $settings['invoice_prefix']) }}" required class="w-full text-xs font-mono font-bold uppercase rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                    <span class="text-[10px] text-slate-400">Contoh hasil: {{ strtoupper($settings['invoice_prefix']) }}/SDIT/202611/0001</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Prefix Nomor Kwitansi Baru *</label>
                    <input type="text" name="receipt_prefix" value="{{ old('receipt_prefix', $settings['receipt_prefix']) }}" required class="w-full text-xs font-mono font-bold uppercase rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                    <span class="text-[10px] text-slate-400">Contoh hasil: {{ strtoupper($settings['receipt_prefix']) }}/SDIT/202611/0001</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Jatuh Tempo Default *</label>
                    <input type="number" min="1" max="28" name="default_due_day" value="{{ old('default_due_day', $settings['default_due_day']) }}" required class="w-full text-xs font-mono font-bold rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                    <span class="text-[10px] text-slate-400">Setiap tanggal 1 s/d 28 tiap bulan</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tampilan Kode Transaksi Panjang di Tabel *</label>
                    <select name="code_display_mode" class="w-full text-xs font-semibold rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                        <option value="compact" {{ $settings['code_display_mode'] === 'compact' ? 'selected' : '' }}>
                            Ringkas Rapi (Contoh: OCT2026…8RUYX + Tombol Salin)
                        </option>
                        <option value="full" {{ $settings['code_display_mode'] === 'full' ? 'selected' : '' }}>
                            Tampilkan Kode Lengkap Penuh
                        </option>
                    </select>
                    <span class="text-[10px] text-slate-400">Mode ringkas mencegah tabel melebar dan nominal terpotong.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jabatan Penandatangan PDF *</label>
                    <input type="text" name="receipt_signer_title" value="{{ old('receipt_signer_title', $settings['receipt_signer_title']) }}" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Kaki Invoice &amp; Kwitansi PDF</label>
                <textarea name="invoice_footer_note" rows="2" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">{{ old('invoice_footer_note', $settings['invoice_footer_note']) }}</textarea>
            </div>

            <div>
                <label class="inline-flex items-center gap-2.5 text-xs font-bold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="show_foundation_logo_on_pdf" value="1" {{ $settings['show_foundation_logo_on_pdf'] === '1' ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <span>Tampilkan Logo Resmi Yayasan pada Kop Surat Invoice, Kwitansi &amp; Laporan PDF</span>
                </label>
            </div>

            <!-- Live Kop Invoice Preview -->
            <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/70">
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-3">Pratinjau Kop Surat Invoice &amp; Kwitansi</div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center justify-between gap-4 border-b-2 border-b-[#0B3B6F]">
                    <div class="flex items-center gap-4">
                        @if($settings['show_foundation_logo_on_pdf'] === '1')
                            <img src="{{ app_asset_url($settings['foundation_logo_path'], 'images/yayasan-logo.png') }}" alt="Logo Yayasan" class="h-12 w-auto object-contain shrink-0">
                        @endif
                        <div>
                            <div class="text-[11px] font-extrabold text-[#0B3B6F] uppercase">{{ $settings['foundation_name'] }}</div>
                            <div class="text-sm font-black text-teal-700 uppercase">SDIT ROBBANI (CONTOH UNIT)</div>
                            <div class="text-[11px] text-slate-500">{{ $settings['foundation_address'] }} | Telp: {{ $settings['foundation_phone'] }}</div>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-xs font-black text-[#0B3B6F]">INVOICE TAGIHAN</div>
                        <div class="text-[11px] font-mono font-bold text-slate-600">{{ strtoupper($settings['invoice_prefix']) }}/SDIT/{{ date('Ym') }}/0001</div>
                    </div>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md transition cursor-pointer">
                    Simpan Pengaturan Invoice
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 3: JENIS HURUF, UKURAN HURUF & TEMA -->
    <div x-show="tab === 'appearance'" x-cloak class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
        <form action="{{ route('settings.update-appearance') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Jenis Huruf Aplikasi (Font Family) *</label>
                    <select name="font_family" class="w-full text-xs font-semibold rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                        @foreach($fontOptions as $key => $meta)
                            <option value="{{ $key }}" {{ $settings['font_family'] === $key ? 'selected' : '' }}>
                                {{ $meta['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Ukuran Huruf Dasar (Font Size) *</label>
                    <select name="font_size" class="w-full text-xs font-semibold rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                        @foreach($fontSizes as $sizeVal => $sizeLabel)
                            <option value="{{ $sizeVal }}" {{ (string) $settings['font_size'] === (string) $sizeVal ? 'selected' : '' }}>
                                {{ $sizeLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Kerapatan Tabel Data *</label>
                    <select name="table_density" class="w-full text-xs font-semibold rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                        <option value="comfortable" {{ $settings['table_density'] === 'comfortable' ? 'selected' : '' }}>Nyaman (Spasi Lega)</option>
                        <option value="compact" {{ $settings['table_density'] === 'compact' ? 'selected' : '' }}>Padat (Lebih Banyak Baris Terlihat)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Tema Warna Sidebar &amp; Banner *</label>
                    <select name="theme_color" class="w-full text-xs font-semibold rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                        @foreach($themeColors as $key => $theme)
                            <option value="{{ $key }}" {{ $settings['theme_color'] === $key ? 'selected' : '' }}>
                                {{ $theme['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md transition cursor-pointer">
                    Terapkan Pengaturan Tampilan
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 4: DATA UNIT SEKOLAH & KEPSEK -->
    <div x-show="tab === 'units'" x-cloak class="space-y-4">
        @foreach($units as $u)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                <form action="{{ route('settings.update-unit', $u) }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                    @csrf
                    <div class="md:col-span-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-slate-100 text-[#0B3B6F]">{{ $u->code->value }}</span>
                        <input type="text" name="name" value="{{ $u->name }}" required class="mt-1.5 w-full text-xs font-bold rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Kepala Sekolah / Pimpinan</label>
                        <input type="text" name="principal_name" value="{{ $u->principal_name }}" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Telepon</label>
                        <input type="text" name="phone" value="{{ $u->phone }}" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Alamat Unit</label>
                        <input type="text" name="address" value="{{ $u->address }}" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                    </div>
                    <div class="md:col-span-2 flex items-center justify-between gap-2">
                        <label class="inline-flex items-center gap-1.5 text-[11px] text-slate-600 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ $u->is_active ? 'checked' : '' }} {{ $u->code === \App\Enums\UnitCode::YAYASAN ? 'disabled checked' : '' }} class="rounded border-slate-300 text-teal-600">
                            <span>Aktif</span>
                        </label>
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#0B3B6F] hover:bg-[#062547] text-white font-bold text-xs transition cursor-pointer">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>

    <!-- TAB 5: MANAJEMEN AKUN ADMIN & PENGGUNA -->
    <div x-show="tab === 'users'" x-cloak class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Daftar Akun Admin, Bendahara &amp; Kepala Sekolah</h3>
                <p class="text-xs text-slate-500">Tambah akun staf baru, ubah penugasan unit, reset kata sandi, atau nonaktifkan akses pengguna.</p>
            </div>
            <button type="button" @click="addUserOpen = true" class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md transition cursor-pointer shrink-0">
                + Tambah Akun Baru
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nama &amp; Jabatan</th>
                            <th class="py-3 px-4">Email Login</th>
                            <th class="py-3 px-4">Role Sistem</th>
                            <th class="py-3 px-4">Cakupan Unit</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($users as $usr)
                            @php
                                $roleName = $usr->roles->first()?->name ?? 'finance_admin';
                                $userPayload = [
                                    'id' => $usr->id,
                                    'name' => $usr->name,
                                    'email' => $usr->email,
                                    'phone' => $usr->phone ?? '',
                                    'role' => $roleName,
                                    'unit_id' => $usr->unit_id ?? '',
                                    'is_active' => $usr->is_active,
                                    'update_url' => route('settings.users.update', $usr),
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">{{ $usr->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $usr->displayRoleTitle() }}</div>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-700 whitespace-nowrap">{{ $usr->email }}</td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 uppercase">{{ $roleName }}</span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    {{ $usr->unit?->name ?? 'Pusat Yayasan (Semua Unit)' }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $usr->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $usr->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" @click="editUser = @js($userPayload)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#0B3B6F] text-slate-700 hover:text-white font-bold text-[10px] transition cursor-pointer">
                                            Edit
                                        </button>
                                        @if($usr->id !== auth()->id())
                                            <form action="{{ route('settings.users.destroy', $usr) }}" method="POST" onsubmit="return confirm('Nonaktifkan dan hapus akun {{ $usr->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white font-bold text-[10px] transition cursor-pointer">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL: TAMBAH AKUN BARU -->
    <div x-show="addUserOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div @click.away="addUserOpen = false" class="bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl space-y-4">
            <h3 class="text-lg font-bold text-slate-900">Tambah Akun Pengguna Baru</h3>
            <form action="{{ route('settings.users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Login *</label>
                        <input type="email" name="email" required placeholder="nama@sitrobbani.sch.id" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Role *</label>
                        <select name="role" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ strtoupper($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Unit Tugas</label>
                        <select name="unit_id" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                            <option value="">Pusat Yayasan (Semua Unit)</option>
                            @foreach($schoolUnits as $su)
                                <option value="{{ $su->id }}">{{ $su->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. HP / WhatsApp</label>
                        <input type="text" name="phone" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-teal-600">
                            <span>Akun Aktif</span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kata Sandi *</label>
                        <input type="password" name="password" required minlength="8" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ulangi Kata Sandi *</label>
                        <input type="password" name="password_confirmation" required minlength="8" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="addUserOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT AKUN PENGGUNA -->
    <div x-show="editUser !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div @click.away="editUser = null" class="bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl space-y-4">
            <h3 class="text-lg font-bold text-slate-900">Edit Akun Pengguna</h3>
            <template x-if="editUser">
                <form :action="editUser.update_url" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap *</label>
                            <input type="text" name="name" x-model="editUser.name" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Login *</label>
                            <input type="email" name="email" x-model="editUser.email" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Role *</label>
                            <select name="role" x-model="editUser.role" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ strtoupper($role->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Unit Tugas</label>
                            <select name="unit_id" x-model="editUser.unit_id" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                                <option value="">Pusat Yayasan (Semua Unit)</option>
                                @foreach($schoolUnits as $su)
                                    <option value="{{ $su->id }}">{{ $su->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. HP / WhatsApp</label>
                            <input type="text" name="phone" x-model="editUser.phone" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                        </div>
                        <div class="flex items-end pb-2">
                            <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" x-model="editUser.is_active" class="rounded border-slate-300 text-teal-600">
                                <span>Akun Aktif</span>
                            </label>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Password Baru (Opsional)</label>
                            <input type="password" name="password" minlength="8" placeholder="Kosongkan jika tetap" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ulangi Password Baru</label>
                            <input type="password" name="password_confirmation" minlength="8" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="editUser = null" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[#0B3B6F] hover:bg-[#062547] text-white text-xs font-bold shadow-md">Update Akun</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
