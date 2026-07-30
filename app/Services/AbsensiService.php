<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\IzinSakit;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TemplatePesan;
use App\Models\WaLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AbsensiService
{
    public function __construct(
        private FonnteService $fonnteService
    ) {}

    /**
     * Proses scan QR code siswa oleh guru.
     *
     * @param string $qrToken Token dari QR Code yang discan
     * @param int    $kelasId Kelas yang sedang melakukan absensi
     * @return array ['success' => bool, 'message' => string, 'data' => Absensi|null, 'siswa' => Siswa|null]
     */
    public function prosesQrScan(string $qrToken, int $kelasId): array
    {
        // 1. Cari siswa berdasarkan QR token
        $siswa = Siswa::where('qr_token', $qrToken)
            ->where('qr_is_active', true)
            ->with(['kelas', 'kelas.pengaturanAbsensi'])
            ->first();

        if (!$siswa) {
            return [
                'success' => false,
                'message' => 'QR Code tidak valid atau sudah tidak aktif.',
                'data'    => null,
                'siswa'   => null,
            ];
        }

        // 2. Validasi apakah siswa dari kelas yang benar
        if ($siswa->kelas_id !== $kelasId) {
            return [
                'success' => false,
                'message' => "⚠️ Perhatian: {$siswa->nama} bukan siswa kelas ini! (Kelas: {$siswa->kelas->nama})",
                'data'    => null,
                'siswa'   => $siswa,
            ];
        }

        // 3. Cek apakah sudah absen hari ini
        $absensiExisting = $siswa->absensiHariIni();
        if ($absensiExisting) {
            return [
                'success' => false,
                'message' => "{$siswa->nama} sudah tercatat absen hari ini pukul " . ($absensiExisting->jam_scan ?? '-') . " dengan status " . $absensiExisting->status_label,
                'data'    => $absensiExisting,
                'siswa'   => $siswa,
            ];
        }

        // 4. Ambil pengaturan absensi kelas
        $pengaturan = $siswa->kelas->pengaturan;

        // 5. Tentukan status berdasarkan jam
        $jamSekarang = now()->format('H:i:s');

        if (!$pengaturan) {
            // Fallback: hadir sebelum 07:30, terlambat sesudahnya
            $status = $jamSekarang <= '07:30:00' ? 'hadir' : 'terlambat';
        } else {
            $status = $pengaturan->tentukanStatus($jamSekarang);
        }

        // 6. Simpan absensi ke database (dalam transaksi)
        try {
            DB::beginTransaction();

            $absensi = Absensi::create([
                'siswa_id'      => $siswa->id,
                'kelas_id'      => $kelasId,
                'dicatat_oleh'  => Auth::id(),
                'tanggal'       => today(),
                'jam_scan'      => $jamSekarang,
                'status'        => $status,
                'notif_terkirim' => false,
            ]);

            DB::commit();

            // 7. Kirim notifikasi WA langsung (synchronous, tanpa queue)
            if ($siswa->no_wa_ortu) {
                $this->kirimNotifikasiWALangsung($absensi, $siswa);
            }

            $statusLabel = $status === 'hadir' ? 'Hadir ✅' : 'Terlambat ⚠️';

            return [
                'success' => true,
                'message' => "{$siswa->nama} berhasil dicatat: {$statusLabel} pukul {$jamSekarang}",
                'data'    => $absensi,
                'siswa'   => $siswa,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AbsensiService::prosesQrScan - gagal simpan', [
                'siswa_id' => $siswa->id,
                'error'    => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
                'data'    => null,
                'siswa'   => $siswa,
            ];
        }
    }

    /**
     * Set status Alpha otomatis untuk siswa yang belum absen
     * setelah jam absensi tutup. Dipanggil oleh Scheduler.
     *
     * @param Carbon|null $tanggal Tanggal target (default: hari ini)
     * @return int Jumlah siswa yang di-set Alpha
     */
    public function autoSetAlpha(?Carbon $tanggal = null): int
    {
        $tanggal = $tanggal ?? today();
        $jumlah  = 0;

        // Skip hari Minggu
        if ($tanggal->dayOfWeek === Carbon::SUNDAY) {
            return 0;
        }

        $kelasList = Kelas::with(['siswa', 'pengaturanAbsensi'])->get();

        foreach ($kelasList as $kelas) {
            $pengaturan = $kelas->pengaturan;

            // Skip Sabtu jika kelas tidak aktif Sabtu
            if ($tanggal->dayOfWeek === Carbon::SATURDAY && !$pengaturan->aktif_sabtu) {
                continue;
            }

            // Siswa di kelas ini yang belum punya record absensi hari ini
            $siswaIds = $kelas->siswa->pluck('id');

            $sudahAbsenIds = Absensi::whereIn('siswa_id', $siswaIds)
                ->whereDate('tanggal', $tanggal)
                ->pluck('siswa_id');

            $belumAbsenIds = $siswaIds->diff($sudahAbsenIds);

            foreach ($belumAbsenIds as $siswaId) {
                $siswa = Siswa::find($siswaId);
                if (!$siswa) continue;

                // Cek ada izin/sakit yang sudah disetujui
                $izin = $siswa->punyaIzinPadaTanggal($tanggal);

                $statusFinal = 'alpha';
                if ($izin) {
                    $statusFinal = $izin->jenis; // 'izin' atau 'sakit'
                }

                // Buat record absensi alpha
                $admin = \App\Models\User::role('super_admin')->first()
                    ?? \App\Models\User::first();

                $absensi = Absensi::create([
                    'siswa_id'       => $siswaId,
                    'kelas_id'       => $kelas->id,
                    'dicatat_oleh'   => $admin->id,
                    'tanggal'        => $tanggal,
                    'jam_scan'       => null,
                    'status'         => $statusFinal,
                    'keterangan'     => $izin ? "Izin/Sakit disetujui: {$izin->keterangan}" : 'Auto Alpha oleh sistem',
                    'notif_terkirim' => false,
                ]);

                // Kirim notifikasi WA untuk alpha (bukan izin/sakit yang sudah dinotif)
                if ($statusFinal === 'alpha' && $siswa->no_wa_ortu) {
                    KirimNotifikasiWA::dispatch($absensi->id)
                        ->onQueue('notifikasi-wa')
                        ->delay(now()->addSeconds($jumlah * 3)); // Delay bertahap
                }

                $jumlah++;
            }
        }

        Log::info("AutoSetAlpha: {$jumlah} siswa di-set pada tanggal {$tanggal->toDateString()}");
        return $jumlah;
    }

    /**
     * Rekap statistik absensi per kelas untuk hari ini.
     */
    public function rekapHariIni(int $kelasId): array
    {
        $stats = Absensi::where('kelas_id', $kelasId)
            ->whereDate('tanggal', today())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $totalSiswa = Siswa::where('kelas_id', $kelasId)->count();

        return [
            'hadir'       => $stats['hadir']     ?? 0,
            'terlambat'   => $stats['terlambat'] ?? 0,
            'izin'        => $stats['izin']       ?? 0,
            'sakit'       => $stats['sakit']      ?? 0,
            'alpha'       => $stats['alpha']      ?? 0,
            'belum_absen' => $totalSiswa - array_sum($stats),
            'total_siswa' => $totalSiswa,
        ];
    }

    /**
     * Kirim notifikasi WhatsApp LANGSUNG (synchronous, tanpa queue/job).
     * Dipanggil setelah absensi tersimpan.
     */
    public function kirimNotifikasiWALangsung(Absensi $absensi, Siswa $siswa): void
    {
        try {
            $kelas    = $siswa->kelas->load('waSender');
            $waSender = $kelas->waSender;

            // Tidak ada WA sender untuk kelas ini
            if (!$waSender) {
                Log::warning("AbsensiService::kirimWA - Tidak ada WA Sender untuk kelas {$kelas->nama}");
                WaLog::create([
                    'wa_sender_id'    => null,
                    'absensi_id'      => $absensi->id,
                    'target_nomor'    => $siswa->no_wa_ortu_format ?: $siswa->no_wa_ortu,
                    'pesan'           => 'Gagal: tidak ada WA Sender untuk kelas ini',
                    'status'          => 'gagal',
                    'response_fonnte' => 'Tidak ada WA Sender',
                    'sent_at'         => null,
                ]);
                return;
            }

            // Format nomor tujuan
            $targetNomor = $siswa->no_wa_ortu_format ?: $siswa->no_wa_ortu;
            if (!$targetNomor) {
                Log::warning("AbsensiService::kirimWA - Siswa {$siswa->nama} tidak punya nomor WA ortu");
                return;
            }

            // Ambil template pesan berdasarkan status absensi
            $template = TemplatePesan::aktif($absensi->status);
            if (!$template) {
                Log::warning("AbsensiService::kirimWA - Template '{$absensi->status}' tidak ditemukan/nonaktif");
                // Buat pesan default jika template tidak ada
                $pesan = "Yth. Orang Tua/Wali {$siswa->nama},\n\nSiswa atas nama *{$siswa->nama}* telah tercatat *{$absensi->status_label}* pada:\n📅 " . Carbon::parse($absensi->tanggal)->translatedFormat('l, d F Y') . "\n🕐 Jam: " . ($absensi->jam_scan ?? '-') . "\n🏫 Kelas: {$kelas->nama}\n\nTerima kasih.";
            } else {
                // Render template dengan data nyata
                $pesan = $template->render([
                    'nama_siswa'   => $siswa->nama,
                    'nama_ortu'    => $siswa->nama_ortu ?? 'Orang Tua/Wali',
                    'jam'          => $absensi->jam_scan ?? '-',
                    'tanggal'      => Carbon::parse($absensi->tanggal)->translatedFormat('l, d F Y'),
                    'status'       => strtoupper($absensi->status_label),
                    'nama_sekolah' => config('app.nama_sekolah', 'SMK'),
                    'kelas'        => $kelas->nama,
                    'keterangan'   => $absensi->keterangan ?? '-',
                ]);
            }

            // Kirim via Fonnte API (synchronous)
            $hasil = $this->fonnteService->kirim(
                $waSender->token_fonnte,
                $targetNomor,
                $pesan
            );

            // Catat ke WaLog
            WaLog::create([
                'wa_sender_id'    => $waSender->id,
                'absensi_id'      => $absensi->id,
                'target_nomor'    => $targetNomor,
                'pesan'           => $pesan,
                'status'          => $hasil['success'] ? 'terkirim' : 'gagal',
                'response_fonnte' => is_array($hasil['response'])
                                        ? json_encode($hasil['response'])
                                        : (string) $hasil['response'],
                'sent_at'         => $hasil['success'] ? now() : null,
                'retry_count'     => 0,
            ]);

            // Update flag di absensi
            if ($hasil['success']) {
                $absensi->update(['notif_terkirim' => true]);
                Log::info("AbsensiService::kirimWA ✅ Terkirim ke {$targetNomor} ({$siswa->nama})");
            } else {
                Log::error("AbsensiService::kirimWA ❌ Gagal kirim ke {$targetNomor}", [
                    'siswa'    => $siswa->nama,
                    'response' => $hasil['response'],
                ]);
            }

        } catch (\Exception $e) {
            Log::error('AbsensiService::kirimNotifikasiWALangsung - Exception', [
                'siswa_id'   => $siswa->id,
                'absensi_id' => $absensi->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
