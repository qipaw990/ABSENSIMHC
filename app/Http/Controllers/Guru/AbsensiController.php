<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Services\AbsensiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    public function __construct(private AbsensiService $absensiService) {}

    /**
     * Halaman pilih kelas untuk absensi.
     */
    public function index()
    {
        $user = Auth::user();
        $guru = $user->guru;

        // Jika guru adalah wali kelas, langsung tampilkan kelasnya
        // Jika admin/super admin, tampilkan semua kelas
        if ($user->hasRole('guru') && $guru) {
            $kelasList = $guru->kelasWali()->with('jurusan')->get();
        } else {
            $kelasList = Kelas::with('jurusan')->get();
        }

        return view('guru.absensi.index', compact('kelasList'));
    }

    /**
     * Halaman scan QR untuk kelas tertentu.
     */
    public function scan(int $kelas_id)
    {
        $kelas    = Kelas::with(['jurusan', 'pengaturanAbsensi', 'waSender'])->findOrFail($kelas_id);
        $pengaturan = $kelas->pengaturan;

        // Statistik hari ini
        $stats = $this->absensiService->rekapHariIni($kelas_id);

        // Daftar siswa yang belum absen
        $belumAbsen = Siswa::where('kelas_id', $kelas_id)
            ->whereDoesntHave('absensi', fn($q) => $q->whereDate('tanggal', today()))
            ->orderBy('nama')
            ->get();

        return view('guru.absensi.scan', compact('kelas', 'pengaturan', 'stats', 'belumAbsen'));
    }

    /**
     * API Endpoint: Proses scan QR dari kamera (AJAX/JSON).
     */
    public function prosesScan(Request $request): JsonResponse
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
            \Illuminate\Support\Facades\Log::error('prosesScan - exception', [
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
                'qr_token' => substr($validated['qr_token'], 0, 12) . '...',
                'kelas_id' => $validated['kelas_id'],
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
                'siswa'   => null,
                'absensi' => null,
            ], 500);
        }

        // Sertakan data siswa untuk tampilan konfirmasi visual
        $siswaData = null;
        if ($hasil['siswa']) {
            $siswa = $hasil['siswa'];
            $siswaData = [
                'nama'     => $siswa->nama,
                'nis'      => $siswa->nis,
                'kelas'    => $siswa->kelas->nama ?? '-',
                'foto_url' => $siswa->foto_url,
            ];
        }

        $absensiData = null;
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
     * API Endpoint: Daftar siswa yang belum scan (untuk refresh real-time).
     */
    public function belumScan(int $kelas_id): JsonResponse
    {
        $belumScan = Siswa::where('kelas_id', $kelas_id)
            ->whereDoesntHave('absensi', fn($q) => $q->whereDate('tanggal', today()))
            ->orderBy('nama')
            ->get(['id', 'nama', 'nis', 'foto']);

        return response()->json([
            'count' => $belumScan->count(),
            'siswa' => $belumScan->map(fn($s) => [
                'nama'    => $s->nama,
                'nis'     => $s->nis,
                'foto_url' => $s->foto_url,
            ]),
        ]);
    }

    /**
     * Rekap absensi kelas (daftar hadir hari ini).
     */
    public function rekap(Request $request, int $kelas_id)
    {
        $kelas    = Kelas::with('jurusan')->findOrFail($kelas_id);
        $tanggal  = $request->tanggal ? \Carbon\Carbon::parse($request->tanggal) : today();

        $absensiList = Absensi::with('siswa')
            ->where('kelas_id', $kelas_id)
            ->whereDate('tanggal', $tanggal)
            ->orderBy('jam_scan')
            ->get();

        // Siswa yang belum punya record absensi
        $belumAbsen = Siswa::where('kelas_id', $kelas_id)
            ->whereDoesntHave('absensi', fn($q) => $q->whereDate('tanggal', $tanggal))
            ->get();

        $stats = $this->absensiService->rekapHariIni($kelas_id);

        return view('guru.absensi.rekap', compact('kelas', 'absensiList', 'belumAbsen', 'stats', 'tanggal'));
    }

    /**
     * Input absensi manual oleh guru (untuk izin/sakit/koreksi).
     */
    public function inputManual(Request $request)
    {
        $validated = $request->validate([
            'siswa_id'   => 'required|exists:siswa,id',
            'kelas_id'   => 'required|exists:kelas,id',
            'tanggal'    => 'required|date',
            'status'     => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'keterangan' => 'nullable|string|max:500',
            'lampiran'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('lampiran')) {
            $validated['lampiran'] = $request->file('lampiran')->store('absensi/lampiran', 'public');
        }

        $validated['dicatat_oleh'] = Auth::id();
        $validated['jam_scan']     = null;

        Absensi::updateOrCreate(
            ['siswa_id' => $validated['siswa_id'], 'tanggal' => $validated['tanggal']],
            $validated
        );

        return back()->with('success', 'Absensi manual berhasil dicatat.');
    }

    /**
     * Update status absensi yang sudah ada.
     */
    public function update(Request $request, Absensi $absensi)
    {
        $validated = $request->validate([
            'status'     => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $absensi->update($validated);

        return back()->with('success', 'Status absensi berhasil diperbarui.');
    }
}
