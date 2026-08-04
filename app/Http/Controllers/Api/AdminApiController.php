<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PengaturanAbsensi;
use App\Models\Siswa;
use App\Models\User;
use App\Models\WaLog;
use App\Models\WaSender;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminApiController extends Controller
{
    /**
     * Dashboard statistik executive seluruh sekolah hari ini.
     * GET /api/admin/dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        $totalSiswa  = Siswa::count();
        $totalGuru   = Guru::count();
        $totalKelas  = Kelas::count();
        $totalMapel  = MataPelajaran::count();

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
                'terlambat' => $stats['terlambat'] ?? 0,
                'alpha'     => $stats['alpha']     ?? 0,
                'izin'      => $stats['izin']      ?? 0,
                'sakit'     => $stats['sakit']     ?? 0,
                'belum'     => max(0, $totalSiswaKelas - array_sum($stats)),
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
                'izin_sakit'=> ($stats['izin'] ?? 0) + ($stats['sakit'] ?? 0),
            ];
        });

        // Ringkasan status WA Gateway
        $waActive = WaSender::where('status', 'aktif')->count();
        $waTotal  = WaSender::count();

        return response()->json([
            'success'     => true,
            'tanggal'     => now()->translatedFormat('l, d F Y'),
            'ringkasan'   => [
                'total_siswa'  => $totalSiswa,
                'total_guru'   => $totalGuru,
                'total_kelas'  => $totalKelas,
                'total_mapel'  => $totalMapel,
                'hadir'        => ($statsHariIni['hadir'] ?? 0) + ($statsHariIni['terlambat'] ?? 0),
                'terlambat'    => $statsHariIni['terlambat'] ?? 0,
                'izin'         => $statsHariIni['izin']       ?? 0,
                'sakit'        => $statsHariIni['sakit']      ?? 0,
                'alpha'        => $statsHariIni['alpha']      ?? 0,
                'belum_absen'  => max(0, $belumAbsen),
                'wa_active'    => $waActive,
                'wa_total'     => $waTotal,
            ],
            'per_kelas'   => $perKelas,
            'chart_7_hari' => $chart7Hari,
        ]);
    }

    // ─── SISWA MANAGEMENT ──────────────────────────────────────────────────

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

        $siswaPaginator = $query->orderBy('nama')->paginate(20);

        $mappedData = collect($siswaPaginator->items())->map(fn($s) => [
            'id'           => $s->id,
            'nis'          => $s->nis,
            'nisn'         => $s->nisn ?? '-',
            'nama'         => $s->nama,
            'foto_url'     => $s->foto_url,
            'kelas_id'     => $s->kelas_id,
            'kelas'        => $s->kelas->nama ?? '-',
            'jurusan'      => $s->kelas->jurusan->nama ?? '-',
            'nama_ortu'    => $s->nama_ortu ?? '-',
            'no_wa_ortu'   => $s->no_wa_ortu ?? '-',
            'qr_token'     => $s->qr_token,
            'qr_is_active' => (bool) $s->qr_is_active,
        ])->values();

        return response()->json([
            'success'    => true,
            'total'      => $siswaPaginator->total(),
            'data'       => $mappedData,
            'pagination' => [
                'current_page' => $siswaPaginator->currentPage(),
                'last_page'    => $siswaPaginator->lastPage(),
                'per_page'     => $siswaPaginator->perPage(),
                'total'        => $siswaPaginator->total(),
            ],
        ]);
    }

    /**
     * Detail Siswa per ID.
     * GET /api/admin/siswa/{id}
     */
    public function siswaDetail(int $id): JsonResponse
    {
        $siswa = Siswa::with('kelas.jurusan')->findOrFail($id);

        $stats = Absensi::where('siswa_id', $siswa->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $siswa->id,
                'nis'          => $siswa->nis,
                'nisn'         => $siswa->nisn ?? '-',
                'nama'         => $siswa->nama,
                'foto_url'     => $siswa->foto_url,
                'kelas_id'     => $siswa->kelas_id,
                'kelas'        => $siswa->kelas->nama ?? '-',
                'jurusan'      => $siswa->kelas->jurusan->nama ?? '-',
                'nama_ortu'    => $siswa->nama_ortu ?? '-',
                'no_wa_ortu'   => $siswa->no_wa_ortu ?? '-',
                'qr_token'     => $siswa->qr_token,
                'qr_is_active' => (bool) $siswa->qr_is_active,
                'stats'        => [
                    'hadir'     => ($stats['hadir'] ?? 0) + ($stats['terlambat'] ?? 0),
                    'terlambat' => $stats['terlambat'] ?? 0,
                    'izin'      => $stats['izin']      ?? 0,
                    'sakit'     => $stats['sakit']     ?? 0,
                    'alpha'     => $stats['alpha']     ?? 0,
                ],
            ],
        ]);
    }

    /**
     * Tambah Data Siswa Baru.
     * POST /api/admin/siswa
     */
    public function siswaStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kelas_id'   => 'required|exists:kelas,id',
            'nis'        => 'required|string|max:50|unique:siswa,nis',
            'nisn'       => 'nullable|string|max:50',
            'nama'       => 'required|string|max:255',
            'nama_ortu'  => 'nullable|string|max:255',
            'no_wa_ortu' => 'nullable|string|max:30',
        ]);

        $validated['qr_token'] = Siswa::generateQrToken();
        $validated['qr_is_active'] = true;

        $siswa = Siswa::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Data siswa '{$siswa->nama}' berhasil ditambahkan.",
            'data'    => $siswa,
        ], 201);
    }

    /**
     * Update Data Siswa.
     * PUT /api/admin/siswa/{id}
     */
    public function siswaUpdate(Request $request, int $id): JsonResponse
    {
        $siswa = Siswa::findOrFail($id);

        $validated = $request->validate([
            'kelas_id'   => 'required|exists:kelas,id',
            'nis'        => "required|string|max:50|unique:siswa,nis,{$id}",
            'nisn'       => 'nullable|string|max:50',
            'nama'       => 'required|string|max:255',
            'nama_ortu'  => 'nullable|string|max:255',
            'no_wa_ortu' => 'nullable|string|max:30',
        ]);

        $siswa->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Data siswa '{$siswa->nama}' berhasil diperbarui.",
            'data'    => $siswa,
        ]);
    }

    /**
     * Hapus Data Siswa.
     * DELETE /api/admin/siswa/{id}
     */
    public function siswaDestroy(int $id): JsonResponse
    {
        $siswa = Siswa::findOrFail($id);
        $nama  = $siswa->nama;
        $siswa->delete();

        return response()->json([
            'success' => true,
            'message' => "Data siswa '{$nama}' berhasil dihapus.",
        ]);
    }

    // ─── GURU MANAGEMENT ───────────────────────────────────────────────────

    /**
     * Monitoring & Pencarian Data Guru.
     * GET /api/admin/guru?search=budi
     */
    public function guruList(Request $request): JsonResponse
    {
        $query = Guru::with('kelasWali');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $guruList = $query->orderBy('nama')->get();

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
     * Tambah Guru Baru.
     * POST /api/admin/guru
     */
    public function guruStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama'     => 'required|string|max:255',
            'nip'      => 'nullable|string|max:50|unique:guru,nip',
            'no_hp'    => 'nullable|string|max:30',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        DB::transaction(function () use ($validated, &$guru) {
            $user = User::create([
                'name'     => $validated['nama'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
            $user->assignRole('guru');

            $guru = Guru::create([
                'user_id' => $user->id,
                'nama'    => $validated['nama'],
                'nip'     => $validated['nip'] ?? null,
                'no_hp'   => $validated['no_hp'] ?? null,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "Guru '{$guru->nama}' berhasil ditambahkan.",
            'data'    => $guru,
        ], 201);
    }

    /**
     * Update Data Guru.
     * PUT /api/admin/guru/{id}
     */
    public function guruUpdate(Request $request, int $id): JsonResponse
    {
        $guru = Guru::findOrFail($id);

        $validated = $request->validate([
            'nama'  => 'required|string|max:255',
            'nip'   => "nullable|string|max:50|unique:guru,nip,{$id}",
            'no_hp' => 'nullable|string|max:30',
        ]);

        $guru->update($validated);

        if ($guru->user) {
            $guru->user->update(['name' => $validated['nama']]);
        }

        return response()->json([
            'success' => true,
            'message' => "Data guru '{$guru->nama}' berhasil diperbarui.",
            'data'    => $guru,
        ]);
    }

    /**
     * Hapus Data Guru.
     * DELETE /api/admin/guru/{id}
     */
    public function guruDestroy(int $id): JsonResponse
    {
        $guru = Guru::findOrFail($id);
        $nama = $guru->nama;

        if ($guru->user) {
            $guru->user->delete();
        } else {
            $guru->delete();
        }

        return response()->json([
            'success' => true,
            'message' => "Data guru '{$nama}' berhasil dihapus.",
        ]);
    }

    // ─── KELAS MANAGEMENT ──────────────────────────────────────────────────

    /**
     * Daftar Seluruh Kelas.
     * GET /api/admin/kelas
     */
    public function kelasList(Request $request): JsonResponse
    {
        $kelasList = Kelas::with(['jurusan', 'waliKelas'])->orderBy('nama')->get();

        return response()->json([
            'success' => true,
            'data'    => $kelasList->map(fn($k) => [
                'id'          => $k->id,
                'nama'        => $k->nama,
                'jurusan'     => $k->jurusan->nama ?? '-',
                'wali_kelas'  => $k->waliKelas->nama ?? 'Belum ditentukan',
                'total_siswa' => $k->siswa()->count(),
            ]),
        ]);
    }

    /**
     * Tambah Kelas Baru.
     * POST /api/admin/kelas
     */
    public function kelasStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama'          => 'required|string|max:100|unique:kelas,nama',
            'jurusan_id'    => 'required|exists:jurusan,id',
            'wali_kelas_id' => 'nullable|exists:guru,id',
        ]);

        $kelas = Kelas::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Kelas '{$kelas->nama}' berhasil dibuat.",
            'data'    => $kelas,
        ], 201);
    }

    // ─── WA SENDER & LOGS ──────────────────────────────────────────────────

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
     * GET /api/admin/wa-logs?search=...
     */
    public function waLogsList(Request $request): JsonResponse
    {
        $query = WaLog::with('siswa');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('recipient', 'like', "%{$search}%")
                  ->orWhereHas('siswa', fn($s) => $s->where('nama', 'like', "%{$search}%"));
            });
        }

        $logsPaginator = $query->orderByDesc('created_at')->paginate(20);

        $mappedLogs = collect($logsPaginator->items())->map(fn($l) => [
            'id'               => $l->id,
            'recipient'        => $l->recipient,
            'siswa_nama'       => $l->siswa->nama ?? '-',
            'pesan'            => $l->message ?? '-',
            'status'           => $l->status,
            'created_at'       => $l->created_at?->format('Y-m-d H:i:s'),
            'created_at_label' => $l->created_at ? $l->created_at->diffForHumans() : '-',
        ])->values();

        return response()->json([
            'success'    => true,
            'total'      => $logsPaginator->total(),
            'data'       => $mappedLogs,
            'pagination' => [
                'current_page' => $logsPaginator->currentPage(),
                'last_page'    => $logsPaginator->lastPage(),
                'per_page'     => $logsPaginator->perPage(),
                'total'        => $logsPaginator->total(),
            ],
        ]);
    }

    // ─── MASTER MAPEL & JADWAL & USERS ─────────────────────────────────────

    /**
     * User Management API.
     * GET /api/admin/users
     */
    public function userList(Request $request): JsonResponse
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $usersPaginator = $query->orderBy('name')->paginate(20);

        $mappedUsers = collect($usersPaginator->items())->map(fn($u) => [
            'id'         => $u->id,
            'name'       => $u->name,
            'email'      => $u->email,
            'roles'      => $u->roles->pluck('name'),
            'role_label' => strtoupper($u->roles->pluck('name')->implode(', ')),
            'created_at' => $u->created_at?->format('Y-m-d H:i:s'),
        ])->values();

        return response()->json([
            'success'    => true,
            'total'      => $usersPaginator->total(),
            'data'       => $mappedUsers,
            'pagination' => [
                'current_page' => $usersPaginator->currentPage(),
                'last_page'    => $usersPaginator->lastPage(),
                'per_page'     => $usersPaginator->perPage(),
                'total'        => $usersPaginator->total(),
            ],
        ]);
    }

    /**
     * Data Master Mapel API.
     * GET /api/admin/mapel
     */
    public function mapelList(Request $request): JsonResponse
    {
        $mapel = MataPelajaran::orderBy('kode')->get();

        return response()->json([
            'success' => true,
            'data'    => $mapel->map(fn($m) => [
                'id'        => $m->id,
                'kode'      => $m->kode,
                'nama'      => $m->nama,
                'kelompok'  => $m->kelompok_label,
            ]),
        ]);
    }

    /**
     * Tambah Mapel Baru.
     * POST /api/admin/mapel
     */
    public function mapelStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode'     => 'required|string|max:50|unique:mata_pelajaran,kode',
            'nama'     => 'required|string|max:255',
            'kelompok' => 'required|in:normatif,adaptif,produktif,muatan_lokal',
        ]);

        $mapel = MataPelajaran::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Mata pelajaran '{$mapel->nama}' berhasil ditambahkan.",
            'data'    => $mapel,
        ], 201);
    }

    /**
     * Jadwal Pelajaran API.
     * GET /api/admin/jadwal
     */
    public function jadwalList(Request $request): JsonResponse
    {
        $query = JadwalPelajaran::with(['kelas', 'mataPelajaran', 'guru']);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }

        $jadwal = $query->orderBy('jam_mulai')->get();

        return response()->json([
            'success' => true,
            'data'    => $jadwal->map(fn($j) => [
                'id'            => $j->id,
                'kelas'         => $j->kelas->nama ?? '-',
                'mata_pelajaran'=> $j->mataPelajaran->nama ?? '-',
                'guru'          => $j->guru->nama ?? '-',
                'hari'          => $j->hari_label,
                'jam'           => $j->jam_format,
                'ruangan'       => $j->ruangan ?? 'Kelas Reguler',
            ]),
        ]);
    }

    // ─── PENGATURAN ABSENSI SEKOLAH ───────────────────────────────────────

    /**
     * Get Pengaturan Jam Absensi Sekolah.
     * GET /api/admin/pengaturan-absensi
     */
    public function pengaturanAbsensi(): JsonResponse
    {
        $pengaturan = PengaturanAbsensi::firstOrCreate(
            ['kelas_id' => null],
            [
                'jam_masuk_batas'   => '07:00:00',
                'jam_absensi_tutup' => '12:00:00',
                'aktif_sabtu'       => false,
            ]
        );

        return response()->json([
            'success' => true,
            'data'    => $pengaturan,
        ]);
    }

    /**
     * Update Pengaturan Jam Absensi Sekolah.
     * POST /api/admin/pengaturan-absensi
     */
    public function pengaturanAbsensiUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'jam_masuk_batas'   => 'required|string',
            'jam_absensi_tutup' => 'required|string',
            'aktif_sabtu'       => 'required|boolean',
        ]);

        $pengaturan = PengaturanAbsensi::firstOrCreate(['kelas_id' => null]);
        $pengaturan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan jam absensi sekolah berhasil diperbarui.',
            'data'    => $pengaturan,
        ]);
    }
}
