<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\IzinSakit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiwayatController extends Controller
{
    private function getSiswa()
    {
        return Auth::user()->siswa;
    }

    public function index(Request $request)
    {
        $siswa = $this->getSiswa();
        if (!$siswa) {
            return redirect()->route('dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        $bulan = $request->bulan ?? now()->format('m');
        $tahun = $request->tahun ?? now()->format('Y');

        $tanggalMulai = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $tanggalAkhir = $tanggalMulai->copy()->endOfMonth();

        $riwayat = Absensi::where('siswa_id', $siswa->id)
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
            ->orderBy('tanggal', 'desc')
            ->get();

        // Rekap bulanan
        $rekap = $riwayat->groupBy('status')->map->count();

        return view('siswa.riwayat.index', compact('siswa', 'riwayat', 'rekap', 'bulan', 'tahun'));
    }

    public function show(Absensi $absensi)
    {
        $siswa = $this->getSiswa();
        if ($absensi->siswa_id !== $siswa?->id) {
            abort(403, 'Unauthorized.');
        }

        return view('siswa.riwayat.show', compact('absensi'));
    }

    public function izinIndex()
    {
        $siswa   = $this->getSiswa();
        $izinList = IzinSakit::where('siswa_id', $siswa->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('siswa.izin.index', compact('siswa', 'izinList'));
    }

    public function izinCreate()
    {
        $siswa = $this->getSiswa();
        return view('siswa.izin.create', compact('siswa'));
    }

    public function izinStore(Request $request)
    {
        $siswa = $this->getSiswa();

        $validated = $request->validate([
            'tanggal_mulai'   => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis'           => 'required|in:izin,sakit',
            'keterangan'      => 'required|string|max:500',
            'lampiran'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('lampiran')) {
            $validated['lampiran'] = $request->file('lampiran')->store('izin-sakit', 'public');
        }

        IzinSakit::create($validated + [
            'siswa_id' => $siswa->id,
            'status'   => 'pending',
        ]);

        return redirect()->route('siswa.izin.index')
            ->with('success', 'Pengajuan izin berhasil dikirim. Menunggu persetujuan wali kelas.');
    }
}
