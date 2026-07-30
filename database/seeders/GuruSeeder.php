<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GuruSeeder extends Seeder
{
    public function run(): void
    {
        // Kosong: Tidak ada guru dummy (dapat ditambahkan manual dari menu Data Guru)
        $this->command->info('ℹ️ GuruSeeder: Kosong (tidak ada guru dummy).');
    }
}
