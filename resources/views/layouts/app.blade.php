<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' - ' : '' }}Sistem Keuangan Yayasan Generasi Robbani</title>

    <!-- Google Fonts & Tailwind CDN & Alpine.js -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.05); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(16, 185, 129, 0.2); border-radius: 9999px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(16, 185, 129, 0.4); }
    </style>
</head>
<body class="h-full antialiased text-slate-800 bg-slate-50/60 flex" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" 
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-xs lg:hidden"></div>

    <!-- Sidebar Navigation -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
           class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-950 text-white flex flex-col transition-transform duration-250 ease-in-out lg:translate-x-0 lg:static lg:inset-auto border-r border-slate-800/80 shadow-2xl lg:shadow-none">
        
        <!-- Foundation Branding Header -->
        <div class="h-20 flex items-center gap-3 px-5 border-b border-slate-800/80 bg-slate-900/60">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 p-0.5 shadow-lg shadow-emerald-500/20 shrink-0">
                <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center text-emerald-400 font-extrabold text-lg">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
            </div>
            <div class="min-w-0">
                <div class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-400">SIMKEU TERPADU</div>
                <h1 class="font-extrabold text-sm tracking-tight text-white truncate">GENERASI ROBBANI</h1>
                <p class="text-[10px] text-slate-400 truncate">4 Unit SIT • Sumatera Selatan</p>
            </div>
        </div>

        <!-- Navigation Links (Scrollable) -->
        <nav class="flex-1 overflow-y-auto px-3.5 py-5 space-y-1 text-xs font-semibold custom-scrollbar">
            
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                <svg class="w-4.5 h-4.5 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-emerald-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span>Dashboard Utama</span>
            </a>

            <!-- Quick Action POS: Kasir Pembayaran SPP -->
            <div class="pt-2 pb-2">
                <a href="{{ route('payments.cashier') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-extrabold shadow-lg shadow-amber-500/20 transition transform active:scale-98">
                    <span class="flex items-center gap-2.5">
                        <svg class="w-4.5 h-4.5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Kasir SPP (POS)</span>
                    </span>
                    <span class="text-[10px] bg-slate-950/20 px-1.5 py-0.5 rounded-md font-black">BAYAR</span>
                </a>
            </div>

            <!-- SECTION: PENERIMAAN & PIUTANG -->
            <div class="pt-3 pb-1 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest px-3">
                Penerimaan Siswa
            </div>
            
            <a href="{{ route('invoices.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('invoices.*') ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                <svg class="w-4.5 h-4.5 {{ request()->routeIs('invoices.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Tagihan Siswa (Invoices)</span>
            </a>

            <a href="{{ route('payments.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('payments.index', 'payments.show') ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                <svg class="w-4.5 h-4.5 {{ request()->routeIs('payments.index', 'payments.show') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span>Riwayat Kwitansi Pembayaran</span>
            </a>

            <a href="{{ route('students.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('students.*') ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                <svg class="w-4.5 h-4.5 {{ request()->routeIs('students.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Data Siswa & Kelas</span>
            </a>

            <!-- SECTION: PENGELUARAN & ANGGARAN -->
            <div class="pt-4 pb-1 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest px-3">
                Pengeluaran & Anggaran
            </div>

            <a href="{{ route('expenses.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('expenses.*') ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                <svg class="w-4.5 h-4.5 {{ request()->routeIs('expenses.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Pengajuan Belanja & Kas Kecil</span>
            </a>

            <!-- SECTION: LAPORAN KEUANGAN KONSOLIDASI -->
            <div class="pt-4 pb-1 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest px-3">
                Laporan & Konsolidasi
            </div>

            <a href="{{ route('reports.cash-ledger') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('reports.cash-ledger*') ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                <svg class="w-4.5 h-4.5 {{ request()->routeIs('reports.cash-ledger*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>Buku Kas Umum (BKU)</span>
            </a>

            <a href="{{ route('reports.income-expense') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('reports.income-expense*') ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                <svg class="w-4.5 h-4.5 {{ request()->routeIs('reports.income-expense*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Surplus / Defisit (Laba Rugi)</span>
            </a>

            <a href="{{ route('reports.arrears') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('reports.arrears*') ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                <svg class="w-4.5 h-4.5 {{ request()->routeIs('reports.arrears*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Rekap Tunggakan SPP</span>
            </a>

            <!-- SECTION: TATA KELOLA & AUDIT -->
            <div class="pt-4 pb-1 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest px-3">
                Tata Kelola & Audit
            </div>

            <a href="{{ route('closing-periods.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('closing-periods.*') ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                <svg class="w-4.5 h-4.5 {{ request()->routeIs('closing-periods.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Tutup Buku Bulanan</span>
            </a>

            <a href="{{ route('audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('audit-logs.*') ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                <svg class="w-4.5 h-4.5 {{ request()->routeIs('audit-logs.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>Audit Trail (Log Aktivitas)</span>
            </a>

        </nav>

        <!-- Current User Profile in Footer -->
        <div class="p-3.5 border-t border-slate-800 bg-slate-900/90">
            @php
                $userRoleName = auth()->user()->roles->first()?->name ?? 'User';
                $roleBadge = match(auth()->user()->email) {
                    'superadmin@sit.sch.id' => 'Ketua Yayasan',
                    'bendahara@sit.sch.id' => 'Bendahara Yayasan',
                    'admin.yayasan@sit.sch.id' => 'Admin Pusat Pembayaran',
                    'admin.tkit@sit.sch.id' => 'Admin TKIT',
                    'admin.sdit@sit.sch.id' => 'Admin SDIT',
                    'admin.smpit@sit.sch.id' => 'Admin SMPIT',
                    'admin.smait@sit.sch.id' => 'Admin SMAIT',
                    default => $userRoleName,
                };
            @endphp
            <div class="flex items-center justify-between gap-2.5">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white font-extrabold text-sm flex items-center justify-center shrink-0 shadow-sm">
                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="truncate">
                        <div class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-emerald-400 font-semibold truncate">{{ $roleBadge }}</div>
                    </div>
                </div>
                
                <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" title="Keluar / Logout" class="p-2 rounded-xl bg-slate-800 hover:bg-rose-600/90 text-slate-300 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Top Institutional Header Bar -->
        <header class="h-16 bg-white/90 backdrop-blur-md border-b border-slate-200/90 flex items-center justify-between px-4 sm:px-6 z-20 sticky top-0 shadow-2xs">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        {{ auth()->user()->unit?->name ?? 'Yayasan Generasi Robbani (Pusat Konsolidasi)' }}
                    </span>
                    <span class="text-xs text-slate-400 font-medium hidden md:inline">• TA 2026/2027</span>
                </div>
            </div>

            <!-- Top Header Right: Quick Kasir POS + User Role Tag -->
            <div class="flex items-center gap-2.5">
                <a href="{{ route('payments.cashier') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 text-xs font-bold transition">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Kasir POS</span>
                </a>

                <div class="px-3 py-1 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold hidden sm:block">
                    {{ $roleBadge }}
                </div>
            </div>
        </header>

        <!-- Flash Messages & Main Body -->
        <main class="flex-1 overflow-y-auto bg-slate-50/70 p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6">

                <!-- Success Alert -->
                @if(session('success'))
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/90 text-emerald-900 text-xs sm:text-sm flex items-center justify-between shadow-2xs">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="font-medium">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Error Alert -->
                @if(session('error') || $errors->any())
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200/90 text-rose-900 text-xs sm:text-sm shadow-2xs">
                        <div class="flex items-center gap-2 font-bold mb-1">
                            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Perhatian / Terjadi Kesalahan:</span>
                        </div>
                        <ul class="list-disc list-inside ml-6 text-xs text-rose-800 space-y-0.5">
                            @if(session('error'))
                                <li>{{ session('error') }}</li>
                            @endif
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')

            </div>
        </main>
    </div>

</body>
</html>
