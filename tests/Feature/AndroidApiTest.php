<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AndroidApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed database roles & initial data
        $this->artisan('db:seed');
    }

    #[Test]
    public function login_api_dengan_kredensial_valid()
    {
        $user = User::factory()->create([
            'email'    => 'test_guru@sekolah.com',
            'password' => bcrypt('password123'),
        ]);
        $user->assignRole('guru');

        $response = $this->postJson('/api/auth/login', [
            'email'       => 'test_guru@sekolah.com',
            'password'    => 'password123',
            'device_name' => 'Android Unit Test',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'token',
                'user' => ['id', 'name', 'email', 'role'],
            ]);
    }

    #[Test]
    public function login_api_gagal_jika_password_salah()
    {
        $user = User::factory()->create([
            'email'    => 'test_guru2@sekolah.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test_guru2@sekolah.com',
            'password' => 'password_salah',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Email atau password salah.',
            ]);
    }

    #[Test]
    public function get_profile_me_dengan_bearer_token()
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user'    => [
                    'email' => $user->email,
                    'role'  => 'super_admin',
                ],
            ]);
    }

    #[Test]
    public function admin_dashboard_api()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'tanggal',
                'ringkasan' => ['total_siswa', 'total_kelas', 'hadir', 'terlambat', 'izin', 'sakit', 'alpha'],
                'per_kelas',
            ]);
    }

    #[Test]
    public function logout_api()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Berhasil logout.',
            ]);
    }
}
