<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusan = Jurusan::withCount('kelas')->orderBy('nama')->paginate(20);
        return view('admin.jurusan.index', compact('jurusan'));
    }

    public function create()
    {
        return view('admin.jurusan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:jurusan',
            'nama' => 'required|string|max:100',
        ]);

        Jurusan::create($validated);
        return redirect()->route('admin.jurusan.index')
            ->with('success', "Jurusan {$validated['nama']} berhasil ditambahkan.");
    }

    public function edit(Jurusan $jurusan)
    {
        return view('admin.jurusan.edit', compact('jurusan'));
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:jurusan,kode,' . $jurusan->id,
            'nama' => 'required|string|max:100',
        ]);

        $jurusan->update($validated);
        return redirect()->route('admin.jurusan.index')
            ->with('success', "Jurusan {$jurusan->nama} berhasil diperbarui.");
    }

    public function destroy(Jurusan $jurusan)
    {
        if ($jurusan->kelas()->count() > 0) {
            return back()->with('error', 'Tidak bisa hapus jurusan yang masih memiliki kelas.');
        }
        $jurusan->delete();
        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Jurusan berhasil dihapus.');
    }
}
