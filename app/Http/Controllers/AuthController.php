<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $demoUsers = User::with(['unit', 'roles'])->orderByRaw("CASE 
            WHEN email = 'superadmin@sit.sch.id' THEN 1
            WHEN email = 'bendahara@sit.sch.id' THEN 2
            WHEN email = 'admin.yayasan@sit.sch.id' THEN 3
            WHEN email LIKE 'admin.tkit%' THEN 4
            WHEN email LIKE 'admin.sdit%' THEN 5
            WHEN email LIKE 'admin.smpit%' THEN 6
            WHEN email LIKE 'admin.smait%' THEN 7
            ELSE 8 END")->get();

        return view('auth.login', compact('demoUsers'));
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, (bool) $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Kombinasi email dan password yang Anda masukkan tidak sesuai.',
        ])->onlyInput('email');
    }

    public function quickLogin(Request $request, User $user): RedirectResponse
    {
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', "Selamat datang, {$user->name}!");
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil logout.');
    }
}
