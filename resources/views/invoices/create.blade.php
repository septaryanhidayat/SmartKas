@extends('layouts.app', ['title' => 'Generate Tagihan Siswa'])

@section('content')
<div class="space-y-6 max-w-4xl">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Generate Tagihan Siswa</h2>
            <p class="text-xs text-slate-500 mt-0.5">Buat tagihan massal bulanan rutin atau tagihan insidentil untuk siswa.</p>
        </div>
        <a href="{{ route('invoices.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
            &larr; Kembali ke Daftar Tagihan
        </a>
    </div>

    <!-- Tabs Container -->
    <div x-data="{ tab: 'monthly' }" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <div class="flex border-b border-slate-200">
            <button @click="tab = 'monthly'" :class="tab === 'monthly' ? 'border-b-2 border-emerald-600 text-emerald-700 font-bold bg-emerald-50/40' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-4 text-xs font-bold uppercase tracking-wider text-center transition">
                1. Auto-Generate Tagihan Bulanan (SPP Rutin)
            </button>
            <button @click="tab = 'incidental'" :class="tab === 'incidental' ? 'border-b-2 border-emerald-600 text-emerald-700 font-bold bg-emerald-50/40' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-4 text-xs font-bold uppercase tracking-wider text-center transition">
                2. Tagihan Insidentil / Tahunan (Uang Pangkal, Seragam, dll)
            </button>
        </div>

        <!-- Tab 1: Monthly Invoices -->
        <div x-show="tab === 'monthly'" class="p-6">
            <div class="mb-5 p-4 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 text-xs">
                <strong>Informasi Tagihan Rutin:</strong> Sistem akan otomatis mencari seluruh siswa aktif pada unit yang dipilih, menghitung tarif komponen bulanan dan potongan beasiswa masing-masing siswa, serta mencegah duplikasi tagihan untuk bulan & tahun yang sama.
            </div>

            <form action="{{ route('invoices.generate-monthly') }}" method="POST" class="space-y-4">
                @csrf
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
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tahun Ajaran *</label>
                        <select name="academic_year_id" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Bulan Tagihan *</label>
                        <select name="month" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                    Bulan {{ $m }} ({{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }})
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tahun Tagihan *</label>
                        <input type="number" name="year" value="{{ date('Y') }}" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Batas Jatuh Tempo (Due Date)</label>
                        <input type="date" name="due_date" value="{{ date('Y-m-10') }}" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" onclick="return confirm('Mulai proses generate tagihan bulanan untuk seluruh siswa aktif?')" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition">
                        Generate Tagihan Bulanan Sekarang &rarr;
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab 2: Incidental Invoices -->
        <div x-show="tab === 'incidental'" x-cloak class="p-6">
            <form action="{{ route('invoices.generate-incidental') }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Komponen Biaya *</label>
                        <select name="fee_component_id" required class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                            @foreach($feeComponents as $fc)
                                <option value="{{ $fc->id }}">{{ $fc->unit->code->value }} - {{ $fc->name }} (Standar: {{ format_rupiah($fc->default_amount) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nominal Custom (Kosongkan jika mengikuti tarif standar)</label>
                            <input type="number" name="custom_amount" placeholder="Contoh: 5000000" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Batas Jatuh Tempo</label>
                            <input type="date" name="due_date" value="{{ now()->addDays(14)->toDateString() }}" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                        </div>
                    </div>

                    <div x-data="{ studentFilter: '' }">
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase">Pilih Siswa Penerima Tagihan *</label>
                            <input type="text" x-model="studentFilter" placeholder="Cari cepat nama / NIS..." class="text-xs rounded-lg border border-slate-200 px-2.5 py-1 w-52 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div class="max-h-52 overflow-y-auto border border-slate-200 rounded-xl p-3 bg-slate-50 space-y-1.5 text-xs">
                            @foreach($students as $st)
                                <label data-search="{{ strtolower($st->name . ' ' . $st->nis . ' ' . $st->unit->code->value) }}"
                                    x-show="!studentFilter || $el.dataset.search.includes(studentFilter.toLowerCase())"
                                    class="flex items-center gap-2.5 hover:bg-white p-1.5 rounded cursor-pointer">
                                    <input type="checkbox" name="student_ids[]" value="{{ $st->id }}" class="rounded text-emerald-600 focus:ring-emerald-500">
                                    <span class="font-bold text-slate-800">{{ $st->name }}</span>
                                    <span class="text-slate-400 font-mono">({{ $st->nis }} - {{ $st->unit->code->value }} / {{ $st->classroom?->name }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Tagihan (Opsional)</label>
                        <input type="text" name="notes" placeholder="Misal: Uang Pangkal Gelombang 1" class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition">
                        Buat Tagihan Insidentil &rarr;
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection
