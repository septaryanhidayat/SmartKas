<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sistem Keuangan Yayasan SIT' }}</title>

    <!-- Tailwind CSS CDN Fallback & Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full antialiased text-slate-800 flex" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs lg:hidden"></div>

    <!-- Sidebar Navigation -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-72 bg-emerald-950 text-white flex flex-col transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:inset-auto">
        <!-- Logo & Branding -->
        <div class="h-20 flex items-center gap-3 px-6 border-b border-emerald-900 bg-emerald-900/40">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-emerald-500/30">
                SIT
            </div>
            <div>
                <h1 class="font-bold text-sm tracking-wide text-white">KEUANGAN SIT</h1>
                <p class="text-xs text-emerald-300">Yayasan Pendidikan Islam</p>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1.5 text-sm font-medium">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white font-semibold shadow-md' : 'text-emerald-100 hover:bg-emerald-900/60' }}">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <!-- Quick Action POS: Kasir Pembayaran -->
            <div class="pt-2 pb-1">
                <a href="{{ route('payments.cashier') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold shadow-lg transition">
                    <span class="flex items-center gap-2.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Kasir SPP (Bayar)
                    </span>
                    <span class="text-[10px] bg-slate-900/20 px-1.5 py-0.5 rounded uppercase tracking-wider font-extrabold">POS</span>
                </a>
            </div>

            <div class="pt-4 pb-1 text-[11px] font-bold text-emerald-400/80 uppercase tracking-wider px-3">
                Pemasukan (Piutang)
            </div>
            <a href="{{ route('invoices.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('invoices.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-emerald-100 hover:bg-emerald-900/60' }}">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Tagihan Siswa
            </a>
            <a href="{{ route('payments.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('payments.index', 'payments.show') ? 'bg-emerald-600 text-white font-semibold' : 'text-emerald-100 hover:bg-emerald-900/60' }}">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Riwayat Kwitansi
            </a>
            <a href="{{ route('students.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('students.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-emerald-100 hover:bg-emerald-900/60' }}">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Data Siswa & Kelas
            </a>

            <div class="pt-4 pb-1 text-[11px] font-bold text-emerald-400/80 uppercase tracking-wider px-3">
                Pengeluaran & Anggaran
            </div>
            <a href="{{ route('expenses.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('expenses.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-emerald-100 hover:bg-emerald-900/60' }}">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Pengajuan Dana & Kas Kecil
            </a>

            <div class="pt-4 pb-1 text-[11px] font-bold text-emerald-400/80 uppercase tracking-wider px-3">
                Laporan & Akuntansi
            </div>
            <a href="{{ route('reports.cash-ledger') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('reports.cash-ledger') ? 'bg-emerald-600 text-white font-semibold' : 'text-emerald-100 hover:bg-emerald-900/60' }}">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Buku Kas Umum
            </a>
            <a href="{{ route('reports.income-expense') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('reports.income-expense') ? 'bg-emerald-600 text-white font-semibold' : 'text-emerald-100 hover:bg-emerald-900/60' }}">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Surplus / Defisit (Laba Rugi)
            </a>
            <a href="{{ route('reports.arrears') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('reports.arrears') ? 'bg-emerald-600 text-white font-semibold' : 'text-emerald-100 hover:bg-emerald-900/60' }}">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Rekap Tunggakan Siswa
            </a>

            <div class="pt-4 pb-1 text-[11px] font-bold text-emerald-400/80 uppercase tracking-wider px-3">
                Keamanan & Audit
            </div>
            <a href="{{ route('closing-periods.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('closing-periods.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-emerald-100 hover:bg-emerald-900/60' }}">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Tutup Buku Bulanan
            </a>
            <a href="{{ route('audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('audit-logs.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-emerald-100 hover:bg-emerald-900/60' }}">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Audit Trail (Log)
            </a>
        </nav>

        <!-- Current User Profile in Footer -->
        <div class="p-4 border-t border-emerald-900 bg-emerald-950/80">
            <div class="flex items-center justify-between">
                <div class="truncate mr-2">
                    <div class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-emerald-300 truncate">
                        {{ auth()->user()->unit?->code->value ?? 'Yayasan (Pusat)' }}
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Keluar / Logout" class="p-2 rounded-lg bg-emerald-900/60 hover:bg-rose-600 text-emerald-200 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Navigation Bar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 z-10">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        {{ auth()->user()->unit?->name ?? 'Yayasan SIT (Konsolidasi Pusat)' }}
                    </span>
                    <span class="text-xs text-slate-400 font-medium hidden sm:inline">• Tahun Ajaran Aktif 2026/2027</span>
                </div>
            </div>

            <!-- Role Badge & Help -->
            <div class="flex items-center gap-3">
                <span class="text-xs px-2.5 py-1 rounded-md font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    Role: {{ auth()->user()->roles->first()?->name ?? 'User' }}
                </span>
            </div>
        </header>

        <!-- Flash Messages -->
        <main class="flex-1 overflow-y-auto bg-slate-50 p-6">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error') || $errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs">
                    <div class="flex items-center gap-2 font-semibold">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Terjadi Kesalahan:</span>
                    </div>
                    <ul class="list-disc list-inside mt-1 ml-5 text-xs text-rose-700 space-y-0.5">
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
        </main>
    </div>

</body>
</html>
