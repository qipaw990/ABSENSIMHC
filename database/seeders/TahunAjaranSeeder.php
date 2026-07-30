<?php

namespace Database\Seeders;

use App\Models\TahunAjaran;
use Illuminate\Database\Seeder;

class TahunAjaranSeeder extends Seeder
{
    public function run(): void
    {
        TahunAjaran::firstOrCreate(
            ['nama' => '2025/2026', 'semester' => 'ganjil'],
            ['is_aktif' => true]
        );

        $this->command->info('✅ Tahun Ajaran seeded (aktif: 2025/2026 Ganjil)');
    }
}
