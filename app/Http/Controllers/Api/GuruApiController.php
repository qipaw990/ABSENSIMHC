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

        if ($user->hasRole('guru') && $guru) {
            $kelasList = $guru->getKelasAkses();
        } else {
            $kelasList = Kelas::with('jurusan')->orderBy('nama')->get();
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
                'kelas_id'     => $validated['kelas_id'],
                'status'       => $validated['status'],
                'jam_scan'     => now()->format('H:i:s'),
                'keterangan'   => $validated['keterangan'] ?? null,
                'dicatat_oleh' => $request->user()?->id,
            ]
        );

        // Kirim notifikasi WA langsung
        try {
            if ($siswa->no_wa_ortu) {
                $this->absensiService->kirimNotifikasiWALangsung($absensi, $siswa);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('GuruApiController::inputManual WA error: ' . $e->getMessage());
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

        $query = \App\Models\TugasMateri::with(['kelas', 'guru', 'mataPelajaran', 'nilaiSiswa']);

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
                'id'                  => $t->id,
                'kelas'               => $t->kelas->nama ?? '-',
                'kelas_id'            => $t->kelas_id,
                'guru_id'             => $t->guru_id,
                'guru_nama'           => $t->guru->nama ?? '-',
                'mata_pelajaran'      => $t->mata_pelajaran,
                'mata_pelajaran_id'   => $t->mata_pelajaran_id,
                'kode_mapel'          => $t->mataPelajaran->kode ?? '-',
                'jadwal_pelajaran_id' => $t->jadwal_pelajaran_id,
                'bab_materi'          => $t->bab_materi,
                'judul_tugas'         => $t->judul_tugas,
                'jenis_label'         => $t->jenis_label,
                'tanggal'             => $t->tanggal?->format('Y-m-d') ?? '-',
                'total_siswa'         => $t->nilaiSiswa->count(),
                'sudah_dinilai'       => $t->nilaiSiswa->where('nilai', '>', 0)->count(),
            ]),
        ]);
    }

    /**
     * Detail Nilai Siswa per Tugas ID.
     * GET /api/guru/penilaian/{id}
     */
    public function penilaianDetail(int $id): JsonResponse
    {
        $penilaian = \App\Models\TugasMateri::with(['kelas', 'guru', 'mataPelajaran'])->findOrFail($id);

        // Pastikan seluruh siswa di kelas tersebut punya record nilai
        $siswaList = Siswa::where('kelas_id', $penilaian->kelas_id)->orderBy('nama')->get();
        foreach ($siswaList as $s) {
            \App\Models\NilaiSiswa::firstOrCreate([
                'tugas_materi_id' => $penilaian->id,
                'siswa_id'        => $s->id,
            ], [
                'nilai' => 0,
            ]);
        }

        $nilaiList = \App\Models\NilaiSiswa::with('siswa')
            ->where('tugas_materi_id', $penilaian->id)
            ->get()
            ->sortBy('siswa.nama');

        $totalSiswa = $nilaiList->count();
        $sudahDinilai = $nilaiList->filter(fn($n) => (float)$n->nilai > 0)->count();
        $tuntasCount  = $nilaiList->filter(fn($n) => (float)$n->nilai >= 75)->count();
        $remidiCount  = $nilaiList->filter(fn($n) => (float)$n->nilai > 0 && (float)$n->nilai < 75)->count();
        $belumCount   = $nilaiList->filter(fn($n) => (float)$n->nilai == 0)->count();
        $rataRata     = $nilaiList->avg('nilai') ?? 0;

        $mapelNama = !empty($penilaian->mata_pelajaran)
            ? $penilaian->mata_pelajaran
            : ($penilaian->mataPelajaran->nama ?? '-');

        return response()->json([
            'success'   => true,
            'penilaian' => [
                'id'                  => $penilaian->id,
                'kelas_id'            => $penilaian->kelas_id,
                'kelas'               => $penilaian->kelas->nama ?? '-',
                'guru_id'             => $penilaian->guru_id,
                'guru_nama'           => $penilaian->guru->nama ?? '-',
                'mata_pelajaran'      => $mapelNama,
                'mata_pelajaran_id'   => $penilaian->mata_pelajaran_id,
                'kode_mapel'          => $penilaian->mataPelajaran->kode ?? '-',
                'jadwal_pelajaran_id' => $penilaian->jadwal_pelajaran_id,
                'bab_materi'          => $penilaian->bab_materi,
                'judul_tugas'         => $penilaian->judul_tugas,
                'jenis'               => $penilaian->jenis,
                'jenis_label'         => $penilaian->jenis_label,
                'tanggal'             => $penilaian->tanggal?->format('Y-m-d') ?? '-',
                'tanggal_formatted'   => $penilaian->tanggal ? Carbon::parse($penilaian->tanggal)->translatedFormat('l, d F Y') : '-',
                'keterangan'          => $penilaian->keterangan ?? '-',
                'kkm'                 => 75,
            ],
            'ringkasan' => [
                'total_siswa'         => $totalSiswa,
                'sudah_dinilai'       => $sudahDinilai,
                'tuntas_count'        => $tuntasCount,
                'remidi_count'        => $remidiCount,
                'belum_dinilai_count' => $belumCount,
                'rata_rata'           => round($rataRata, 1),
            ],
            'nilai_siswa' => $nilaiList->values()->map(function ($n) {
                $skor       = (float) $n->nilai;
                $isTuntas   = $skor >= 75;
                $status     = $skor >= 75 ? 'Tuntas' : ($skor > 0 ? 'Remidi' : 'Belum');
                $statusColor= $skor >= 75 ? '#10b981' : ($skor > 0 ? '#f59e0b' : '#6b7280');

                $predikat = match (true) {
                    $skor >= 90 => 'A',
                    $skor >= 80 => 'B',
                    $skor >= 75 => 'C',
                    $skor > 0   => 'D',
                    default     => '-',
                };

                return [
                    'id'           => $n->id,
                    'siswa_id'     => $n->siswa_id,
                    'nama_siswa'   => $n->siswa->nama ?? '-',
                    'nis'          => $n->siswa->nis ?? '-',
                    'foto_url'     => $n->siswa->foto_url ?? null,
                    'nilai'        => $skor,
                    'nilai_formatted' => number_format($skor, 1),
                    'kkm'          => 75,
                    'is_tuntas'    => $isTuntas,
                    'predikat'     => $predikat,
                    'catatan_guru' => $n->catatan_guru ?? '',
                    'status'       => $status,
                    'status_color' => $statusColor,
                ];
            }),
        ]);
    }

    /**
     * Buat Tugas/Penilaian Baru via Mobile App.
     * POST /api/guru/penilaian
     */
    public function penilaianStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kelas_id'            => 'required|exists:kelas,id',
            'mata_pelajaran_id'   => 'nullable|exists:mata_pelajaran,id',
            'jadwal_pelajaran_id' => 'nullable|exists:jadwal_pelajaran,id',
            'mata_pelajaran'      => 'required|string|max:255',
            'bab_materi'          => 'required|string|max:255',
            'judul_tugas'         => 'required|string|max:255',
            'jenis'               => 'required|in:tugas,uh,uts,uas,praktikum',
            'tanggal'             => 'required|date',
            'keterangan'          => 'nullable|string',
        ]);

        $guru = $request->user()->guru;
        $validated['guru_id'] = $guru?->id;

        if (!empty($validated['mata_pelajaran_id'])) {
            $mapel = \App\Models\MataPelajaran::find($validated['mata_pelajaran_id']);
            if ($mapel) {
                $validated['mata_pelajaran'] = $mapel->nama;
            }
        }

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
     *
     * Menerima format Fleksibel:
     * Format A (Array List):
     * {
     *    "items": [
     *       {"siswa_id": 12, "nilai": 88.5, "catatan_guru": "Bagus"},
     *       {"nilai_id": 101, "nilai": 75.0, "catatan_guru": "Cukup"}
     *    ]
     * }
     * Format B (Keyed Map):
     * {
     *    "nilai": {"101": 88.5, "102": 75.0},
     *    "catatan_guru": {"101": "Sangat rapi"}
     * }
     */
    public function penilaianNilaiBatch(Request $request, int $id): JsonResponse
    {
        $penilaian = \App\Models\TugasMateri::findOrFail($id);

        // Opsi A: Jika mengirim key "items" berisi array of objects
        if ($request->has('items') && is_array($request->input('items'))) {
            $request->validate([
                'items'                => 'required|array',
                'items.*.nilai'        => 'required|numeric|min:0|max:100',
                'items.*.catatan_guru' => 'nullable|string|max:255',
            ]);

            foreach ($request->input('items') as $item) {
                $skor    = $item['nilai'];
                $catatan = $item['catatan_guru'] ?? null;

                if (!empty($item['nilai_id']) || !empty($item['id'])) {
                    $nilaiId = $item['nilai_id'] ?? $item['id'];
                    \App\Models\NilaiSiswa::where('id', $nilaiId)
                        ->where('tugas_materi_id', $penilaian->id)
                        ->update(['nilai' => $skor, 'catatan_guru' => $catatan]);
                } elseif (!empty($item['siswa_id'])) {
                    \App\Models\NilaiSiswa::updateOrCreate(
                        [
                            'tugas_materi_id' => $penilaian->id,
                            'siswa_id'        => $item['siswa_id'],
                        ],
                        [
                            'nilai'        => $skor,
                            'catatan_guru' => $catatan,
                        ]
                    );
                }
            }
        } else {
            // Opsi B: Format Keyed Dictionary
            $validated = $request->validate([
                'nilai'        => 'required|array',
                'catatan_guru' => 'nullable|array',
            ]);

            foreach ($validated['nilai'] as $key => $skor) {
                $catatan = $validated['catatan_guru'][$key] ?? null;

                // Coba update by nilai_siswa.id terlebih dahulu, jika gagal coba by siswa_id
                $updated = \App\Models\NilaiSiswa::where('id', $key)
                    ->where('tugas_materi_id', $penilaian->id)
                    ->update(['nilai' => $skor, 'catatan_guru' => $catatan]);

                if (!$updated) {
                    \App\Models\NilaiSiswa::updateOrCreate(
                        [
                            'tugas_materi_id' => $penilaian->id,
                            'siswa_id'        => $key,
                        ],
                        [
                            'nilai'        => $skor,
                            'catatan_guru' => $catatan,
                        ]
                    );
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Nilai seluruh siswa berhasil diperbarui.',
        ]);
    }
}
