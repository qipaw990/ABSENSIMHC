<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class JadwalPelajaranController extends Controller
{
    /**
     * Tampilkan jadwal pelajaran.
     */
    public function index(Request $request)
    {
        $query = JadwalPelajaran::with(['kelas', 'mataPelajaran', 'guru']);

        // Filter kelas
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Filter hari
        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }

        // Filter guru
        if ($request->filled('guru_id')) {
            $query->where('guru_id', $request->guru_id);
        }

        $jadwalList = $query->orderByFieldHari()->orderBy('jam_mulai')->paginate(20)->withQueryString();

        $kelasList = Kelas::orderBy('nama')->get();
        $mapelList = MataPelajaran::orderBy('nama')->get();
        $guruList  = Guru::orderBy('nama')->get();

        return view('admin.jadwal.index', compact('jadwalList', 'kelasList', 'mapelList', 'guruList'));
    }

    /**
     * Simpan jadwal pelajaran baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id'          => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'guru_id'           => 'required|exists:guru,id',
            'hari'              => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_mulai'         => 'required|date_format:H:i',
            'jam_selesai'       => 'required|date_format:H:i|after:jam_mulai',
            'ruangan'           => 'nullable|string|max:100',
        ]);

        $jadwal = JadwalPelajaran::create($validated);

        return back()->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    /**
     * Update jadwal pelajaran.
     */
    public function update(Request $request, JadwalPelajaran $jadwal)
    {
        $validated = $request->validate([
            'kelas_id'          => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'guru_id'           => 'required|exists:guru,id',
            'hari'              => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_mulai'         => 'required',
            'jam_selesai'       => 'required|after:jam_mulai',
            'ruangan'           => 'nullable|string|max:100',
        ]);

        $jadwal->update($validated);

        return back()->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    /**
     * Hapus jadwal pelajaran.
     */
    public function destroy(JadwalPelajaran $jadwal)
    {
        $jadwal->delete();
        return back()->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }
}

/**
 * Extension macro untuk urutan hari (Senin -> Sabtu)
 */
JadwalPelajaran::macro('scopeOrderByFieldHari', function ($query) {
    return $query->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')");
});
