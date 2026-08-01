<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminApiController extends Controller
{
    /**
     * Dashboard statistik seluruh sekolah hari ini.
     * GET /api/admin/dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        $totalSiswa  = Siswa::count();
        $totalKelas  = Kelas::count();

        // Statistik global hari ini
        $statsHariIni = Absensi::whereDate('tanggal', today())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $sudahAbsen = array_sum($statsHariIni);
        $belumAbsen = $totalSiswa - $sudahAbsen;

        // Per kelas breakdown
        $kelasList = Kelas::with(['jurusan'])->get();
        $perKelas  = $kelasList->map(function ($kelas) {
            $totalSiswaKelas = $kelas->siswa()->count();
            $stats = Absensi::where('kelas_id', $kelas->id)
                ->whereDate('tanggal', today())
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            return [
                'id'        => $kelas->id,
                'nama'      => $kelas->nama,
                'jurusan'   => $kelas->jurusan->nama ?? '-',
                'total'     => $totalSiswaKelas,
                'hadir'     => ($stats['hadir'] ?? 0) + ($stats['terlambat'] ?? 0),
                'alpha'     => $stats['alpha']     ?? 0,
                'izin'      => ($stats['izin'] ?? 0) + ($stats['sakit'] ?? 0),
                'belum'     => $totalSiswaKelas - array_sum($stats),
            ];
        });

        // Rekap 7 hari terakhir untuk chart
        $chart7Hari = collect(range(6, 0))->map(function ($i) {
            $tgl   = now()->subDays($i);
            $stats = Absensi::whereDate('tanggal', $tgl)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            return [
                'tanggal'   => $tgl->format('Y-m-d'),
                'label'     => $tgl->translatedFormat('D'),
                'hadir'     => ($stats['hadir'] ?? 0) + ($stats['terlambat'] ?? 0),
                'alpha'     => $stats['alpha'] ?? 0,
            ];
        });

        return response()->json([
            'success'     => true,
            'tanggal'     => now()->translatedFormat('l, d F Y'),
            'ringkasan'   => [
                'total_siswa'  => $totalSiswa,
                'total_kelas'  => $totalKelas,
                'hadir'        => ($statsHariIni['hadir'] ?? 0) + ($statsHariIni['terlambat'] ?? 0),
                'terlambat'    => $statsHariIni['terlambat'] ?? 0,
                'izin'         => $statsHariIni['izin']       ?? 0,
                'sakit'        => $statsHariIni['sakit']      ?? 0,
                'alpha'        => $statsHariIni['alpha']      ?? 0,
                'belum_absen'  => max(0, $belumAbsen),
            ],
            'per_kelas'   => $perKelas,
            'chart_7_hari' => $chart7Hari,
        ]);
    }
}
