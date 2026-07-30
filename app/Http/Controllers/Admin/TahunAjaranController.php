<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $tahunAjaran = TahunAjaran::withCount('kelas')->orderBy('nama', 'desc')->get();
        return view('admin.tahun-ajaran.index', compact('tahunAjaran'));
    }

    public function create()
    {
        return view('admin.tahun-ajaran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'     => 'required|string|max:20',
            'semester' => 'required|in:ganjil,genap',
        ]);

        TahunAjaran::create($validated + ['is_aktif' => false]);

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', "Tahun ajaran {$validated['nama']} berhasil ditambahkan.");
    }

    public function edit(TahunAjaran $tahunAjaran)
    {
        return view('admin.tahun-ajaran.edit', compact('tahunAjaran'));
    }

    public function update(Request $request, TahunAjaran $tahunAjaran)
    {
        $validated = $request->validate([
            'nama'     => 'required|string|max:20',
            'semester' => 'required|in:ganjil,genap',
        ]);

        $tahunAjaran->update($validated);

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(TahunAjaran $tahunAjaran)
    {
        if ($tahunAjaran->kelas()->count() > 0) {
            return back()->with('error', 'Tidak bisa hapus tahun ajaran yang masih memiliki kelas.');
        }
        $tahunAjaran->delete();
        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    /**
     * Aktifkan tahun ajaran ini (nonaktifkan yang lain).
     */
    public function aktifkan(TahunAjaran $tahunAjaran)
    {
        TahunAjaran::where('is_aktif', true)->update(['is_aktif' => false]);
        $tahunAjaran->update(['is_aktif' => true]);

        return back()->with('success', "Tahun ajaran {$tahunAjaran->nama_lengkap} sekarang aktif.");
    }
}
