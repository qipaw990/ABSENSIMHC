<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        $tahunAktif = TahunAjaran::where('is_aktif', true)->first();
        if (!$tahunAktif) {
            $this->command->warn('⚠️  Tahun Ajaran aktif tidak ditemukan. Jalankan TahunAjaranSeeder dulu.');
            return;
        }

        $rpl = Jurusan::where('kode', 'RPL')->first();
        if (!$rpl) {
            $this->command->warn('⚠️  Jurusan RPL tidak ditemukan. Jalankan JurusanSeeder dulu.');
            return;
        }

        Kelas::firstOrCreate(
            [
                'nama'            => 'XII RPL 1',
                'tahun_ajaran_id' => $tahunAktif->id,
            ],
            [
                'tingkat'       => 12,
                'jurusan_id'    => $rpl->id,
                'wali_kelas_id' => null,
            ]
        );

        $this->command->info("✅ Kelas seeded: XII RPL 1 (Tahun Ajaran: {$tahunAktif->nama} {$tahunAktif->semester})");
    }
}
