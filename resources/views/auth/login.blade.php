<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Keuangan Terpusat Yayasan SIT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="h-full flex items-center justify-center p-6">
    <div class="w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 bg-white rounded-3xl shadow-2xl border border-slate-200/80 overflow-hidden">
        
        <!-- Left: Login Form -->
        <div class="p-8 lg:p-10 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600 flex items-center justify-center text-white font-black text-2xl shadow-lg shadow-emerald-600/30">
                        SIT
                    </div>
                    <div>
                        <h2 class="font-extrabold text-xl text-slate-900 tracking-tight">SISTEM KEUANGAN</h2>
                        <p class="text-xs text-emerald-700 font-semibold tracking-wide">YAYASAN PENDIDIKAN ISLAM TERPADU</p>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-lg font-bold text-slate-800">Masuk ke Akun Anda</h3>
                    <p class="text-xs text-slate-500 mt-1">Kelola kas, tagihan SPP, pencairan dana & pelaporan 4 unit SIT.</p>
                </div>

                @if($errors->any())
                    <div class="mb-5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm bg-slate-50 focus:bg-white transition"
                            placeholder="nama@sit.sch.id">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi</label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm bg-slate-50 focus:bg-white transition"
                            placeholder="••••••••">
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded text-emerald-600 focus:ring-emerald-500">
                            Ingat saya di perangkat ini
                        </label>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-lg shadow-emerald-600/30 transition">
                        Masuk Sekarang
                    </button>
                </form>
            </div>

            <div class="mt-8 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} Yayasan Pendidikan Islam Terpadu. All rights reserved.
            </div>
        </div>

        <!-- Right: Demo Quick Login Selector -->
        <div class="bg-gradient-to-br from-emerald-950 via-emerald-900 to-teal-950 p-8 lg:p-10 text-white flex flex-col justify-between">
            <div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-800/80 text-emerald-200 mb-4 border border-emerald-700">
                    ⚡ Mode Cepat (Demo Akun)
                </span>
                <h3 class="text-xl font-black text-white tracking-tight">Pilih Akun Demo Uji Coba</h3>
                <p class="text-xs text-emerald-200/80 mt-1 mb-6">
                    Klik tombol di bawah untuk langsung login sesuai peran masing-masing:
                </p>

                <div class="space-y-2.5">
                    @foreach($demoUsers as $du)
                        <form action="{{ route('quick-login', $du) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full p-3 rounded-xl bg-white/10 hover:bg-white/20 border border-white/10 text-left flex items-center justify-between transition group">
                                <div>
                                    <div class="text-sm font-bold text-white group-hover:text-emerald-300 transition">{{ $du->name }}</div>
                                    <div class="text-xs text-emerald-300/80">
                                        {{ $du->email }} • <span class="capitalize">{{ $du->roles->first()?->name ?? 'User' }}</span>
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-emerald-300 opacity-0 group-hover:opacity-100 transition">
                                    Login &rarr;
                                </span>
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 p-3 rounded-xl bg-emerald-900/60 border border-emerald-800 text-[11px] text-emerald-200">
                <strong>Password Default Demo:</strong> <code class="bg-emerald-950 px-1.5 py-0.5 rounded text-white font-mono">password123</code>
            </div>
        </div>

    </div>
</body>
</html>
