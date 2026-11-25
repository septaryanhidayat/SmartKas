@php
    $appSetting = app(\App\Services\AppSettingService::class);
    $appName = app_setting('app_name', 'SmartKas');
    $appTagline = app_setting('app_tagline', 'Sistem Keuangan Terpusat');
    $foundationName = app_setting('foundation_name', 'Yayasan Generasi Robbani Sumatera Selatan');
    $foundationAddress = app_setting('foundation_address', 'Jl. Sarjana Blok A No. 25 Timbangan, Indralaya Utara, Ogan Ilir');
    $academicYear = app_setting('academic_year_label', '2026/2027');
    $appLogoUrl = app_asset_url(app_setting('app_logo_path', 'images/smartkas-horizontal.png'));
    $appIconUrl = app_asset_url(app_setting('app_icon_path', 'images/smartkas-icon.png'), 'images/smartkas-icon.png');
    $activeTheme = $appSetting->activeTheme();
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50" style="font-size: {{ $appSetting->activeFontSizePx() }}px;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - {{ $appName }} | {{ $foundationName }}</title>
    <link rel="icon" type="image/png" href="{{ $appIconUrl }}">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --font-sans: {!! $appSetting->activeFontStack() !!};
        }
        body { font-family: var(--font-sans) !important; }
    </style>
</head>
<body class="min-h-full bg-slate-100/80 text-slate-800 antialiased flex items-center justify-center p-4 sm:p-6 relative overflow-x-hidden">

    <!-- Subtle Decorative Background -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
        <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-[#0B3B6F]/10 blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-[#0D9488]/10 blur-3xl"></div>
    </div>

    <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl shadow-slate-900/10 border border-slate-200/90 overflow-hidden grid grid-cols-1 lg:grid-cols-12 relative z-10">

        <!-- Left Column: Institutional Identity (5 cols) -->
        <div class="lg:col-span-5 p-8 sm:p-9 text-white flex flex-col justify-between relative overflow-hidden" style="background: {{ $activeTheme['gradient'] }};">
            <!-- Subtle Geometric Glow -->
            <div class="absolute -right-16 -bottom-16 w-56 h-56 rounded-full bg-teal-400/15 blur-2xl pointer-events-none"></div>

            <div class="relative z-10 space-y-7">
                <!-- App Official Logo on Crisp White Card -->
                <div class="bg-white rounded-2xl px-5 py-3.5 shadow-lg inline-flex items-center justify-center">
                    <img src="{{ $appLogoUrl }}" alt="{{ $appName }} Logo" class="h-10 w-auto object-contain">
                </div>

                <div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-white/10 text-teal-200 border border-white/15 mb-3">
                        {{ $appTagline }}
                    </span>
                    <h1 class="text-lg font-extrabold tracking-tight leading-snug text-white">
                        {{ $foundationName }}
                    </h1>
                    <p class="text-xs text-slate-200/85 mt-1.5 leading-relaxed">
                        Platform keuangan terpusat untuk pengelolaan kas, tagihan SPP, pencairan anggaran, dan pelaporan akuntabel seluruh unit SIT.
                    </p>
                </div>

                <!-- 4 School Units Badges -->
                <div class="space-y-2">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-teal-200/90">
                        Unit Sekolah Terintegrasi
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="px-3 py-2 rounded-xl bg-white/10 border border-white/10 font-semibold text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-300 shrink-0"></span>
                            <span>KB/TKIT Robbani</span>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-white/10 border border-white/10 font-semibold text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-300 shrink-0"></span>
                            <span>SDIT Robbani</span>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-white/10 border border-white/10 font-semibold text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-300 shrink-0"></span>
                            <span>SMPIT Robbani</span>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-white/10 border border-white/10 font-semibold text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-300 shrink-0"></span>
                            <span>SMAIT Robbani</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Institutional Location Footer -->
            <div class="mt-8 pt-5 border-t border-white/15 relative z-10 flex items-center justify-between gap-3 text-[11px] text-slate-200/85">
                <span class="flex items-start gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-teal-400 shrink-0 mt-1"></span>
                    <span>{{ $foundationAddress }}</span>
                </span>
                <span class="font-semibold text-teal-200 shrink-0">TA {{ $academicYear }}</span>
            </div>
        </div>

        <!-- Right Column: Clean Standard Login Form (7 cols) -->
        <div class="lg:col-span-7 p-8 sm:p-11 flex flex-col justify-between bg-white">
            <div class="my-auto">
                <!-- Mobile Logo Preview -->
                <div class="lg:hidden mb-6 flex justify-center">
                    <img src="{{ $appLogoUrl }}" alt="{{ $appName }} Logo" class="h-10 w-auto object-contain">
                </div>

                <div class="mb-7">
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Masuk ke Akun Anda</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1.5">
                        Gunakan email resmi <span class="font-semibold text-slate-700">@sitrobbani.sch.id</span> dan kata sandi Anda.
                    </p>
                </div>

                <!-- Validation & Status Alerts -->
                @if($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-2.5">
                        <svg class="w-4 h-4 shrink-0 text-rose-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-5 p-3.5 rounded-xl bg-sky-50 border border-sky-200 text-sky-800 text-xs flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                <!-- Login Credentials Form -->
                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Alamat Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/>
                                </svg>
                            </div>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                                class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                                placeholder="nama@sitrobbani.sch.id">
                        </div>
                    </div>

                    <div x-data="{ showPass: false }">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input id="password" :type="showPass ? 'text' : 'password'" name="password" required autocomplete="current-password"
                                class="w-full pl-11 pr-11 py-3 rounded-xl border border-slate-200 bg-slate-50/70 text-sm text-slate-900 placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                                placeholder="Masukkan kata sandi">
                            <button type="button" @click="showPass = !showPass" aria-label="Tampilkan atau sembunyikan kata sandi"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 transition">
                                <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPass" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="inline-flex items-center gap-2 text-slate-600 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                            <span>Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-[#0B3B6F] to-[#0D9488] hover:from-[#082E58] hover:to-[#0F766E] text-white font-bold text-sm shadow-lg shadow-[#0B3B6F]/20 transition cursor-pointer flex items-center justify-center gap-2">
                        <span>Masuk Sekarang</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Security & Copyright Footer -->
            <div class="mt-8 pt-5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-400">
                <span>&copy; {{ date('Y') }} {{ $appName }} &bull; {{ $foundationName }}</span>
                <span class="flex items-center gap-1 text-slate-500 font-medium">
                    <svg class="w-3.5 h-3.5 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Akses Terbatas &amp; Diaudit
                </span>
            </div>
        </div>

    </div>

</body>
</html>
