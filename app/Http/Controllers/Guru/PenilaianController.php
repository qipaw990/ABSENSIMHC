<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\NilaiSiswa;
use App\Models\Siswa;
use App\Models\TugasMateri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenilaianController extends Controller
{
    /**
     * Tampilkan daftar tugas/materi & penilaian.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $guru = $user->guru;

        if ($user->hasRole('guru') && $guru) {
            $kelasList = $guru->getKelasAkses();
        } else {
            $kelasList = Kelas::with('jurusan')->orderBy('nama')->get();
        }

        $query = TugasMateri::with(['kelas', 'guru', 'nilaiSiswa']);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('mata_pelajaran', 'like', "%{$search}%")
                  ->orWhere('bab_materi', 'like', "%{$search}%")
                  ->orWhere('judul_tugas', 'like', "%{$search}%");
            });
        }

        $tugasList = $query->orderByDesc('tanggal')->paginate(15)->withQueryString();

        return view('guru.penilaian.index', compact('tugasList', 'kelasList'));
    }

    /**
     * Form tambah Tugas / Bab Pembelajaran baru.
     */
    public function create()
    {
        $user = Auth::user();
        $guru = $user->guru;

        if ($user->hasRole('guru') && $guru) {
            $kelasList = $guru->getKelasAkses();
        } else {
            $kelasList = Kelas::with('jurusan')->orderBy('nama')->get();
        }

        return view('guru.penilaian.create', compact('kelasList'));
    }

    /**
     * Simpan Tugas / Materi baru & persiapkan entri nilai siswa.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id'       => 'required|exists:kelas,id',
            'mata_pelajaran' => 'required|string|max:255',
            'bab_materi'     => 'required|string|max:255',
            'judul_tugas'    => 'required|string|max:255',
            'jenis'          => 'required|in:tugas,uh,uts,uas,praktikum',
            'tanggal'        => 'required|date',
            'keterangan'     => 'nullable|string|max:1000',
        ]);

        $guru = Auth::user()->guru;
        $validated['guru_id'] = $guru?->id;

        $tugas = TugasMateri::create($validated);

        // Buat record nilai awal (nilai 0) untuk seluruh siswa di kelas tersebut
        $siswaList = Siswa::where('kelas_id', $tugas->kelas_id)->get();
        foreach ($siswaList as $siswa) {
            NilaiSiswa::firstOrCreate([
                'tugas_materi_id' => $tugas->id,
                'siswa_id'        => $siswa->id,
            ], [
                'nilai' => 0,
            ]);
        }

        return redirect()->route('guru.penilaian.show', $tugas->id)
            ->with('success', "Tugas '{$tugas->judul_tugas}' (Bab: {$tugas->bab_materi}) berhasil dibuat! Silakan masukan nilai siswa.");
    }

    /**
     * Form input/edit nilai siswa per tugas (Batch Input).
     */
    public function show(TugasMateri $penilaian)
    {
        $penilaian->load(['kelas', 'guru', 'nilaiSiswa.siswa']);

        // Pastikan seluruh siswa di kelas tersebut punya record nilai
        $siswaList = Siswa::where('kelas_id', $penilaian->kelas_id)->orderBy('nama')->get();
        foreach ($siswaList as $siswa) {
            NilaiSiswa::firstOrCreate([
                'tugas_materi_id' => $penilaian->id,
                'siswa_id'        => $siswa->id,
            ], [
                'nilai' => 0,
            ]);
        }

        $nilaiList = NilaiSiswa::with('siswa')
            ->where('tugas_materi_id', $penilaian->id)
            ->get()
            ->sortBy('siswa.nama');

        return view('guru.penilaian.show', compact('penilaian', 'nilaiList'));
    }

    /**
     * Simpan nilai batch seluruh siswa.
     */
    public function storeNilaiBatch(Request $request, TugasMateri $penilaian)
    {
        $validated = $request->validate([
            'nilai'          => 'required|array',
            'nilai.*'        => 'required|numeric|min:0|max:100',
            'catatan_guru'   => 'nullable|array',
            'catatan_guru.*' => 'nullable|string|max:255',
        ]);

        foreach ($validated['nilai'] as $nilaiId => $skor) {
            $catatan = $validated['catatan_guru'][$nilaiId] ?? null;

            NilaiSiswa::where('id', $nilaiId)
                ->where('tugas_materi_id', $penilaian->id)
                ->update([
                    'nilai'        => $skor,
                    'catatan_guru' => $catatan,
                ]);
        }

        return back()->with('success', 'Nilai seluruh siswa berhasil diperbarui!');
    }

    /**
     * Form Edit rincian Tugas/Materi.
     */
    public function edit(TugasMateri $penilaian)
    {
        $kelasList = Kelas::with('jurusan')->orderBy('nama')->get();
        return view('guru.penilaian.edit', compact('penilaian', 'kelasList'));
    }

    /**
     * Update rincian Tugas/Materi.
     */
    public function update(Request $request, TugasMateri $penilaian)
    {
        $validated = $request->validate([
            'kelas_id'       => 'required|exists:kelas,id',
            'mata_pelajaran' => 'required|string|max:255',
            'bab_materi'     => 'required|string|max:255',
            'judul_tugas'    => 'required|string|max:255',
            'jenis'          => 'required|in:tugas,uh,uts,uas,praktikum',
            'tanggal'        => 'required|date',
            'keterangan'     => 'nullable|string|max:1000',
        ]);

        $penilaian->update($validated);

        return redirect()->route('guru.penilaian.show', $penilaian->id)
            ->with('success', 'Rincian tugas/materi berhasil diperbarui.');
    }

    /**
     * Hapus Tugas & seluruh nilai siswa terkait.
     */
    public function destroy(TugasMateri $penilaian)
    {
        $judul = $penilaian->judul_tugas;
        $penilaian->delete();

        return redirect()->route('guru.penilaian.index')
            ->with('success', "Tugas '{$judul}' dan seluruh data nilainya berhasil dihapus.");
    }
}
