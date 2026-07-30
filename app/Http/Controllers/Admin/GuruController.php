<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $guru = Guru::with(['user', 'kelasWali.jurusan'])
            ->when($request->search, fn($q) => $q->where('nama', 'like', '%' . $request->search . '%')
                ->orWhere('nip', 'like', '%' . $request->search . '%'))
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        return view('admin.guru.index', compact('guru'));
    }

    public function create()
    {
        return view('admin.guru.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip'      => 'nullable|string|max:20|unique:guru',
            'nama'     => 'required|string|max:100',
            'no_wa'    => 'nullable|string|max:20',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'foto'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Buat akun user untuk guru
        $user = User::create([
            'name'     => $validated['nama'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $user->assignRole('guru');

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('guru/foto', 'public');
        }

        Guru::create([
            'user_id' => $user->id,
            'nip'     => $validated['nip'] ?? null,
            'nama'    => $validated['nama'],
            'no_wa'   => $validated['no_wa'] ?? null,
            'foto'    => $fotoPath,
        ]);

        return redirect()->route('admin.guru.index')
            ->with('success', "Guru {$validated['nama']} berhasil ditambahkan.");
    }

    public function show(Guru $guru)
    {
        $guru->load(['user', 'kelasWali.jurusan']);
        return view('admin.guru.show', compact('guru'));
    }

    public function edit(Guru $guru)
    {
        return view('admin.guru.edit', compact('guru'));
    }

    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nip'    => 'nullable|string|max:20|unique:guru,nip,' . $guru->id,
            'nama'   => 'required|string|max:100',
            'no_wa'  => 'nullable|string|max:20',
            'foto'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('guru/foto', 'public');
        }

        $guru->update($validated);
        $guru->user->update(['name' => $validated['nama']]);

        return redirect()->route('admin.guru.index')
            ->with('success', "Data guru {$guru->nama} berhasil diperbarui.");
    }

    public function destroy(Guru $guru)
    {
        $guru->user->delete(); // Cascade akan hapus guru juga
        return redirect()->route('admin.guru.index')
            ->with('success', 'Data guru berhasil dihapus.');
    }
}
