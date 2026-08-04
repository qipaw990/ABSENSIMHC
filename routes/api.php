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
            Route::post('update-profile',  [AuthController::class, 'updateProfile']);
            Route::post('update-photo',    [AuthController::class, 'updateProfile']);
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
            Route::get('jadwal',                   [GuruApiController::class, 'jadwal']);
            Route::get('mapel',                    [GuruApiController::class, 'mapelList']);
            Route::get('penilaian/options',        [GuruApiController::class, 'penilaianOptions']);
            Route::get('penilaian/create',         [GuruApiController::class, 'penilaianOptions']);
            Route::get('penilaian',                [GuruApiController::class, 'penilaianList']);
            Route::get('penilaian/{id}',           [GuruApiController::class, 'penilaianDetail']);
            Route::post('penilaian',               [GuruApiController::class, 'penilaianStore']);
            Route::put('penilaian/{id}',            [GuruApiController::class, 'penilaianUpdate']);
            Route::delete('penilaian/{id}',         [GuruApiController::class, 'penilaianDestroy']);
            Route::post('penilaian/{id}/nilai-batch', [GuruApiController::class, 'penilaianNilaiBatch']);
        });

        // ── Siswa ──────────────────────────────────────────────────────────────
        Route::middleware('role:siswa')->prefix('siswa')->group(function () {
            Route::get('profile',       [SiswaApiController::class, 'profile']);
            Route::post('qr-refresh',   [SiswaApiController::class, 'refreshQr']);
            Route::get('absensi',       [SiswaApiController::class, 'riwayat']);
            Route::get('absensi/stats', [SiswaApiController::class, 'stats']);
            Route::post('izin-sakit',   [SiswaApiController::class, 'pengajuanIzin']);
            Route::get('jadwal',        [SiswaApiController::class, 'jadwal']);
            Route::get('nilai',         [SiswaApiController::class, 'nilai']);
        });

        // ── Admin / Super Admin ────────────────────────────────────────────────
        Route::middleware('role:admin|super_admin')->prefix('admin')->group(function () {
            Route::get('dashboard',          [AdminApiController::class, 'dashboard']);

            // Rekap Absensi Global Admin
            Route::get('absensi/rekap',      [AdminApiController::class, 'absensiRekap']);
            
            // Siswa Suite
            Route::get('siswa',              [AdminApiController::class, 'siswaList']);
            Route::get('siswa/{id}',         [AdminApiController::class, 'siswaDetail']);
            Route::post('siswa',             [AdminApiController::class, 'siswaStore']);
            Route::put('siswa/{id}',         [AdminApiController::class, 'siswaUpdate']);
            Route::delete('siswa/{id}',      [AdminApiController::class, 'siswaDestroy']);

            // Guru Suite
            Route::get('guru',               [AdminApiController::class, 'guruList']);
            Route::post('guru',              [AdminApiController::class, 'guruStore']);
            Route::put('guru/{id}',          [AdminApiController::class, 'guruUpdate']);
            Route::delete('guru/{id}',       [AdminApiController::class, 'guruDestroy']);

            // Kelas Suite
            Route::get('kelas',              [AdminApiController::class, 'kelasList']);
            Route::post('kelas',             [AdminApiController::class, 'kelasStore']);
            Route::put('kelas/{id}',         [AdminApiController::class, 'kelasUpdate']);
            Route::delete('kelas/{id}',      [AdminApiController::class, 'kelasDestroy']);

            // Jurusan Suite
            Route::get('jurusan',            [AdminApiController::class, 'jurusanList']);
            Route::post('jurusan',           [AdminApiController::class, 'jurusanStore']);

            // WA Sender & Logs
            Route::get('wa-sender',          [AdminApiController::class, 'waSenderList']);
            Route::post('wa-sender',         [AdminApiController::class, 'waSenderStore']);
            Route::put('wa-sender/{id}',     [AdminApiController::class, 'waSenderUpdate']);
            Route::delete('wa-sender/{id}',  [AdminApiController::class, 'waSenderDestroy']);
            Route::get('wa-logs',            [AdminApiController::class, 'waLogsList']);

            // Users Management
            Route::get('users',              [AdminApiController::class, 'userList']);
            Route::post('users',             [AdminApiController::class, 'userStore']);
            Route::put('users/{id}',         [AdminApiController::class, 'userUpdate']);
            Route::delete('users/{id}',      [AdminApiController::class, 'userDestroy']);

            // Master Mapel
            Route::get('mapel',              [AdminApiController::class, 'mapelList']);
            Route::post('mapel',             [AdminApiController::class, 'mapelStore']);
            Route::put('mapel/{id}',         [AdminApiController::class, 'mapelUpdate']);
            Route::delete('mapel/{id}',      [AdminApiController::class, 'mapelDestroy']);

            // Master Jadwal
            Route::get('jadwal',             [AdminApiController::class, 'jadwalList']);
            Route::post('jadwal',            [AdminApiController::class, 'jadwalStore']);
            Route::put('jadwal/{id}',        [AdminApiController::class, 'jadwalUpdate']);
            Route::delete('jadwal/{id}',     [AdminApiController::class, 'jadwalDestroy']);

            // Pengaturan Jam Absensi Sekolah
            Route::get('pengaturan-absensi', [AdminApiController::class, 'pengaturanAbsensi']);
            Route::post('pengaturan-absensi',[AdminApiController::class, 'pengaturanAbsensiUpdate']);
        });
    });

});
