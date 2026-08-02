@extends('layouts.app')

@section('title', 'Penilaian & Nilai Harian Siswa')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title"><i class="bi bi-journal-check me-2 text-primary"></i>Penilaian & Nilai Harian</h1>
        <p class="page-subtitle">Kelola tugas, bab materi pembelajaran, ulangan harian, dan input nilai siswa.</p>
    </div>
    <a href="{{ route('guru.penilaian.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Tambah Tugas / Bab Baru
    </a>
</div>

<!-- Alert Notifikasi -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Filter & Search Bar -->
<div class="card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" action="{{ route('guru.penilaian.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari Mapel, Bab, atau Judul Tugas..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="kelas_id" class="form-select form-select-sm">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                        {{ $k->nama }} ({{ $k->jurusan->nama ?? '-' }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button>
                @if(request()->hasAny(['search', 'kelas_id']))
                <a href="{{ route('guru.penilaian.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel Daftar Penilaian / Tugas -->
<div class="card mb-4">
    <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-task me-2 text-primary"></i>Daftar Tugas & Penilaian ({{ $tugasList->total() }})</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Mata Pelajaran & Bab</th>
                        <th>Judul Tugas</th>
                        <th>Kelas</th>
                        <th>Jenis</th>
                        <th>Tanggal</th>
                        <th>Siswa Dinilai</th>
                        <th class="text-end pe-4">Aksi / Kelola</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tugasList as $tugas)
                    <tr>
                        <td style="color:#6b7280;">{{ $loop->iteration + ($tugasList->currentPage() - 1) * $tugasList->perPage() }}</td>
                        <td>
                            <div style="font-weight:600;font-size:0.88rem;color:#f1f5f9;">{{ $tugas->mata_pelajaran }}</div>
                            <div style="font-size:0.75rem;color:#94a3b8;"><i class="bi bi-book me-1"></i>{{ $tugas->bab_materi }}</div>
                        </td>
                        <td style="font-size:0.85rem;color:#e2e8f0;font-weight:500;">{{ $tugas->judul_tugas }}</td>
                        <td><span class="badge bg-secondary" style="font-size:0.75rem;">{{ $tugas->kelas->nama ?? '-' }}</span></td>
                        <td>
                            @php
                                $badgeJenis = match($tugas->jenis) {
                                    'tugas'     => 'bg-primary',
                                    'uh'        => 'bg-warning text-dark',
                                    'uts'       => 'bg-info text-dark',
                                    'uas'       => 'bg-danger',
                                    'praktikum' => 'bg-success',
                                    default     => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeJenis }}" style="font-size:0.75rem;">{{ $tugas->jenis_label }}</span>
                        </td>
                        <td style="font-size:0.8rem;color:#94a3b8;">{{ $tugas->tanggal ? $tugas->tanggal->format('d/m/Y') : '-' }}</td>
                        <td style="font-size:0.82rem;">
                            @php
                                $totalNilai = $tugas->nilaiSiswa->count();
                                $sudahDinilai = $tugas->nilaiSiswa->where('nilai', '>', 0)->count();
                            @endphp
                            <span class="badge bg-success" style="font-size:0.75rem;">{{ $sudahDinilai }} / {{ $totalNilai }} Siswa</span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end">
                                <!-- Input / Edit Nilai Button -->
                                <a href="{{ route('guru.penilaian.show', $tugas->id) }}" class="btn btn-sm btn-outline-success" title="Input & Edit Nilai Siswa">
                                    <i class="bi bi-pencil-square"></i> Input Nilai
                                </a>

                                <!-- Edit Rincian Button -->
                                <a href="{{ route('guru.penilaian.edit', $tugas->id) }}" class="btn btn-sm btn-outline-warning" title="Edit Rincian Tugas">
                                    <i class="bi bi-gear"></i> Edit
                                </a>

                                <!-- Hapus Button -->
                                <form action="{{ route('guru.penilaian.destroy', $tugas->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas {{ $tugas->judul_tugas }} dan seluruh nilainya?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Tugas">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada tugas atau materi penilaian yang dibuat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tugasList->hasPages())
        <div class="p-3">
            {{ $tugasList->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
