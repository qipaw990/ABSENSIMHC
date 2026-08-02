<?php

namespace App\Http\Controllers\Guru;

use App\Exports\LaporanAbsensiExport;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $guru = $user->guru;

        // Tentukan kelas yang bisa dilihat
        if ($user->hasRole('guru') && $guru) {
            $kelasList = $guru->kelasWali()->with('jurusan')->get();
        } else {
            $kelasList = Kelas::with('jurusan')->orderBy('nama')->get();
        }

        // Filter
        $kelasId      = $request->kelas_id ?? $kelasList->first()?->id;
        $bulan        = (int) ($request->bulan ?? now()->format('m'));
        $tahun        = (int) ($request->tahun ?? now()->format('Y'));
        $tanggalMulai = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $tanggalAkhir = $tanggalMulai->copy()->endOfMonth();
        $jumlahHari   = $tanggalMulai->daysInMonth;

        // Rekap bulanan per siswa + matriks harian (1 s/d jumlahHari)
        $siswaList = Siswa::where('kelas_id', $kelasId)
            ->with(['absensi' => fn($q) => $q->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])])
            ->orderBy('nama')
            ->get()
            ->map(function ($siswa) use ($jumlahHari) {
                $absensiByDay = $siswa->absensi->keyBy(fn($a) => (int) Carbon::parse($a->tanggal)->format('d'));

                $daily = [];
                for ($d = 1; $d <= $jumlahHari; $d++) {
                    $daily[$d] = $absensiByDay->get($d)?->status;
                }

                $stats = $siswa->absensi->groupBy('status')->map->count();

                return [
                    'siswa'     => $siswa,
                    'daily'     => $daily,
                    'hadir'     => $stats->get('hadir', 0),
                    'terlambat' => $stats->get('terlambat', 0),
                    'izin'      => $stats->get('izin', 0),
                    'sakit'     => $stats->get('sakit', 0),
                    'alpha'     => $stats->get('alpha', 0),
                    'total'     => $siswa->absensi->count(),
                ];
            });

        $kelas = $kelasList->firstWhere('id', $kelasId);

        return view('guru.laporan.index', compact(
            'kelasList', 'kelas', 'siswaList',
            'kelasId', 'bulan', 'tahun',
            'tanggalMulai', 'tanggalAkhir', 'jumlahHari'
        ));
    }

    /**
     * Export laporan ke Excel.
     */
    public function exportExcel(Request $request)
    {
        $kelasId = $request->kelas_id;
        $bulan   = $request->bulan ?? now()->format('m');
        $tahun   = $request->tahun ?? now()->format('Y');

        $kelas = Kelas::findOrFail($kelasId);
        $tanggalMulai = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $tanggalAkhir = $tanggalMulai->copy()->endOfMonth();

        $namaSekolah  = config('app.nama_sekolah', 'SMK');
        $namaFile     = "laporan-absensi-{$kelas->nama}-{$bulan}-{$tahun}.xlsx";

        $siswaList = Siswa::where('kelas_id', $kelasId)
            ->with(['absensi' => fn($q) => $q->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])])
            ->orderBy('nama')
            ->get()
            ->map(function ($siswa) use ($jumlahHari) {
                $absensiByDay = $siswa->absensi->keyBy(fn($a) => (int) Carbon::parse($a->tanggal)->format('d'));
                $daily = [];
                for ($d = 1; $d <= $jumlahHari; $d++) {
                    $daily[$d] = $absensiByDay->get($d)?->status;
                }
                $stats = $siswa->absensi->groupBy('status')->map->count();
                return [
                    'siswa'     => $siswa,
                    'daily'     => $daily,
                    'hadir'     => $stats->get('hadir', 0),
                    'terlambat' => $stats->get('terlambat', 0),
                    'izin'      => $stats->get('izin', 0),
                    'sakit'     => $stats->get('sakit', 0),
                    'alpha'     => $stats->get('alpha', 0),
                ];
            })->toArray();

        return Excel::download(
            new LaporanAbsensiExport($kelas, $siswaList, $tanggalMulai, $jumlahHari, $namaSekolah),
            $namaFile
        );
    }

    /**
     * Export laporan ke PDF (Matriks Harian & Rekap Total).
     */
    public function exportPdf(Request $request)
    {
        $kelasId = $request->kelas_id;
        $bulan   = (int) ($request->bulan ?? now()->format('m'));
        $tahun   = (int) ($request->tahun ?? now()->format('Y'));

        $kelas        = Kelas::with('jurusan', 'waliKelas')->findOrFail($kelasId);
        $tanggalMulai = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $tanggalAkhir = $tanggalMulai->copy()->endOfMonth();
        $jumlahHari   = $tanggalMulai->daysInMonth;

        $siswaList = Siswa::where('kelas_id', $kelasId)
            ->with(['absensi' => fn($q) => $q->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])])
            ->orderBy('nama')
            ->get()
            ->map(function ($siswa) use ($jumlahHari) {
                $absensiByDay = $siswa->absensi->keyBy(fn($a) => (int) Carbon::parse($a->tanggal)->format('d'));

                $daily = [];
                for ($d = 1; $d <= $jumlahHari; $d++) {
                    $daily[$d] = $absensiByDay->get($d)?->status;
                }

                $stats = $siswa->absensi->groupBy('status')->map->count();

                return [
                    'siswa'     => $siswa,
                    'daily'     => $daily,
                    'hadir'     => $stats->get('hadir', 0),
                    'terlambat' => $stats->get('terlambat', 0),
                    'izin'      => $stats->get('izin', 0),
                    'sakit'     => $stats->get('sakit', 0),
                    'alpha'     => $stats->get('alpha', 0),
                    'total'     => $siswa->absensi->count(),
                ];
            });

        $namaSekolah = config('app.nama_sekolah', 'SMK');
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('guru.laporan.pdf', compact(
            'kelas', 'siswaList', 'tanggalMulai', 'tanggalAkhir', 'jumlahHari', 'namaSekolah'
        ));
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'dpi'             => 120,
            'isRemoteEnabled' => false,
            'isLocalEnabled'  => true,
            'chroot'          => public_path(),
        ]);

        return $pdf->stream("laporan-absensi-{$kelas->nama}-{$bulan}-{$tahun}.pdf");
    }
}
