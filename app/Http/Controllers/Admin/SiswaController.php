<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SiswaTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\SiswaImport;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $siswa = Siswa::with('kelas.jurusan')
            ->when($request->search, fn($q) => $q->where('nama', 'like', '%' . $request->search . '%')
                ->orWhere('nis', 'like', '%' . $request->search . '%'))
            ->when($request->kelas_id, fn($q) => $q->where('kelas_id', $request->kelas_id))
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        $kelasList = Kelas::with('jurusan')->orderBy('nama')->get();

        return view('admin.siswa.index', compact('siswa', 'kelasList'));
    }

    public function create()
    {
        $kelasList = Kelas::with('jurusan')->orderBy('nama')->get();
        return view('admin.siswa.create', compact('kelasList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis'         => 'required|string|max:20|unique:siswa',
            'nisn'        => 'nullable|string|max:20|unique:siswa',
            'nama'        => 'required|string|max:100',
            'kelas_id'    => 'required|exists:kelas,id',
            'no_wa_ortu'  => 'nullable|string|max:20',
            'nama_ortu'   => 'nullable|string|max:100',
            'foto'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle upload foto
        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('siswa/foto', 'public');
        }

        // Generate QR token unik
        $validated['qr_token'] = Siswa::generateQrToken();

        // Buat akun user untuk siswa (opsional, dengan NIS sebagai email)
        $user = User::create([
            'name'     => $validated['nama'],
            'email'    => $validated['nis'] . '@siswa.sch.id',
            'password' => Hash::make($validated['nis']), // Password default = NIS
        ]);
        $user->assignRole('siswa');
        $validated['user_id'] = $user->id;

        Siswa::create($validated);

        return redirect()->route('admin.siswa.index')
            ->with('success', "Siswa {$validated['nama']} berhasil ditambahkan! (Password default: NIS)");
    }

    public function show(Siswa $siswa)
    {
        $siswa->load(['kelas.jurusan', 'absensi' => fn($q) => $q->orderBy('tanggal', 'desc')->limit(30)]);

        // SVG — tidak butuh Imagick/GD, aman di semua environment
        $qrSvg    = QrCode::format('svg')->size(250)->margin(1)->generate($siswa->qr_token);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        return view('admin.siswa.show', compact('siswa', 'qrBase64'));
    }

    public function edit(Siswa $siswa)
    {
        $kelasList = Kelas::with('jurusan')->orderBy('nama')->get();
        return view('admin.siswa.edit', compact('siswa', 'kelasList'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nis'        => 'required|string|max:20|unique:siswa,nis,' . $siswa->id,
            'nisn'       => 'nullable|string|max:20|unique:siswa,nisn,' . $siswa->id,
            'nama'       => 'required|string|max:100',
            'kelas_id'   => 'required|exists:kelas,id',
            'no_wa_ortu' => 'nullable|string|max:20',
            'nama_ortu'  => 'nullable|string|max:100',
            'foto'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            // Hapus foto lama
            if ($siswa->foto) Storage::disk('public')->delete($siswa->foto);
            $validated['foto'] = $request->file('foto')->store('siswa/foto', 'public');
        }

        $siswa->update($validated);

        return redirect()->route('admin.siswa.index')
            ->with('success', "Data siswa {$siswa->nama} berhasil diperbarui!");
    }

    public function destroy(Siswa $siswa)
    {
        if ($siswa->foto) Storage::disk('public')->delete($siswa->foto);
        if ($siswa->user) $siswa->user->delete();
        $siswa->delete();

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil dihapus.');
    }

    /**
     * Regenerate QR token siswa (QR lama otomatis invalid).
     */
    public function regenerateQr(Siswa $siswa)
    {
        $siswa->regenerateQr();
        return back()->with('success', "QR Code {$siswa->nama} berhasil di-generate ulang. QR lama sudah tidak berlaku.");
    }

    /**
     * Cetak kartu QR siswa (PDF).
     * Menggunakan SVG backend — tidak membutuhkan Imagick/GD.
     */
    public function cetakKartu(Siswa $siswa)
    {
        $siswa->load('kelas.jurusan');

        // QR kecil (200px) = SVG lebih kecil = render lebih cepat
        $qrSvg    = QrCode::format('svg')->size(200)->margin(0)->generate($siswa->qr_token);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
        $logoBase64  = $this->getLogoBase64();
        $namaSekolah = config('app.nama_sekolah', 'SMK');

        $html = trim(view('admin.siswa.kartu-pdf', compact(
            'siswa', 'qrBase64', 'logoBase64', 'namaSekolah'
        ))->render());

        /** @var \Barryvdh\DomPDF\PDF $pdf */
        $pdf = app('dompdf.wrapper');
        $pdf->setOptions([
            'dpi'             => 96,
            'isRemoteEnabled' => false,  // logo sudah base64, tidak butuh remote/local
            'defaultFont'     => 'Arial',
        ]);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper([0, 0, 242.65, 226.77]); // 85.6mm × 80mm dalam pt

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="kartu-qr-' . $siswa->nis . '.pdf"',
        ]);
    }

    /**
     * Cetak batch kartu QR satu kelas (PDF).
     */
    public function cetakBatch(int $kelas_id)
    {
        $kelas     = Kelas::with('jurusan')->findOrFail($kelas_id);
        $siswaList = Siswa::where('kelas_id', $kelas_id)->orderBy('nama')->get();

        $namaSekolah = config('app.nama_sekolah', 'SMK');
        $logoBase64  = $this->getLogoBase64();
        $qrCodes     = [];

        foreach ($siswaList as $siswa) {
            // QR 180px sudah cukup untuk batch — lebih kecil = lebih cepat
            $qrSvg = QrCode::format('svg')->size(180)->margin(0)->generate($siswa->qr_token);
            $qrCodes[$siswa->id] = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
        }

        $pdf = app('dompdf.wrapper');
        $pdf->setOptions([
            'dpi'             => 96,
            'isRemoteEnabled' => false,
            'defaultFont'     => 'Arial',
        ]);
        $pdf->loadView('admin.siswa.kartu-batch-pdf',
            compact('kelas', 'siswaList', 'qrCodes', 'namaSekolah', 'logoBase64')
        );
        $pdf->setPaper('A4');

        return $pdf->stream("kartu-qr-{$kelas->nama}.pdf");
    }

    /**
     * Helper: baca logo sekolah sekali & encode ke base64 data URI.
     * Dompdf tidak perlu akses file → render lebih cepat.
     */
    private function getLogoBase64(): string
    {
        $logoPath = public_path('images/logo-smk.png');
        if (file_exists($logoPath)) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }
        // Fallback: kotak kuning jika logo tidak ada
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwADhQGAWjR9awAAAABJRU5ErkJggg==';
    }

    /**
     * Import siswa dari Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new SiswaImport();
        Excel::import($import, $request->file('file'));

        $message = "Import selesai! Berhasil: {$import->imported} siswa.";
        if ($import->skipped > 0) {
            $message .= " Dilewati: {$import->skipped}.";
        }

        $session = ['success' => $message];
        if (!empty($import->importErrors)) {
            $session['import_errors'] = $import->importErrors;
        }

        return back()->with($session);
    }

    /**
     * Download template Excel untuk import siswa (generated dynamically).
     */
    public function templateImport()
    {
        return Excel::download(new SiswaTemplateExport(), 'template-import-siswa.xlsx');
    }
}
