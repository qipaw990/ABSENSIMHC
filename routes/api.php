<?php

use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GuruApiController;
use App\Http\Controllers\Api\SiswaApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Sistem Absensi QR Code (qpawdeveloper)
|--------------------------------------------------------------------------
| Autentikasi menggunakan Laravel Sanctum (Bearer Token).
| Mobile app login → dapat token → gunakan token di setiap request.
*/

// Nonaktifkan CSRF dan Session middleware secara mutlak untuk seluruh rute API
Route::withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
])->group(function () {

    // ── Root API Check (Publik) ────────────────--------------------------------
    Route::get('/', function () {
        return response()->json([
            'status'    => 'online',
            'service'   => 'RESTful API Sistem Absensi MHC',
            'developer' => 'qpawdeveloper',
            'version'   => '1.0.0',
            'endpoints' => [
                'login'  => 'POST /api/auth/login',
                'health' => 'GET /up',
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    // ── Auth (Publik) ──────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
    });

    // ── Protected Routes (butuh Bearer Token) ─────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth & Pengaturan Profil
        Route::prefix('auth')->group(function () {
            Route::post('logout',          [AuthController::class, 'logout']);
            Route::get('me',               [AuthController::class, 'me']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
            Route::post('fcm-token',       [AuthController::class, 'updateDeviceToken']);
        });

        // ── Guru / Wali Kelas ──────────────────────────────────────────────────
        Route::middleware('role:guru|admin|super_admin')->prefix('guru')->group(function () {
            Route::get('kelas',                    [GuruApiController::class, 'kelasList']);
            Route::get('kelas/{id}/stats',         [GuruApiController::class, 'kelasStats']);
            Route::get('kelas/{id}/belum-scan',    [GuruApiController::class, 'belumScan']);
            Route::post('absensi/scan',            [GuruApiController::class, 'scan']);
            Route::post('absensi/manual',          [GuruApiController::class, 'inputManual']);
            Route::get('absensi/rekap/{kelas_id}', [GuruApiController::class, 'rekap']);
            Route::put('absensi/{id}',             [GuruApiController::class, 'updateAbsensi']);
            Route::delete('absensi/{id}',          [GuruApiController::class, 'deleteAbsensi']);
        });

        // ── Siswa ──────────────────────────────────────────────────────────────
        Route::middleware('role:siswa')->prefix('siswa')->group(function () {
            Route::get('profile',       [SiswaApiController::class, 'profile']);
            Route::post('qr-refresh',   [SiswaApiController::class, 'refreshQr']);
            Route::get('absensi',       [SiswaApiController::class, 'riwayat']);
            Route::get('absensi/stats', [SiswaApiController::class, 'stats']);
            Route::post('izin-sakit',   [SiswaApiController::class, 'pengajuanIzin']);
        });

        // ── Admin / Super Admin ────────────────────────────────────────────────
        Route::middleware('role:admin|super_admin')->prefix('admin')->group(function () {
            Route::get('dashboard', [AdminApiController::class, 'dashboard']);
            Route::get('siswa',     [AdminApiController::class, 'siswaList']);
            Route::get('guru',      [AdminApiController::class, 'guruList']);
            Route::get('wa-sender', [AdminApiController::class, 'waSenderList']);
            Route::get('wa-logs',   [AdminApiController::class, 'waLogsList']);
        });
    });

});
