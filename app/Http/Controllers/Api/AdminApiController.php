<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Models\Guru;
use App\Models\WaLog;
use App\Models\WaSender;

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

    /**
     * Pencarian & Monitoring data Siswa.
     * GET /api/admin/siswa?search=budi&kelas_id=1
     */
    public function siswaList(Request $request): JsonResponse
    {
        $query = Siswa::with('kelas.jurusan');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $siswaList = $query->orderBy('nama')->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $siswaList->through(fn($s) => [
                'id'       => $s->id,
                'nis'      => $s->nis,
                'nama'     => $s->nama,
                'foto_url' => $s->foto_url,
                'kelas'    => $s->kelas->nama ?? '-',
                'no_wa_ortu' => $s->no_wa_ortu,
            ]),
        ]);
    }

    /**
     * Monitoring Data Guru.
     * GET /api/admin/guru
     */
    public function guruList(Request $request): JsonResponse
    {
        $guruList = Guru::with('kelasWali')->orderBy('nama')->get();

        return response()->json([
            'success' => true,
            'data'    => $guruList->map(fn($g) => [
                'id'         => $g->id,
                'nip'        => $g->nip ?? '-',
                'nama'       => $g->nama,
                'no_hp'      => $g->no_hp ?? '-',
                'foto_url'   => $g->foto_url,
                'wali_kelas' => $g->kelasWali->pluck('nama')->implode(', ') ?: '-',
            ]),
        ]);
    }

    /**
     * Status WA Sender device.
     * GET /api/admin/wa-sender
     */
    public function waSenderList(Request $request): JsonResponse
    {
        $senders = WaSender::with('kelas')->get();

        return response()->json([
            'success' => true,
            'data'    => $senders->map(fn($w) => [
                'id'          => $w->id,
                'name'        => $w->name,
                'phone'       => $w->phone,
                'status'      => $w->status,
                'status_color'=> $w->status === 'aktif' ? '#22c55e' : '#ef4444',
                'kelas_count' => $w->kelas->count(),
            ]),
        ]);
    }

    /**
     * Monitoring Log Pengiriman WA.
     * GET /api/admin/wa-logs
     */
    public function waLogsList(Request $request): JsonResponse
    {
        $logs = WaLog::with('siswa')->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $logs->through(fn($l) => [
                'id'         => $l->id,
                'recipient'  => $l->recipient,
                'siswa_nama' => $l->siswa->nama ?? '-',
                'status'     => $l->status,
                'created_at' => $l->created_at->format('Y-m-d H:i:s'),
                'created_at_label' => $l->created_at->diffForHumans(),
            ]),
        ]);
    }
}
