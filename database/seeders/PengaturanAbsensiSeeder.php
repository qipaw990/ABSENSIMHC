<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\PengaturanAbsensi;
use Illuminate\Database\Seeder;

class PengaturanAbsensiSeeder extends Seeder
{
    public function run(): void
    {
        // Pengaturan Global (kelas_id = null) — fallback untuk semua kelas
        PengaturanAbsensi::firstOrCreate(
            ['kelas_id' => null],
            [
                'jam_masuk_batas'   => '07:15:00',
                'jam_absensi_tutup' => '08:00:00',
                'aktif_sabtu'       => false,
            ]
        );

        // Pengaturan khusus untuk kelas XII RPL 1
        $kelas = Kelas::where('nama', 'XII RPL 1')->first();
        if ($kelas) {
            PengaturanAbsensi::firstOrCreate(
                ['kelas_id' => $kelas->id],
                [
                    'jam_masuk_batas'   => '07:15:00',
                    'jam_absensi_tutup' => '08:00:00',
                    'aktif_sabtu'       => false,
                ]
            );
            $this->command->info("✅ Pengaturan Absensi seeded: 1 global + 1 untuk kelas XII RPL 1");
        } else {
            $this->command->info("✅ Pengaturan Absensi seeded: 1 global");
        }
    }
}
