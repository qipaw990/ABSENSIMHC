<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiswaApiController extends Controller
{
    /**
     * Profil siswa yang sedang login beserta QR token.
     * GET /api/siswa/profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user  = $request->user();
        $siswa = $user->siswa()->with('kelas.jurusan')->first();

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        // Cek apakah sudah absen hari ini
        $absensiHariIni = $siswa->absensiHariIni();

        return response()->json([
            'success' => true,
            'siswa'   => [
                'id'        => $siswa->id,
                'nis'       => $siswa->nis,
                'nisn'      => $siswa->nisn,
                'nama'      => $siswa->nama,
                'foto_url'  => $siswa->foto_url,
                'qr_token'  => $siswa->qr_token,
                'qr_is_active' => $siswa->qr_is_active,
                'kelas'     => $siswa->kelas ? [
                    'id'      => $siswa->kelas->id,
                    'nama'    => $siswa->kelas->nama,
                    'jurusan' => $siswa->kelas->jurusan->nama ?? '-',
                ] : null,
            ],
            'absensi_hari_ini' => $absensiHariIni ? [
                'status'       => $absensiHariIni->status,
                'status_label' => $absensiHariIni->status_label,
                'status_color' => $absensiHariIni->status_color,
                'jam_scan'     => $absensiHariIni->jam_scan,
                'tanggal'      => $absensiHariIni->tanggal,
            ] : null,
        ]);
    }

    /**
     * Riwayat absensi 30 hari terakhir untuk siswa yang login.
     * GET /api/siswa/absensi?bulan=2025-08
     */
    public function riwayat(Request $request): JsonResponse
    {
        $user  = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        // Default 30 hari terakhir, atau berdasarkan bulan yang dipilih
        if ($request->bulan) {
            $mulai   = Carbon::parse($request->bulan . '-01')->startOfMonth();
            $selesai = $mulai->copy()->endOfMonth();
        } else {
            $mulai   = now()->subDays(29)->startOfDay();
            $selesai = now()->endOfDay();
        }

        $absensiList = Absensi::where('siswa_id', $siswa->id)
            ->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->orderByDesc('tanggal')
            ->get();

        return response()->json([
            'success'  => true,
            'periode'  => [
                'mulai'   => $mulai->format('Y-m-d'),
                'selesai' => $selesai->format('Y-m-d'),
            ],
            'riwayat'  => $absensiList->map(fn($a) => [
                'id'           => $a->id,
                'tanggal'      => $a->tanggal,
                'tanggal_label' => Carbon::parse($a->tanggal)->translatedFormat('l, d F Y'),
                'jam_scan'     => $a->jam_scan ?? '-',
                'status'       => $a->status,
                'status_label' => $a->status_label,
                'status_color' => $a->status_color,
                'keterangan'   => $a->keterangan ?? '-',
            ]),
        ]);
    }

    /**
     * Statistik kehadiran siswa (bulan berjalan & total).
     * GET /api/siswa/absensi/stats
     */
    public function stats(Request $request): JsonResponse
    {
        $user  = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        // Statistik bulan ini
        $bulanIni = Absensi::where('siswa_id', $siswa->id)
            ->whereYear('tanggal', now()->year)
            ->whereMonth('tanggal', now()->month)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Statistik keseluruhan (tahun ajaran)
        $total = Absensi::where('siswa_id', $siswa->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $totalHariMasuk = array_sum($total);
        $pctHadir = $totalHariMasuk > 0
            ? round((($total['hadir'] ?? 0) + ($total['terlambat'] ?? 0)) / $totalHariMasuk * 100, 1)
            : 0;

        return response()->json([
            'success'        => true,
            'bulan_ini'      => [
                'hadir'     => $bulanIni['hadir']     ?? 0,
                'terlambat' => $bulanIni['terlambat'] ?? 0,
                'izin'      => $bulanIni['izin']       ?? 0,
                'sakit'     => $bulanIni['sakit']      ?? 0,
                'alpha'     => $bulanIni['alpha']      ?? 0,
            ],
            'total'          => [
                'hadir'     => $total['hadir']     ?? 0,
                'terlambat' => $total['terlambat'] ?? 0,
                'izin'      => $total['izin']       ?? 0,
                'sakit'     => $total['sakit']      ?? 0,
                'alpha'     => $total['alpha']      ?? 0,
            ],
            'pct_hadir'      => $pctHadir,
            'total_hari'     => $totalHariMasuk,
        ]);
    }

    /**
     * Refresh / Re-generate QR token siswa.
     * POST /api/siswa/qr-refresh
     */
    public function refreshQr(Request $request): JsonResponse
    {
        $user  = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        $newToken = \Illuminate\Support\Str::random(32);
        $siswa->update([
            'qr_token' => $newToken,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'QR Token berhasil diperbarui.',
            'qr_token' => $newToken,
        ]);
    }

    /**
     * Pengajuan Izin/Sakit dari HP Siswa dengan lampiran keterangan/surat.
     * POST /api/siswa/izin-sakit
     */
    public function pengajuanIzin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status'     => 'required|in:izin,sakit',
            'keterangan' => 'required|string|max:255',
            'tanggal'    => 'nullable|date',
            'bukti_foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user  = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        $tanggal = $validated['tanggal'] ?? today()->toDateString();
        $fotoPath = null;

        if ($request->hasFile('bukti_foto')) {
            $fotoPath = $request->file('bukti_foto')->store('izin_sakit', 'public');
        }

        $absensi = Absensi::updateOrCreate(
            [
                'siswa_id' => $siswa->id,
                'tanggal'  => $tanggal,
            ],
            [
                'kelas_id'     => $siswa->kelas_id,
                'status'       => $validated['status'],
                'jam_scan'     => now()->format('H:i:s'),
                'keterangan'   => $validated['keterangan'] . ($fotoPath ? " (Bukti: storage/{$fotoPath})" : ''),
                'dicatat_oleh' => $user->id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan ' . strtoupper($validated['status']) . ' berhasil dikirim.',
            'absensi' => [
                'id'           => $absensi->id,
                'status'       => $absensi->status,
                'status_label' => $absensi->status_label,
                'keterangan'   => $absensi->keterangan,
                'tanggal'      => $absensi->tanggal,
            ],
        ]);
    }

    /**
     * Jadwal Pelajaran untuk Siswa yang Login.
     * GET /api/siswa/jadwal
     */
    public function jadwal(Request $request): JsonResponse
    {
        $user  = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        $jadwalList = \App\Models\JadwalPelajaran::with(['mataPelajaran', 'guru'])
            ->where('kelas_id', $siswa->kelas_id)
            ->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
            ->orderBy('jam_mulai')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $jadwalList->map(fn($j) => [
                'id'            => $j->id,
                'hari'          => $j->hari_label,
                'jam'           => $j->jam_format,
                'mata_pelajaran'=> $j->mataPelajaran->nama ?? '-',
                'kode_mapel'    => $j->mataPelajaran->kode ?? '-',
                'guru'          => $j->guru->nama ?? '-',
                'ruangan'       => $j->ruangan ?? 'Kelas Reguler',
            ]),
        ]);
    }

    /**
     * Nilai & Evaluasi Harian Siswa yang Login.
     * GET /api/siswa/nilai?search=...&jenis=...&mapel_id=...
     */
    public function nilai(Request $request): JsonResponse
    {
        $user  = $request->user();
        $siswa = $user->siswa;

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        $query = \App\Models\NilaiSiswa::with(['tugasMateri.guru', 'tugasMateri.mataPelajaran'])
            ->where('siswa_id', $siswa->id)
            ->whereHas('tugasMateri', function ($q) use ($request) {
                if ($request->filled('search')) {
                    $search = $request->search;
                    $q->where(function ($sub) use ($search) {
                        $sub->where('mata_pelajaran', 'like', "%{$search}%")
                            ->orWhere('bab_materi', 'like', "%{$search}%")
                            ->orWhere('judul_tugas', 'like', "%{$search}%");
                    });
                }
                if ($request->filled('jenis')) {
                    $q->where('jenis', $request->jenis);
                }
                if ($request->filled('mapel_id')) {
                    $q->where('mata_pelajaran_id', $request->mapel_id);
                }
            });

        $nilaiList = $query->get()->sortByDesc(fn($n) => $n->tugasMateri->tanggal ?? now());

        $totalEvaluasi   = $nilaiList->count();
        $dinilaiList     = $nilaiList->filter(fn($n) => (float)$n->nilai > 0);
        $rataRata        = $nilaiList->avg('nilai') ?? 0;
        $totalTuntas     = $nilaiList->filter(fn($n) => (float)$n->nilai >= 75)->count();
        $totalRemidi     = $nilaiList->filter(fn($n) => (float)$n->nilai > 0 && (float)$n->nilai < 75)->count();
        $totalBelum      = $nilaiList->filter(fn($n) => (float)$n->nilai == 0)->count();
        $tertinggi       = $dinilaiList->max('nilai') ?? 0;
        $terendah        = $dinilaiList->min('nilai') ?? 0;

        $mappedData = $nilaiList->map(function ($n) {
            $tm        = $n->tugasMateri;
            $skor      = (float) $n->nilai;
            $isTuntas  = $skor >= 75;
            $status    = $skor >= 75 ? 'Tuntas' : ($skor > 0 ? 'Remidi' : 'Belum Dinilai');
            $statusColor = $skor >= 75 ? '#10b981' : ($skor > 0 ? '#f59e0b' : '#6b7280');

            $predikat = match (true) {
                $skor >= 90 => 'A',
                $skor >= 80 => 'B',
                $skor >= 75 => 'C',
                $skor > 0   => 'D',
                default     => '-',
            };

            $mapelNama = !empty($tm->mata_pelajaran)
                ? $tm->mata_pelajaran
                : ($tm->mataPelajaran->nama ?? '-');

            return [
                'id'               => $n->id,
                'tugas_materi_id'  => $tm->id ?? null,
                'mata_pelajaran'   => $mapelNama,
                'kode_mapel'       => $tm->mataPelajaran->kode ?? '-',
                'guru_nama'        => $tm->guru->nama ?? '-',
                'bab_materi'       => $tm->bab_materi ?? '-',
                'judul_tugas'      => $tm->judul_tugas ?? '-',
                'jenis'            => $tm->jenis ?? 'tugas',
                'jenis_label'      => $tm->jenis_label ?? 'Tugas',
                'tanggal'          => $tm->tanggal?->format('Y-m-d') ?? '-',
                'tanggal_formatted'=> $tm->tanggal ? Carbon::parse($tm->tanggal)->translatedFormat('l, d F Y') : '-',
                'nilai'            => $skor,
                'nilai_formatted'  => number_format($skor, 1),
                'kkm'              => 75,
                'is_tuntas'        => $isTuntas,
                'predikat'         => $predikat,
                'status'           => $status,
                'status_color'     => $statusColor,
                'catatan_guru'     => $n->catatan_guru ?? '',
            ];
        })->values();

        return response()->json([
            'success'   => true,
            'rata_rata' => round($rataRata, 1), // Untuk backward compatibility
            'ringkasan' => [
                'rata_rata'           => round($rataRata, 1),
                'total_evaluasi'      => $totalEvaluasi,
                'total_tuntas'        => $totalTuntas,
                'total_remidi'        => $totalRemidi,
                'total_belum_dinilai' => $totalBelum,
                'tertinggi'           => (float) $tertinggi,
                'terendah'            => (float) $terendah,
                'kkm_default'         => 75,
            ],
            'data'      => $mappedData,
        ]);
    }
}
