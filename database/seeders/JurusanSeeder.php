<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use Illuminate\Database\Seeder;

class JurusanSeeder extends Seeder
{
    public function run(): void
    {
        $jurusan = Jurusan::firstOrCreate(
            ['kode' => 'RPL'],
            ['nama' => 'Rekayasa Perangkat Lunak']
        );

        $this->command->info('✅ Jurusan seeded: RPL (Rekayasa Perangkat Lunak)');
    }
}
