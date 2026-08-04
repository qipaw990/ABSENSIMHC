<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiSiswa;
use App\Models\Siswa;
use App\Models\TugasMateri;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoPenilaianSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dapatkan atau buat Kelas XII RPL 1
        $kelas = Kelas::firstOrCreate(
            ['nama' => 'XII RPL 1'],
            ['jurusan_id' => 1]
        );

        // 2. Dapatkan atau buat Akun Guru Demo
        $userGuru = User::firstOrCreate(
            ['email' => 'guru@sekolah.com'],
            [
                'name'              => 'Budi Santoso, S.Pd.',
                'password'          => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        if (!$userGuru->hasRole('guru')) {
            $userGuru->assignRole('guru');
        }

        $guru = Guru::firstOrCreate(
            ['user_id' => $userGuru->id],
            [
                'nip'   => '198501012010011001',
                'nama'  => 'Budi Santoso, S.Pd.',
                'no_wa' => '081234567890',
            ]
        );

        // 3. Dapatkan atau buat Akun Siswa Demo
        $userSiswa = User::firstOrCreate(
            ['email' => 'siswa@sekolah.com'],
            [
                'name'              => 'Ahmad Rizky',
                'password'          => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        if (!$userSiswa->hasRole('siswa')) {
            $userSiswa->assignRole('siswa');
        }

        $siswaPrimary = Siswa::firstOrCreate(
            ['user_id' => $userSiswa->id],
            [
                'kelas_id'     => $kelas->id,
                'nis'          => '20241001',
                'nisn'         => '0051234567',
                'nama'         => 'Ahmad Rizky',
                'no_wa_ortu'   => '081987654321',
                'nama_ortu'    => 'Bambang Rizky',
                'qr_token'     => Siswa::generateQrToken(),
                'qr_is_active' => true,
            ]
        );

        // Buat 2 siswa tambahan di kelas agar data batch kelas terasa ramai
        $siswa2 = Siswa::firstOrCreate(
            ['nis' => '20241002'],
            [
                'kelas_id'     => $kelas->id,
                'nisn'         => '0051234568',
                'nama'         => 'Siti Nurhaliza',
                'no_wa_ortu'   => '081987654322',
                'nama_ortu'    => 'Hendra',
                'qr_token'     => Siswa::generateQrToken(),
                'qr_is_active' => true,
            ]
        );

        // 4. Buat Data Mata Pelajaran Demo
        $mapelWeb = MataPelajaran::firstOrCreate(
            ['kode' => 'PPLG-WEB'],
            ['nama' => 'Pemrograman Web & Perangkat Bergerak', 'kelompok' => 'produktif']
        );

        $mapelDb = MataPelajaran::firstOrCreate(
            ['kode' => 'PPLG-BD'],
            ['nama' => 'Basis Data & Query SQL', 'kelompok' => 'produktif']
        );

        $mapelMtk = MataPelajaran::firstOrCreate(
            ['kode' => 'MP-MTK'],
            ['nama' => 'Matematika Terapan', 'kelompok' => 'adaptif']
        );

        // 5. Buat Data Tugas / Materi
        $tugas1 = TugasMateri::firstOrCreate(
            ['judul_tugas' => 'Tugas 1 - Layout Landing Page CSS'],
            [
                'guru_id'           => $guru->id,
                'kelas_id'          => $kelas->id,
                'mata_pelajaran_id' => $mapelWeb->id,
                'mata_pelajaran'    => $mapelWeb->nama,
                'bab_materi'        => 'Bab 1 - HTML & CSS Modern',
                'jenis'             => 'tugas',
                'tanggal'           => now()->subDays(5)->toDateString(),
                'keterangan'        => 'Buat layout landing page responsif menggunakan Flexbox dan Grid CSS.',
            ]
        );

        $tugas2 = TugasMateri::firstOrCreate(
            ['judul_tugas' => 'Ulangan Harian 1 - JavaScript DOM'],
            [
                'guru_id'           => $guru->id,
                'kelas_id'          => $kelas->id,
                'mata_pelajaran_id' => $mapelWeb->id,
                'mata_pelajaran'    => $mapelWeb->nama,
                'bab_materi'        => 'Bab 2 - Event Listener DOM',
                'jenis'             => 'uh',
                'tanggal'           => now()->subDays(3)->toDateString(),
                'keterangan'        => 'Ujian harian pemahaman manipulasi DOM JavaScript.',
            ]
        );

        $tugas3 = TugasMateri::firstOrCreate(
            ['judul_tugas' => 'Tugas Query JOIN Database MySQL'],
            [
                'guru_id'           => $guru->id,
                'kelas_id'          => $kelas->id,
                'mata_pelajaran_id' => $mapelDb->id,
                'mata_pelajaran'    => $mapelDb->nama,
                'bab_materi'        => 'Bab 3 - Relasi & Inner Join',
                'jenis'             => 'tugas',
                'tanggal'           => now()->subDays(1)->toDateString(),
                'keterangan'        => 'Buat query SELECT JOIN 3 tabel transaksi toko online.',
            ]
        );

        // 6. Buat Nilai Siswa Demo
        NilaiSiswa::updateOrCreate(
            ['tugas_materi_id' => $tugas1->id, 'siswa_id' => $siswaPrimary->id],
            ['nilai' => 88.5, 'catatan_guru' => 'Sangat rapi, struktur HTML valid dan responsif']
        );
        NilaiSiswa::updateOrCreate(
            ['tugas_materi_id' => $tugas1->id, 'siswa_id' => $siswa2->id],
            ['nilai' => 92.0, 'catatan_guru' => 'Luar biasa, animasi CSS sangat menarik']
        );

        NilaiSiswa::updateOrCreate(
            ['tugas_materi_id' => $tugas2->id, 'siswa_id' => $siswaPrimary->id],
            ['nilai' => 95.0, 'catatan_guru' => 'Sempurna, fungsi event listener bekerja lancar']
        );
        NilaiSiswa::updateOrCreate(
            ['tugas_materi_id' => $tugas2->id, 'siswa_id' => $siswa2->id],
            ['nilai' => 85.0, 'catatan_guru' => 'Bagus, perlu perhatikan kode penamaan variabel']
        );

        NilaiSiswa::updateOrCreate(
            ['tugas_materi_id' => $tugas3->id, 'siswa_id' => $siswaPrimary->id],
            ['nilai' => 70.0, 'catatan_guru' => 'Perlu latihan lagi untuk klausa LEFT JOIN']
        );
        NilaiSiswa::updateOrCreate(
            ['tugas_materi_id' => $tugas3->id, 'siswa_id' => $siswa2->id],
            ['nilai' => 78.0, 'catatan_guru' => 'Tuntas, query sudah berjalan sesuai']
        );
    }
}
