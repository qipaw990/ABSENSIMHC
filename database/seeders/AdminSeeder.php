<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Kosong: Akun admin utama menggunakan admin@mhc.com (SuperAdminSeeder)
        $this->command->info('ℹ️ AdminSeeder: Kosong (tidak menambahkan admin dummy).');
    }
}
