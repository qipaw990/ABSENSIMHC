<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PengaturanAbsensi;
use Illuminate\Http\Request;

class PengaturanAbsensiController extends Controller
{
    public function index()
    {
        $pengaturan = PengaturanAbsensi::with('kelas.jurusan')->orderBy('kelas_id')->get();
        $kelasliput = Kelas::whereDoesntHave('pengaturanAbsensi')->with('jurusan')->get();
        return view('admin.pengaturan-absensi.index', compact('pengaturan', 'kelasliput'));
    }

    public function create()
    {
        $kelasList = Kelas::whereDoesntHave('pengaturanAbsensi')->with('jurusan')->get();
        return view('admin.pengaturan-absensi.create', compact('kelasList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id'          => 'nullable|exists:kelas,id|unique:pengaturan_absensi,kelas_id',
            'jam_masuk_batas'   => 'required|date_format:H:i',
            'jam_absensi_tutup' => 'required|date_format:H:i|after:jam_masuk_batas',
            'aktif_sabtu'       => 'boolean',
        ]);

        $validated['jam_masuk_batas']   .= ':00';
        $validated['jam_absensi_tutup'] .= ':00';
        $validated['aktif_sabtu']        = $request->boolean('aktif_sabtu');

        PengaturanAbsensi::create($validated);

        return redirect()->route('admin.pengaturan-absensi.index')
            ->with('success', 'Pengaturan absensi berhasil disimpan.');
    }

    public function edit(PengaturanAbsensi $pengaturanAbsensi)
    {
        $kelasList = Kelas::with('jurusan')->get();
        return view('admin.pengaturan-absensi.edit', compact('pengaturanAbsensi', 'kelasList'));
    }

    public function update(Request $request, PengaturanAbsensi $pengaturanAbsensi)
    {
        $validated = $request->validate([
            'jam_masuk_batas'   => 'required|date_format:H:i',
            'jam_absensi_tutup' => 'required|date_format:H:i|after:jam_masuk_batas',
            'aktif_sabtu'       => 'boolean',
        ]);

        $validated['jam_masuk_batas']   .= ':00';
        $validated['jam_absensi_tutup'] .= ':00';
        $validated['aktif_sabtu']        = $request->boolean('aktif_sabtu');

        $pengaturanAbsensi->update($validated);

        return redirect()->route('admin.pengaturan-absensi.index')
            ->with('success', 'Pengaturan absensi berhasil diperbarui.');
    }

    public function destroy(PengaturanAbsensi $pengaturanAbsensi)
    {
        $pengaturanAbsensi->delete();
        return redirect()->route('admin.pengaturan-absensi.index')
            ->with('success', 'Pengaturan absensi dihapus (akan menggunakan default global).');
    }
}
