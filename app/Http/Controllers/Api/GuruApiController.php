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

    /**
     * Presensi manual siswa oleh guru/wali kelas (misal siswa tidak membawa QR/HP).
     * POST /api/guru/absensi/manual
     */
    public function inputManual(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'siswa_id'   => 'required|integer|exists:siswa,id',
            'kelas_id'   => 'required|integer|exists:kelas,id',
            'status'     => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'keterangan' => 'nullable|string|max:255',
            'tanggal'    => 'nullable|date',
        ]);

        $tanggal = $validated['tanggal'] ?? today()->toDateString();
        $siswa   = Siswa::findOrFail($validated['siswa_id']);

        $absensi = Absensi::updateOrCreate(
            [
                'siswa_id' => $siswa->id,
                'tanggal'  => $tanggal,
            ],
            [
                'kelas_id'   => $validated['kelas_id'],
                'status'     => $validated['status'],
                'jam_scan'   => now()->format('H:i:s'),
                'keterangan' => $validated['keterangan'] ?? null,
            ]
        );

        // Kirim notifikasi WA
        try {
            $this->absensiService->kirimNotifikasiWA($siswa, $absensi);
        } catch (\Exception $e) {
            // Log error tapi jangan gagalkan respon API
        }

        return response()->json([
            'success' => true,
            'message' => "Presensi manual {$siswa->nama} (" . strtoupper($validated['status']) . ") berhasil disimpan.",
            'absensi' => [
                'id'           => $absensi->id,
                'siswa_nama'   => $siswa->nama,
                'status'       => $absensi->status,
                'status_label' => $absensi->status_label,
                'jam_scan'     => $absensi->jam_scan,
                'tanggal'      => $absensi->tanggal,
            ],
        ]);
    }

    /**
     * Edit / Update data absensi via API Mobile.
     * PUT /api/guru/absensi/{id}
     */
    public function updateAbsensi(Request $request, int $id): JsonResponse
    {
        $absensi = Absensi::findOrFail($id);

        $validated = $request->validate([
            'status'     => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'jam_scan'   => 'nullable|string',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $absensi->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil diperbarui.',
            'data'    => $absensi,
        ]);
    }

    /**
     * Hapus data absensi via API Mobile.
     * DELETE /api/guru/absensi/{id}
     */
    public function deleteAbsensi(int $id): JsonResponse
    {
        $absensi = Absensi::findOrFail($id);
        $absensi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil dihapus.',
        ]);
    }

    /**
     * Jadwal Mengajar Guru yang Login.
     * GET /api/guru/jadwal
     */
    public function jadwal(Request $request): JsonResponse
    {
        $user = $request->user();
        $guru = $user->guru;

        $query = \App\Models\JadwalPelajaran::with(['kelas', 'mataPelajaran']);

        if ($user->hasRole('guru') && $guru) {
            $query->where('guru_id', $guru->id);
        }

        $jadwalList = $query->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
            ->orderBy('jam_mulai')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $jadwalList->map(fn($j) => [
                'id'            => $j->id,
                'hari'          => $j->hari_label,
                'jam'           => $j->jam_format,
                'kelas'         => $j->kelas->nama ?? '-',
                'mata_pelajaran'=> $j->mataPelajaran->nama ?? '-',
                'ruangan'       => $j->ruangan ?? 'Kelas Reguler',
            ]),
        ]);
    }

    /**
     * Daftar Penilaian & Tugas Harian oleh Guru.
     * GET /api/guru/penilaian
     */
    public function penilaianList(Request $request): JsonResponse
    {
        $user = $request->user();
        $guru = $user->guru;

        $query = \App\Models\TugasMateri::with(['kelas', 'nilaiSiswa']);

        if ($user->hasRole('guru') && $guru) {
            $query->where('guru_id', $guru->id);
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $tugasList = $query->orderByDesc('tanggal')->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $tugasList->through(fn($t) => [
                'id'             => $t->id,
                'kelas'          => $t->kelas->nama ?? '-',
                'mata_pelajaran' => $t->mata_pelajaran,
                'bab_materi'     => $t->bab_materi,
                'judul_tugas'    => $t->judul_tugas,
                'jenis_label'    => $t->jenis_label,
                'tanggal'        => $t->tanggal?->format('Y-m-d') ?? '-',
                'total_siswa'    => $t->nilaiSiswa->count(),
                'sudah_dinilai'  => $t->nilaiSiswa->where('nilai', '>', 0)->count(),
            ]),
        ]);
    }

    /**
     * Detail Nilai Siswa per Tugas ID.
     * GET /api/guru/penilaian/{id}
     */
    public function penilaianDetail(int $id): JsonResponse
    {
        $penilaian = \App\Models\TugasMateri::with(['kelas', 'nilaiSiswa.siswa'])->findOrFail($id);

        return response()->json([
            'success'   => true,
            'penilaian' => [
                'id'             => $penilaian->id,
                'kelas'          => $penilaian->kelas->nama ?? '-',
                'mata_pelajaran' => $penilaian->mata_pelajaran,
                'bab_materi'     => $penilaian->bab_materi,
                'judul_tugas'    => $penilaian->judul_tugas,
                'jenis_label'    => $penilaian->jenis_label,
                'tanggal'        => $penilaian->tanggal?->format('Y-m-d') ?? '-',
                'keterangan'     => $penilaian->keterangan ?? '-',
            ],
            'nilai_siswa' => $penilaian->nilaiSiswa->sortBy('siswa.nama')->values()->map(fn($n) => [
                'id'           => $n->id,
                'siswa_id'     => $n->siswa_id,
                'nama_siswa'   => $n->siswa->nama ?? '-',
                'nis'          => $n->siswa->nis ?? '-',
                'nilai'        => (float) $n->nilai,
                'catatan_guru' => $n->catatan_guru ?? '',
                'status'       => $n->nilai >= 75 ? 'Tuntas' : ($n->nilai > 0 ? 'Remidi' : 'Belum'),
            ]),
        ]);
    }

    /**
     * Buat Tugas/Penilaian Baru via Mobile App.
     * POST /api/guru/penilaian
     */
    public function penilaianStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kelas_id'       => 'required|exists:kelas,id',
            'mata_pelajaran' => 'required|string|max:255',
            'bab_materi'     => 'required|string|max:255',
            'judul_tugas'    => 'required|string|max:255',
            'jenis'          => 'required|in:tugas,uh,uts,uas,praktikum',
            'tanggal'        => 'required|date',
            'keterangan'     => 'nullable|string',
        ]);

        $guru = $request->user()->guru;
        $validated['guru_id'] = $guru?->id;

        $tugas = \App\Models\TugasMateri::create($validated);

        // Buat record nilai awal 0 untuk seluruh siswa di kelas
        $siswaList = \App\Models\Siswa::where('kelas_id', $tugas->kelas_id)->get();
        foreach ($siswaList as $s) {
            \App\Models\NilaiSiswa::firstOrCreate([
                'tugas_materi_id' => $tugas->id,
                'siswa_id'        => $s->id,
            ], ['nilai' => 0]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tugas/Penilaian berhasil dibuat.',
            'data'    => $tugas,
        ]);
    }

    /**
     * Simpan Nilai Batch Siswa via Mobile App.
     * POST /api/guru/penilaian/{id}/nilai-batch
     */
    public function penilaianNilaiBatch(Request $request, int $id): JsonResponse
    {
        $penilaian = \App\Models\TugasMateri::findOrFail($id);

        $validated = $request->validate([
            'nilai'          => 'required|array',
            'catatan_guru'   => 'nullable|array',
        ]);

        foreach ($validated['nilai'] as $nilaiId => $skor) {
            $catatan = $validated['catatan_guru'][$nilaiId] ?? null;

            \App\Models\NilaiSiswa::where('id', $nilaiId)
                ->where('tugas_materi_id', $penilaian->id)
                ->update([
                    'nilai'        => $skor,
                    'catatan_guru' => $catatan,
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Nilai seluruh siswa berhasil diperbarui.',
        ]);
    }
}
