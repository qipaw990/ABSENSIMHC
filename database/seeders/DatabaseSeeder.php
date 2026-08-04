<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Urutan penting! Jangan diubah.
     * Dependency: Role → SuperAdmin → Jurusan → TahunAjaran → Guru → Kelas → Siswa → dst.
     */
    public function run(): void
    {
        $this->command->info('🚀 Memulai proses seeding database...');
        $this->command->newLine();

        $this->call([
            // ─── STEP 1: Role & Akun Sistem ───────────────────────────────
            RoleSeeder::class,           // Roles: super_admin, admin, guru, siswa
            SuperAdminSeeder::class,     // Akun Super Admin: admin@mhc.com / @dminX12
            AdminSeeder::class,          // Kosong
            TemplatePesanSeeder::class,  // Template notifikasi WA (hadir, alpha, dll)

            // ─── STEP 2: Data Master ───────────────────────────────────────
            JurusanSeeder::class,        // 1 jurusan: RPL
            TahunAjaranSeeder::class,    // 1 tahun ajaran aktif: 2025/2026 Ganjil
            GuruSeeder::class,           // Kosong
            KelasSeeder::class,          // 1 kelas: XII RPL 1

            // ─── STEP 3: Data Siswa ────────────────────────────────────────
            SiswaSeeder::class,          // Kosong (siswa diinput manual)

            // ─── STEP 4: Pengaturan & Konfigurasi ─────────────────────────
            PengaturanAbsensiSeeder::class, // Jam masuk & tutup absensi
            WaSenderSeeder::class,          // Kosong (dikonfigurasi manual)

            // ─── STEP 5: Data Demo & Nilai ─────────────────────────────────
            AbsensiSeeder::class,           // Kosong
            DemoPenilaianSeeder::class,     // Data demo guru, siswa, mapel, tugas, dan nilai
        ]);

        $this->command->newLine();
        $this->command->info('🎉 Seeding selesai!');
        $this->command->newLine();
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Super Admin', 'admin@mhc.com', '@dminX12'],
            ]
        );
        $this->command->warn('⚠️  Ganti password default setelah login pertama!');
        $this->command->newLine();
        $this->command->info('📋 Data yang tersedia setelah seeding:');
        $this->command->info('   - Jurusan : RPL (Rekayasa Perangkat Lunak)');
        $this->command->info('   - Tahun Ajaran : 2025/2026 Ganjil (aktif)');
        $this->command->info('   - Kelas   : XII RPL 1');
        $this->command->info('   - Siswa   : (belum ada, diinput manual atau via import)');
        $this->command->info('   - WA Sender : (dikonfigurasi manual di menu WA Sender)');
    }
}
