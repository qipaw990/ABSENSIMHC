<?php

use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GuruApiController;
use App\Http\Controllers\Api\SiswaApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Sistem Absensi QR Code
|--------------------------------------------------------------------------
| Autentikasi menggunakan Laravel Sanctum (Bearer Token).
| Mobile app login → dapat token → gunakan token di setiap request.
*/

// ── Auth (Publik) ──────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login',  [AuthController::class, 'login']);
});

// ── Protected Routes (butuh Bearer Token) ─────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);
    });

    // ── Guru / Wali Kelas ──────────────────────────────────────────────────
    Route::middleware('role:guru|admin|super_admin')->prefix('guru')->group(function () {
        Route::get('kelas',                          [GuruApiController::class, 'kelasList']);
        Route::get('kelas/{id}/stats',               [GuruApiController::class, 'kelasStats']);
        Route::get('kelas/{id}/belum-scan',          [GuruApiController::class, 'belumScan']);
        Route::post('absensi/scan',                  [GuruApiController::class, 'scan']);
        Route::get('absensi/rekap/{kelas_id}',       [GuruApiController::class, 'rekap']);
    });

    // ── Siswa ──────────────────────────────────────────────────────────────
    Route::middleware('role:siswa')->prefix('siswa')->group(function () {
        Route::get('profile',           [SiswaApiController::class, 'profile']);
        Route::get('absensi',           [SiswaApiController::class, 'riwayat']);
        Route::get('absensi/stats',     [SiswaApiController::class, 'stats']);
    });

    // ── Admin / Super Admin ────────────────────────────────────────────────
    Route::middleware('role:admin|super_admin')->prefix('admin')->group(function () {
        Route::get('dashboard', [AdminApiController::class, 'dashboard']);
    });
});
