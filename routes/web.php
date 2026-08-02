<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboard;
use App\Http\Controllers\SuperAdmin\WaSenderController;
use App\Http\Controllers\SuperAdmin\TemplatePesanController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\JurusanController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Http\Controllers\Admin\PengaturanAbsensiController;
use App\Http\Controllers\Guru\AbsensiController;
use App\Http\Controllers\Guru\IzinSakitController;
use App\Http\Controllers\Guru\LaporanController;
use App\Http\Controllers\Siswa\RiwayatController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// =====================================================
// ROUTES TERPROTEKSI AUTH
// =====================================================
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard umum (redirect sesuai role)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // =====================================================
    // SUPER ADMIN ROUTES
    // =====================================================
    Route::middleware('role:super_admin')->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/dashboard', [SuperAdminDashboard::class, 'index'])->name('dashboard');

        // WA Sender Management
        Route::resource('wa-sender', WaSenderController::class);
        Route::post('wa-sender/{waSender}/test', [WaSenderController::class, 'testKirim'])->name('wa-sender.test');
        Route::post('wa-sender/{waSender}/cek-status', [WaSenderController::class, 'cekStatus'])->name('wa-sender.cek-status');

        // Template Pesan WA
        Route::get('template-pesan/{templatePesan}/preview', [\App\Http\Controllers\SuperAdmin\TemplatePesanController::class, 'preview'])->name('template-pesan.preview');
        Route::resource('template-pesan', TemplatePesanController::class);

        // Log WA
        Route::get('wa-log', [\App\Http\Controllers\SuperAdmin\WaLogController::class, 'index'])->name('wa-log.index');

        // Manajemen User
        Route::resource('user', \App\Http\Controllers\SuperAdmin\UserController::class);
        Route::post('user/{user}/reset-password', [\App\Http\Controllers\SuperAdmin\UserController::class, 'resetPassword'])->name('user.reset-password');
    });

    // =====================================================
    // ADMIN / TU ROUTES
    // =====================================================
    Route::middleware('role:admin|super_admin')->prefix('admin')->name('admin.')->group(function () {

        // Data Master
        Route::resource('tahun-ajaran', TahunAjaranController::class);
        Route::patch('tahun-ajaran/{tahunAjaran}/aktifkan', [TahunAjaranController::class, 'aktifkan'])->name('tahun-ajaran.aktifkan');

        Route::resource('jurusan', JurusanController::class);

        Route::resource('kelas', KelasController::class)->parameters(['kelas' => 'kelas']);
        Route::get('kelas/{kelas}/siswa', [KelasController::class, 'daftarSiswa'])->name('kelas.siswa');

        Route::resource('guru', GuruController::class);

        // ─── Siswa: route khusus HARUS di atas resource ───────────────────────
        Route::get('siswa/template-import', [SiswaController::class, 'templateImport'])->name('siswa.template-import');
        Route::post('siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
        Route::get('siswa/cetak-batch/{kelas_id}', [SiswaController::class, 'cetakBatch'])->name('siswa.cetak-batch');
        // ─── Resource (harus setelah route khusus) ────────────────────────────
        Route::resource('siswa', SiswaController::class);
        Route::post('siswa/{siswa}/regenerate-qr', [SiswaController::class, 'regenerateQr'])->name('siswa.regenerate-qr');
        Route::get('siswa/{siswa}/kartu-qr', [SiswaController::class, 'cetakKartu'])->name('siswa.kartu-qr');

        // Pengaturan Absensi per Kelas
        Route::resource('pengaturan-absensi', PengaturanAbsensiController::class);
    });

    // =====================================================
    // GURU / WALI KELAS ROUTES
    // =====================================================
    Route::middleware('role:guru|admin|super_admin')->prefix('guru')->name('guru.')->group(function () {

        // Absensi & Scan QR
        Route::get('absensi', [AbsensiController::class, 'index'])->name('absensi.index');
        Route::get('absensi/scan/{kelas_id}', [AbsensiController::class, 'scan'])->name('absensi.scan');
        Route::post('absensi/proses-scan', [AbsensiController::class, 'prosesScan'])->name('absensi.proses-scan');
        Route::get('absensi/rekap/{kelas_id}', [AbsensiController::class, 'rekap'])->name('absensi.rekap');
        Route::post('absensi/manual', [AbsensiController::class, 'inputManual'])->name('absensi.manual');
        Route::patch('absensi/{absensi}', [AbsensiController::class, 'update'])->name('absensi.update');
        Route::delete('absensi/{absensi}', [AbsensiController::class, 'destroy'])->name('absensi.destroy');

        // Siswa belum absen (real-time AJAX)
        Route::get('absensi/belum-scan/{kelas_id}', [AbsensiController::class, 'belumScan'])->name('absensi.belum-scan');

        // Izin & Sakit
        Route::get('izin', [IzinSakitController::class, 'index'])->name('izin.index');
        Route::get('izin/{izinSakit}', [IzinSakitController::class, 'show'])->name('izin.show');
        Route::patch('izin/{izinSakit}/setujui', [IzinSakitController::class, 'setujui'])->name('izin.setujui');
        Route::patch('izin/{izinSakit}/tolak', [IzinSakitController::class, 'tolak'])->name('izin.tolak');

        // Laporan
        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.export-excel');
        Route::get('laporan/export-pdf',   [LaporanController::class, 'exportPdf'])->name('laporan.export-pdf');
        Route::get('laporan/pdf',          [LaporanController::class, 'exportPdf'])->name('laporan.pdf');

        // Penilaian & Nilai Harian Siswa
        Route::resource('penilaian', \App\Http\Controllers\Guru\PenilaianController::class);
        Route::post('penilaian/{penilaian}/nilai-batch', [\App\Http\Controllers\Guru\PenilaianController::class, 'storeNilaiBatch'])->name('penilaian.store-nilai-batch');
    });

    // =====================================================
    // SISWA ROUTES
    // =====================================================
    Route::middleware('role:siswa|guru|admin|super_admin')->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');
        Route::get('riwayat/{absensi}', [RiwayatController::class, 'show'])->name('riwayat.show');
        Route::get('pengajuan-izin', [RiwayatController::class, 'izinIndex'])->name('izin.index');
        Route::get('pengajuan-izin/create', [RiwayatController::class, 'izinCreate'])->name('izin.create');
        Route::post('pengajuan-izin', [RiwayatController::class, 'izinStore'])->name('izin.store');
    });
});

require __DIR__ . '/auth.php';
