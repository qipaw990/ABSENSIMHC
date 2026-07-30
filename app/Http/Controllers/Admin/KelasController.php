<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with(['jurusan', 'tahunAjaran', 'waliKelas'])
            ->withCount('siswa')
            ->orderBy('nama')
            ->paginate(20);

        return view('admin.kelas.index', compact('kelas'));
    }

    public function create()
    {
        $jurusan     = Jurusan::orderBy('nama')->get();
        $tahunAjaran = TahunAjaran::orderBy('nama', 'desc')->get();
        $guru        = Guru::orderBy('nama')->get();
        return view('admin.kelas.create', compact('jurusan', 'tahunAjaran', 'guru'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jurusan_id'      => 'required|exists:jurusan,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'wali_kelas_id'   => 'nullable|exists:guru,id',
            'nama'            => 'required|string|max:50',
            'tingkat'         => 'required|integer|in:10,11,12',
        ]);

        Kelas::create($validated);

        return redirect()->route('admin.kelas.index')
            ->with('success', "Kelas {$validated['nama']} berhasil ditambahkan.");
    }

    public function show(Kelas $kelas)
    {
        $kelas->load(['jurusan', 'tahunAjaran', 'waliKelas', 'waSender', 'pengaturanAbsensi']);
        $siswaCount = $kelas->siswa()->count();
        return view('admin.kelas.show', compact('kelas', 'siswaCount'));
    }

    public function edit(Kelas $kelas)
    {
        $jurusan     = Jurusan::orderBy('nama')->get();
        $tahunAjaran = TahunAjaran::orderBy('nama', 'desc')->get();
        $guru        = Guru::orderBy('nama')->get();
        return view('admin.kelas.edit', compact('kelas', 'jurusan', 'tahunAjaran', 'guru'));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $validated = $request->validate([
            'jurusan_id'      => 'required|exists:jurusan,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'wali_kelas_id'   => 'nullable|exists:guru,id',
            'nama'            => 'required|string|max:50',
            'tingkat'         => 'required|integer|in:10,11,12',
        ]);

        $kelas->update($validated);

        return redirect()->route('admin.kelas.index')
            ->with('success', "Kelas {$kelas->nama} berhasil diperbarui.");
    }

    public function destroy(Kelas $kelas)
    {
        if ($kelas->siswa()->count() > 0) {
            return back()->with('error', 'Tidak bisa hapus kelas yang masih memiliki siswa.');
        }
        $kelas->delete();
        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }

    public function daftarSiswa(Kelas $kelas)
    {
        $kelas->load(['jurusan']);
        $siswa = $kelas->siswa()->orderBy('nama')->paginate(30);
        return view('admin.kelas.siswa', compact('kelas', 'siswa'));
    }
}
