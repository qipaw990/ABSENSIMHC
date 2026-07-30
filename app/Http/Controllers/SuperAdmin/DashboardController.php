<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\WaSender;
use App\Models\WaLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();

        // Statistik kehadiran hari ini (seluruh sekolah)
        $statsHariIni = Absensi::whereDate('tanggal', $today)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalSiswa   = Siswa::count();
        $totalKelas   = Kelas::count();

        // Siswa yang sudah absen hari ini
        $sudahAbsen = Absensi::whereDate('tanggal', $today)->count();
        $belumAbsen = $totalSiswa - $sudahAbsen;

        // Statistik per kelas untuk hari ini
        $statsPerKelas = Kelas::with(['siswa', 'waSender'])
            ->withCount(['absensi as hadir_count' => fn($q) => $q->whereDate('tanggal', $today)->where('status', 'hadir')])
            ->withCount(['absensi as terlambat_count' => fn($q) => $q->whereDate('tanggal', $today)->where('status', 'terlambat')])
            ->withCount(['absensi as alpha_count' => fn($q) => $q->whereDate('tanggal', $today)->where('status', 'alpha')])
            ->withCount(['siswa as total_siswa'])
            ->get();

        // Status WA Sender
        $waSenders = WaSender::with('kelas')->get();
        $waSenderAktif = $waSenders->where('status', 'aktif')->count();

        // WA Log hari ini
        $waLogHariIni = WaLog::whereDate('created_at', $today)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Tren 7 hari terakhir untuk chart
        $trenMingguan = [];
        for ($i = 6; $i >= 0; $i--) {
            $tgl = Carbon::today()->subDays($i);
            $trenMingguan[] = [
                'tanggal'   => $tgl->format('d/m'),
                'hadir'     => Absensi::whereDate('tanggal', $tgl)->where('status', 'hadir')->count(),
                'terlambat' => Absensi::whereDate('tanggal', $tgl)->where('status', 'terlambat')->count(),
                'alpha'     => Absensi::whereDate('tanggal', $tgl)->where('status', 'alpha')->count(),
            ];
        }

        return view('super-admin.dashboard', compact(
            'statsHariIni',
            'totalSiswa',
            'totalKelas',
            'sudahAbsen',
            'belumAbsen',
            'statsPerKelas',
            'waSenders',
            'waSenderAktif',
            'waLogHariIni',
            'trenMingguan',
        ));
    }
}
