<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Masuk - Yayasan Generasi Robbani Sumatera Selatan</title>
    
    <!-- Modern Typography & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN & Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
        .bg-grid-pattern {
            background-image: radial-gradient(rgba(16, 185, 129, 0.12) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-3 sm:p-6 bg-slate-950 relative overflow-x-hidden" x-data="{ tab: 'demo' }">

    <!-- Ambient Background Glows -->
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none -translate-y-1/2"></div>
    <div class="fixed bottom-0 right-1/4 w-96 h-96 bg-teal-500/15 rounded-full blur-3xl pointer-events-none translate-y-1/2"></div>

    <div class="w-full max-w-5xl bg-slate-900/90 rounded-3xl shadow-2xl border border-slate-800 backdrop-blur-xl overflow-hidden grid grid-cols-1 lg:grid-cols-12 z-10">
        
        <!-- Left Column: Institutional Brand & Identity (5 cols) -->
        <div class="lg:col-span-5 bg-gradient-to-br from-emerald-950 via-slate-900 to-teal-950 p-8 sm:p-10 flex flex-col justify-between border-b lg:border-b-0 lg:border-r border-emerald-900/50 relative overflow-hidden">
            <!-- Subtle Grid Background -->
            <div class="absolute inset-0 bg-grid-pattern opacity-60 pointer-events-none"></div>

            <div class="relative z-10">
                <!-- Foundation Monogram & Header -->
                <div class="flex items-center gap-3.5 mb-8">
                    <div class="w-13 h-13 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 p-0.5 shadow-xl shadow-emerald-500/25">
                        <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center text-emerald-400">
                            <!-- Geometric Star / Crescent Icon -->
                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-400">SISTEM KEUANGAN TERPADU</span>
                        <h1 class="text-base font-extrabold text-white leading-tight">YAYASAN GENERASI ROBBANI</h1>
                        <p class="text-[11px] text-slate-400">Sumatera Selatan</p>
                    </div>
                </div>

                <!-- 4 Units Managed Tags -->
                <div class="mb-8">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-300/80 mb-2.5">
                        Layanan Keuangan 4 Unit Sekolah Islam Terpadu:
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="px-3 py-2 rounded-xl bg-white/5 border border-white/10 flex items-center gap-2 text-slate-200">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            <span class="font-semibold">KB/TKIT Robbani</span>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-white/5 border border-white/10 flex items-center gap-2 text-slate-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span class="font-semibold">SDIT Robbani</span>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-white/5 border border-white/10 flex items-center gap-2 text-slate-200">
                            <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                            <span class="font-semibold">SMPIT Robbani</span>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-white/5 border border-white/10 flex items-center gap-2 text-slate-200">
                            <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                            <span class="font-semibold">SMAIT Robbani</span>
                        </div>
                    </div>
                </div>

                <!-- Core Value Proposition -->
                <div class="space-y-3 text-xs text-slate-300">
                    <div class="flex items-start gap-2.5">
                        <div class="w-5 h-5 rounded-md bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span>Pusat Pembayaran SPP & Cicilan Siswa dengan e-Kwitansi PDF Resmi</span>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <div class="w-5 h-5 rounded-md bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span>Persetujuan Pengeluaran Berjenjang (Kepsek & Bendahara Yayasan)</span>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <div class="w-5 h-5 rounded-md bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span>Buku Kas Umum Real-time, Tutup Buku & Audit Trail ISO Standard</span>
                    </div>
                </div>
            </div>

            <!-- Footer Badge -->
            <div class="mt-8 pt-6 border-t border-emerald-900/60 relative z-10 flex items-center justify-between text-[11px] text-slate-400">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Sistem Operasional Aktif
                </span>
                <span>Palembang, Sumsel</span>
            </div>
        </div>

        <!-- Right Column: Interactive Login Portal (7 cols) -->
        <div class="lg:col-span-7 bg-white p-7 sm:p-10 flex flex-col justify-between">
            <div>
                <!-- Top Tabs / Mode Switcher -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Portal Masuk Keuangan</h2>
                        <p class="text-xs text-slate-500 mt-1">Pilih metode akses ke dashboard manajemen keuangan.</p>
                    </div>
                </div>

                <!-- Tab Segmented Pill -->
                <div class="flex p-1 rounded-2xl bg-slate-100 border border-slate-200/80 mb-6">
                    <button type="button" @click="tab = 'demo'"
                        :class="tab === 'demo' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800 font-medium'"
                        class="flex-1 py-2 px-3 rounded-xl text-xs transition flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Akses Pejabat & Admin Yayasan</span>
                    </button>
                    <button type="button" @click="tab = 'manual'"
                        :class="tab === 'manual' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800 font-medium'"
                        class="flex-1 py-2 px-3 rounded-xl text-xs transition flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                        <span>Login Email & Password</span>
                    </button>
                </div>

                <!-- Alert Messages -->
                @if($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-5 p-3.5 rounded-xl bg-sky-50 border border-sky-200 text-sky-800 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                <!-- TAB 1: PEJABAT & ADMIN SELECTION (One-Click Quick Access) -->
                <div x-show="tab === 'demo'" class="space-y-2.5 max-h-[380px] overflow-y-auto pr-1">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-1">
                        Daftar Pejabat & Pengelola Keuangan Terdaftar:
                    </div>

                    @foreach($demoUsers as $du)
                        @php
                            $roleName = $du->roles->first()?->name ?? 'User';
                            $isYayasan = !$du->unit_id;
                            $roleLabel = match($du->email) {
                                'superadmin@sit.sch.id' => 'Ketua Yayasan',
                                'bendahara@sit.sch.id' => 'Bendahara Yayasan',
                                'admin.yayasan@sit.sch.id' => 'Admin Yayasan (Pusat Pembayaran)',
                                'admin.tkit@sit.sch.id' => 'Admin Keuangan TKIT',
                                'admin.sdit@sit.sch.id' => 'Admin Keuangan SDIT',
                                'admin.smpit@sit.sch.id' => 'Admin Keuangan SMPIT',
                                'admin.smait@sit.sch.id' => 'Admin Keuangan SMAIT',
                                default => $du->roles->first()?->name ?? 'Pengguna',
                            };
                            $badgeColor = match(true) {
                                $du->email === 'superadmin@sit.sch.id' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                $du->email === 'bendahara@sit.sch.id' => 'bg-teal-100 text-teal-800 border-teal-200',
                                $du->email === 'admin.yayasan@sit.sch.id' => 'bg-amber-100 text-amber-900 border-amber-200',
                                str_starts_with($du->email, 'admin.tkit') => 'bg-amber-50 text-amber-800 border-amber-200',
                                str_starts_with($du->email, 'admin.sdit') => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                str_starts_with($du->email, 'admin.smpit') => 'bg-sky-50 text-sky-800 border-sky-200',
                                str_starts_with($du->email, 'admin.smait') => 'bg-purple-50 text-purple-800 border-purple-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                        @endphp
                        <form action="{{ route('quick-login', $du) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full p-3 rounded-2xl border border-slate-200/90 hover:border-emerald-500 bg-white hover:bg-emerald-50/40 text-left flex items-center justify-between transition-all duration-150 group shadow-2xs hover:shadow-sm">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 group-hover:bg-emerald-600 group-hover:text-white text-slate-700 flex items-center justify-center font-black text-sm shrink-0 transition">
                                        {{ mb_substr($du->name, 0, 1) }}
                                    </div>
                                    <div class="truncate">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-900 group-hover:text-emerald-800 transition truncate">{{ $du->name }}</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $badgeColor }} shrink-0">
                                                {{ $roleLabel }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                                            <span>{{ $du->email }}</span>
                                            <span>•</span>
                                            <span class="font-medium text-slate-500">{{ $du->unit?->name ?? 'Yayasan (Pusat)' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="pl-2 shrink-0">
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 group-hover:bg-emerald-600 text-slate-600 group-hover:text-white text-xs font-bold transition">
                                        Masuk
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                </div>
                            </button>
                        </form>
                    @endforeach
                </div>

                <!-- TAB 2: MANUAL LOGIN (Email & Password) -->
                <div x-show="tab === 'manual'" x-cloak>
                    <form action="{{ route('login') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email Resmi</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                                </div>
                                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs sm:text-sm bg-slate-50 focus:bg-white transition"
                                    placeholder="nama@sit.sch.id">
                            </div>
                        </div>

                        <div x-data="{ showPass: false }">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <input :type="showPass ? 'text' : 'password'" name="password" required
                                    class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs sm:text-sm bg-slate-50 focus:bg-white transition"
                                    placeholder="••••••••">
                                <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                                    <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs pt-1">
                            <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                                <input type="checkbox" name="remember" class="rounded text-emerald-600 focus:ring-emerald-500">
                                Ingat sesi login saya
                            </label>
                            <span class="text-slate-400">Default: <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-700">password123</code></span>
                        </div>

                        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm shadow-lg shadow-emerald-600/25 transition">
                            Masuk ke Dashboard Keuangan &rarr;
                        </button>
                    </form>
                </div>
            </div>

            <!-- Bottom Disclaimer & Copyright -->
            <div class="mt-8 pt-5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-400">
                <div>&copy; {{ date('Y') }} Yayasan Generasi Robbani Sumatera Selatan</div>
                <div class="flex items-center gap-1.5 text-slate-500">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>Sesi Terenkripsi & Diaudit Penuh</span>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
