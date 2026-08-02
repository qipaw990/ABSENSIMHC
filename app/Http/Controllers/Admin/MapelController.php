<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MapelController extends Controller
{
    /**
     * Tampilkan daftar master mata pelajaran.
     */
    public function index(Request $request)
    {
        $query = MataPelajaran::withCount('jadwalPelajaran');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelompok')) {
            $query->where('kelompok', $request->kelompok);
        }

        $mapelList = $query->orderBy('kode')->paginate(15)->withQueryString();

        return view('admin.mapel.index', compact('mapelList'));
    }

    /**
     * Simpan mapel baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'       => 'required|string|max:50|unique:mata_pelajaran,kode',
            'nama'       => 'required|string|max:255',
            'kelompok'   => 'required|in:normatif,adaptif,produktif,muatan_lokal',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $mapel = MataPelajaran::create($validated);

        return back()->with('success', "Mata Pelajaran '{$mapel->nama}' ({$mapel->kode}) berhasil ditambahkan.");
    }

    /**
     * Update data mapel.
     */
    public function update(Request $request, MataPelajaran $mapel)
    {
        $validated = $request->validate([
            'kode'       => ['required', 'string', 'max:50', Rule::unique('mata_pelajaran')->ignore($mapel->id)],
            'nama'       => 'required|string|max:255',
            'kelompok'   => 'required|in:normatif,adaptif,produktif,muatan_lokal',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $mapel->update($validated);

        return back()->with('success', "Data Mata Pelajaran '{$mapel->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus data mapel.
     */
    public function destroy(MataPelajaran $mapel)
    {
        $namaMapel = $mapel->nama;
        $mapel->delete();

        return back()->with('success', "Mata Pelajaran '{$namaMapel}' berhasil dihapus.");
    }
}
