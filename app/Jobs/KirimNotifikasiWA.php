<?php

namespace App\Jobs;

use App\Models\Absensi;
use App\Models\TemplatePesan;
use App\Models\WaLog;
use App\Services\WaGatewayService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class KirimNotifikasiWA implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maksimal retry jika gagal.
     */
    public int $tries = 3;

    /**
     * Timeout per job dalam detik.
     */
    public int $timeout = 30;

    /**
     * Backoff delay antar retry (detik).
     */
    public array $backoff = [30, 60, 120];

    public function __construct(
        private int $absensiId
    ) {}

    public function handle(WaGatewayService $waGatewayService): void
    {
        // 1. Ambil data absensi beserta relasi yang dibutuhkan
        $absensi = Absensi::with([
            'siswa.kelas.waSender',
            'siswa.kelas',
        ])->find($this->absensiId);

        if (!$absensi) {
            Log::warning("KirimNotifikasiWA: Absensi ID {$this->absensiId} tidak ditemukan.");
            return;
        }

        $siswa    = $absensi->siswa;
        $kelas    = $siswa->kelas;
        $waSender = $kelas->waSender;

        // 2. Validasi: ada WA sender aktif untuk kelas ini?
        if (!$waSender) {
            Log::warning("KirimNotifikasiWA: Tidak ada WA Sender untuk kelas {$kelas->nama}");
            $this->catatLog($absensi, null, null, 'Tidak ada WA Sender', 'gagal');
            return;
        }

        // Jika status terputus/nonaktif, coba tetap kirim (mungkin sudah reconnect)
        if (!$waSender->isAktif()) {
            Log::warning("KirimNotifikasiWA: WA Sender kelas {$kelas->nama} status: {$waSender->status}, tetap mencoba kirim...");
        }

        // 3. Validasi: ada nomor WA orang tua?
        $targetNomor = $siswa->no_wa_ortu_format;
        if (!$targetNomor) {
            Log::warning("KirimNotifikasiWA: Siswa {$siswa->nama} tidak punya nomor WA orang tua.");
            return;
        }

        // 4. Ambil template pesan sesuai status absensi
        $template = TemplatePesan::aktif($absensi->status);

        if (!$template) {
            Log::warning("KirimNotifikasiWA: Template pesan '{$absensi->status}' tidak ditemukan/nonaktif.");
            return;
        }

        // 5. Render pesan dengan data nyata
        $namaSekolah = config('app.nama_sekolah', 'SMK');
        $pesan = $template->render([
            'nama_siswa'   => $siswa->nama,
            'nama_ortu'    => $siswa->nama_ortu ?? 'Orang Tua/Wali',
            'jam'          => $absensi->jam_scan ?? '-',
            'tanggal'      => Carbon::parse($absensi->tanggal)->translatedFormat('l, d F Y'),
            'status'       => strtoupper($absensi->status_label),
            'nama_sekolah' => $namaSekolah,
            'kelas'        => $kelas->nama,
            'keterangan'   => $absensi->keterangan ?? '-',
        ]);

        // 6. Buat log WA dulu dengan status antrian
        $waLog = $this->catatLog($absensi, $waSender->id, $targetNomor, $pesan, 'antrian');

        // 7. Kirim via WhatsApp Gateway API
        $hasil = $waGatewayService->kirim(
            $waSender->api_key,
            $targetNomor,
            $pesan
        );

        // 8. Update log berdasarkan hasil pengiriman
        $statusLog = $hasil['success'] ? 'terkirim' : 'gagal';
        $currentAttempt = method_exists($this, 'attempts') ? $this->attempts() : 1;

        $waLog->update([
            'status'          => $statusLog,
            'response_fonnte' => is_array($hasil['response'])
                                    ? json_encode($hasil['response'])
                                    : (string) $hasil['response'],
            'sent_at'         => $hasil['success'] ? now() : null,
            'retry_count'     => $currentAttempt,
        ]);

        // 9. Update flag notif_terkirim di absensi
        if ($hasil['success']) {
            $absensi->update(['notif_terkirim' => true]);
            Log::info("KirimNotifikasiWA: ✅ Berhasil kirim ke {$targetNomor} untuk absensi ID {$this->absensiId}");
        } else {
            Log::error("KirimNotifikasiWA: ❌ Gagal kirim ke {$targetNomor}", [
                'absensi_id' => $this->absensiId,
                'response'   => $hasil['response'],
                'attempt'    => $currentAttempt,
            ]);

            // Retry hanya jika bukan sync driver
            if (config('queue.default') !== 'sync' && $currentAttempt < $this->tries) {
                throw new \Exception("WhatsApp Gateway API gagal: " . $hasil['response']);
            }
        }
    }

    /**
     * Catat log pengiriman WA ke database.
     */
    private function catatLog(
        Absensi $absensi,
        ?int    $waSenderId,
        ?string $targetNomor,
        string  $pesan,
        string  $status
    ): WaLog {
        return WaLog::create([
            'wa_sender_id'   => $waSenderId,
            'absensi_id'     => $absensi->id,
            'target_nomor'   => $targetNomor ?? '-',
            'pesan'          => $pesan,
            'status'         => $status,
            'response_fonnte' => null,
            'retry_count'    => 0,
        ]);
    }

    /**
     * Handle jika job benar-benar gagal setelah semua retry habis.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("KirimNotifikasiWA: Job failed permanently untuk absensi ID {$this->absensiId}", [
            'error' => $exception->getMessage(),
        ]);

        // Update semua log dengan status gagal
        WaLog::where('absensi_id', $this->absensiId)
            ->where('status', 'antrian')
            ->update(['status' => 'gagal']);
    }
}
