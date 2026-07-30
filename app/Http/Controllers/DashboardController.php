<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Redirect ke dashboard sesuai role pengguna.
     */
    public function index(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            return redirect()->route('super-admin.dashboard');
        }

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.siswa.index');
        }

        if ($user->hasRole('guru')) {
            return redirect()->route('guru.absensi.index');
        }

        if ($user->hasRole('siswa')) {
            return redirect()->route('siswa.riwayat.index');
        }

        // Fallback
        return redirect()->route('login');
    }
}
