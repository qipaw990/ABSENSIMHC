<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@mhc.com'],
            [
                'name'     => 'Administrator MHC',
                'password' => Hash::make('@dminX12'),
            ]
        );

        if (!$admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info('✅ Super Admin dibuat:');
        $this->command->info('   Email    : admin@mhc.com');
        $this->command->info('   Password : @dminX12');
    }
}
