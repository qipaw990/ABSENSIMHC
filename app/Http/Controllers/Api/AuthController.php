<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login → Kembalikan Bearer Token untuk mobile app.
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $user = Auth::user();

        // Hapus token lama supaya tidak menumpuk
        $user->tokens()->delete();

        // Buat token baru dengan nama device
        $deviceName = $request->input('device_name', 'mobile-app');
        $token = $user->createToken($deviceName)->plainTextToken;

        // Load relasi sesuai role
        $profileData = $this->buildProfile($user);

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => $profileData,
        ]);
    }

    /**
     * Logout → Revoke token saat ini.
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout.',
        ]);
    }

    /**
     * Profil user yang sedang login.
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'user'    => $this->buildProfile($user),
        ]);
    }

    /**
     * Build profil user lengkap beserta relasi & role.
     */
    private function buildProfile($user): array
    {
        $roles = $user->getRoleNames()->toArray();
        $role  = $roles[0] ?? 'unknown';

        $data = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $role,
            'roles' => $roles,
        ];

        if ($role === 'guru' || $role === 'admin') {
            $guru = $user->guru;
            if ($guru) {
                $data['guru'] = [
                    'id'   => $guru->id,
                    'nip'  => $guru->nip ?? null,
                    'nama' => $guru->nama,
                    'foto' => $guru->foto_url ?? null,
                ];
            }
        }

        if ($role === 'siswa') {
            $siswa = $user->siswa()->with('kelas.jurusan')->first();
            if ($siswa) {
                $data['siswa'] = [
                    'id'        => $siswa->id,
                    'nis'       => $siswa->nis,
                    'nisn'      => $siswa->nisn,
                    'nama'      => $siswa->nama,
                    'foto'      => $siswa->foto_url,
                    'qr_token'  => $siswa->qr_token,
                    'kelas'     => $siswa->kelas ? [
                        'id'      => $siswa->kelas->id,
                        'nama'    => $siswa->kelas->nama,
                        'jurusan' => $siswa->kelas->jurusan->nama ?? null,
                    ] : null,
                ];
            }
        }

        return $data;
    }

    /**
     * Ubah password user dari aplikasi Android.
     * POST /api/auth/change-password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password saat ini tidak cocok.',
            ], 422);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui.',
        ]);
    }

    /**
     * Simpan / update FCM device token untuk push notification Android.
     * POST /api/auth/fcm-token
     */
    public function updateDeviceToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();
        $user->update([
            'fcm_token' => $validated['fcm_token'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device token FCM berhasil diperbarui.',
        ]);
    }
}
