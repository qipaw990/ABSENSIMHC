<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // Redirect sesuai role
        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            return redirect()->intended(route('super-admin.dashboard'));
        }
        if ($user->hasRole('admin')) {
            return redirect()->intended(route('admin.siswa.index'));
        }
        if ($user->hasRole('guru')) {
            return redirect()->intended(route('guru.absensi.index'));
        }
        if ($user->hasRole('siswa')) {
            return redirect()->intended(route('siswa.riwayat.index'));
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
