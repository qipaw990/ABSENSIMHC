<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruApiController extends Controller
{
    public function __construct(private AbsensiService $absensiService) {}

    /**
     * Daftar kelas yang diampu / menjadi wali kelas guru.
     * GET /api/guru/kelas
     */
    public function kelasList(Request $request): JsonResponse
    {
        $user = $request->user();
        $guru = $user->guru;

        if ($guru) {
            // Guru hanya dapat kelas yang menjadi wali kelasnya
            $kelasList = $guru->kelasWali()->with('jurusan')->get();
        } else {
            // Admin/Super Admin → semua kelas
            $kelasList = Kelas::with('jurusan')->get();
        }

        $data = $kelasList->map(fn($kelas) => [
            'id'      => $kelas->id,
            'nama'    => $kelas->nama,
            'jurusan' => $kelas->jurusan->nama ?? '-',
            'total_siswa' => $kelas->siswa()->count(),
        ]);

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Statistik absensi hari ini untuk kelas tertentu.
     * GET /api/guru/kelas/{id}/stats
     */
    public function kelasStats(int $kelasId): JsonResponse
    {
        $kelas  = Kelas::with('jurusan')->findOrFail($kelasId);
        $stats  = $this->absensiService->rekapHariIni($kelasId);

        return response()->json([
            'success' => true,
            'kelas'   => [
                'id'      => $kelas->id,
                'nama'    => $kelas->nama,
                'jurusan' => $kelas->jurusan->nama ?? '-',
            ],
            'stats' => $stats,
            'tanggal' => now()->translatedFormat('l, d F Y'),
        ]);
    }

    /**
     * Daftar siswa yang belum scan hari ini.
     * GET /api/guru/kelas/{id}/belum-scan
     */
    public function belumScan(int $kelasId): JsonResponse
    {
        $belumScan = Siswa::where('kelas_id', $kelasId)
            ->whereDoesntHave('absensi', fn($q) => $q->whereDate('tanggal', today()))
            ->orderBy('nama')
            ->get(['id', 'nama', 'nis', 'foto']);

        return response()->json([
            'success' => true,
            'count'   => $belumScan->count(),
            'siswa'   => $belumScan->map(fn($s) => [
                'id'       => $s->id,
                'nama'     => $s->nama,
                'nis'      => $s->nis,
                'foto_url' => $s->foto_url,
            ]),
        ]);
    }

    /**
     * Proses scan QR code siswa dari kamera HP guru.
     * POST /api/guru/absensi/scan
     */
    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_token' => 'required|string',
            'kelas_id' => 'required|integer|exists:kelas,id',
        ]);

        try {
            $hasil = $this->absensiService->prosesQrScan(
                $validated['qr_token'],
                $validated['kelas_id']
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }

        $siswaData   = null;
        $absensiData = null;

        if ($hasil['siswa']) {
            $siswa = $hasil['siswa'];
            $siswaData = [
                'nama'     => $siswa->nama,
                'nis'      => $siswa->nis,
                'kelas'    => $siswa->kelas->nama ?? '-',
                'foto_url' => $siswa->foto_url,
            ];
        }

        if ($hasil['data']) {
            $absensi = $hasil['data'];
            $absensiData = [
                'status'       => $absensi->status,
                'status_label' => $absensi->status_label,
                'status_color' => $absensi->status_color,
                'jam_scan'     => $absensi->jam_scan,
            ];
        }

        return response()->json([
            'success' => $hasil['success'],
            'message' => $hasil['message'],
            'siswa'   => $siswaData,
            'absensi' => $absensiData,
        ], $hasil['success'] ? 200 : 422);
    }

    /**
     * Rekap absensi kelas berdasarkan tanggal.
     * GET /api/guru/absensi/rekap/{kelas_id}?tanggal=2025-08-01
     */
    public function rekap(Request $request, int $kelasId): JsonResponse
    {
        $kelas   = Kelas::with('jurusan')->findOrFail($kelasId);
        $tanggal = $request->tanggal ? Carbon::parse($request->tanggal) : today();

        $absensiList = Absensi::with('siswa')
            ->where('kelas_id', $kelasId)
            ->whereDate('tanggal', $tanggal)
            ->orderBy('jam_scan')
            ->get();

        $belumAbsen = Siswa::where('kelas_id', $kelasId)
            ->whereDoesntHave('absensi', fn($q) => $q->whereDate('tanggal', $tanggal))
            ->get(['id', 'nama', 'nis']);

        return response()->json([
            'success' => true,
            'kelas'   => [
                'id'   => $kelas->id,
                'nama' => $kelas->nama,
            ],
            'tanggal'     => $tanggal->format('Y-m-d'),
            'absensi'     => $absensiList->map(fn($a) => [
                'id'           => $a->id,
                'nama'         => $a->siswa->nama ?? '-',
                'nis'          => $a->siswa->nis ?? '-',
                'foto_url'     => $a->siswa->foto_url ?? null,
                'status'       => $a->status,
                'status_label' => $a->status_label,
                'status_color' => $a->status_color,
                'jam_scan'     => $a->jam_scan ?? '-',
            ]),
            'belum_absen' => $belumAbsen->map(fn($s) => [
                'id'   => $s->id,
                'nama' => $s->nama,
                'nis'  => $s->nis,
            ]),
            'stats' => $this->absensiService->rekapHariIni($kelasId),
        ]);
    }
}
