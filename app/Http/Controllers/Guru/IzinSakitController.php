<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\IzinSakit;
use App\Models\Absensi;
use App\Jobs\KirimNotifikasiWA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IzinSakitController extends Controller
{
    /**
     * Daftar pengajuan izin/sakit untuk kelas wali.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $guru = $user->guru;

        $query = IzinSakit::with(['siswa.kelas.jurusan', 'disetujuiOleh'])
            ->orderBy('created_at', 'desc');

        // Guru hanya lihat izin dari kelasnya
        if ($user->hasRole('guru') && $guru) {
            $kelasIds = $guru->kelasWali->pluck('id');
            $query->whereHas('siswa', fn($q) => $q->whereIn('kelas_id', $kelasIds));
        }

        // Filter
        $query
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->jenis, fn($q) => $q->where('jenis', $request->jenis))
            ->when($request->tanggal, fn($q) => $q->where('tanggal_mulai', '<=', $request->tanggal)
                ->where('tanggal_selesai', '>=', $request->tanggal));

        $izinList = $query->paginate(20)->withQueryString();

        return view('guru.izin.index', compact('izinList'));
    }

    public function show(IzinSakit $izinSakit)
    {
        $izinSakit->load(['siswa.kelas', 'disetujuiOleh']);
        return view('guru.izin.show', compact('izinSakit'));
    }

    /**
     * Setujui pengajuan izin/sakit.
     */
    public function setujui(IzinSakit $izinSakit)
    {
        if (!$izinSakit->isPending()) {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $izinSakit->update([
            'status'         => 'disetujui',
            'disetujui_oleh' => Auth::id(),
        ]);

        // Update atau buat record absensi untuk setiap tanggal izin
        $tanggal = $izinSakit->tanggal_mulai->copy();
        while ($tanggal->lte($izinSakit->tanggal_selesai)) {
            $absensi = Absensi::updateOrCreate(
                [
                    'siswa_id' => $izinSakit->siswa_id,
                    'tanggal'  => $tanggal->toDateString(),
                ],
                [
                    'kelas_id'       => $izinSakit->siswa->kelas_id,
                    'dicatat_oleh'   => Auth::id(),
                    'jam_scan'       => null,
                    'status'         => $izinSakit->jenis,
                    'keterangan'     => $izinSakit->keterangan,
                    'lampiran'       => $izinSakit->lampiran,
                    'notif_terkirim' => false,
                ]
            );

            // Kirim notifikasi WA
            if ($izinSakit->siswa->no_wa_ortu) {
                KirimNotifikasiWA::dispatch($absensi->id)->onQueue('notifikasi-wa');
            }

            $tanggal->addDay();
        }

        return back()->with('success', "Pengajuan {$izinSakit->jenis} {$izinSakit->siswa->nama} berhasil disetujui.");
    }

    /**
     * Tolak pengajuan izin/sakit.
     */
    public function tolak(Request $request, IzinSakit $izinSakit)
    {
        $request->validate([
            'catatan_penolakan' => 'nullable|string|max:500',
        ]);

        if (!$izinSakit->isPending()) {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $izinSakit->update([
            'status'            => 'ditolak',
            'disetujui_oleh'    => Auth::id(),
            'catatan_penolakan' => $request->catatan_penolakan,
        ]);

        // Kirim notifikasi penolakan via WA
        // Dispatch job khusus jika template 'izin_ditolak' ada
        if ($izinSakit->siswa->no_wa_ortu) {
            // Buat dummy absensi atau kirim langsung
            // Untuk simplisitas, kita buat WaLog manual
            $siswa    = $izinSakit->siswa->load('kelas.waSender');
            $waSender = $siswa->kelas->waSender;

            if ($waSender && $waSender->isAktif()) {
                $template = \App\Models\TemplatePesan::aktif('izin_ditolak');
                if ($template) {
                    $pesan = $template->render([
                        'nama_siswa'   => $siswa->nama,
                        'nama_ortu'    => $siswa->nama_ortu ?? 'Orang Tua/Wali',
                        'tanggal'      => $izinSakit->tanggal_mulai->format('d/m/Y'),
                        'status'       => 'DITOLAK',
                        'kelas'        => $siswa->kelas->nama,
                        'nama_sekolah' => config('app.nama_sekolah', 'SMK'),
                        'keterangan'   => $request->catatan_penolakan ?? '-',
                        'jam'          => '-',
                    ]);

                    app(\App\Services\WaGatewayService::class)->kirim(
                        $waSender->api_key,
                        $siswa->no_wa_ortu_format,
                        $pesan
                    );
                }
            }
        }

        return back()->with('success', "Pengajuan {$izinSakit->jenis} {$izinSakit->siswa->nama} ditolak.");
    }
}
