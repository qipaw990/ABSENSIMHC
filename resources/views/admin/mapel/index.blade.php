@extends('layouts.app')

@section('title', 'Master Data Mata Pelajaran')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title"><i class="bi bi-book-half me-2 text-primary"></i>Master Mata Pelajaran</h1>
        <p class="page-subtitle">Kelola kurikulum dan daftar mata pelajaran sekolah.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
        <i class="bi bi-plus-lg me-1"></i>Tambah Mapel Baru
    </button>
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
        <form method="GET" action="{{ route('admin.mapel.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari kode atau nama mapel..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="kelompok" class="form-select form-select-sm">
                    <option value="">-- Semua Kelompok Mapel --</option>
                    <option value="normatif" {{ request('kelompok') == 'normatif' ? 'selected' : '' }}>Normatif</option>
                    <option value="adaptif" {{ request('kelompok') == 'adaptif' ? 'selected' : '' }}>Adaptif</option>
                    <option value="produktif" {{ request('kelompok') == 'produktif' ? 'selected' : '' }}>Produktif / Kejuruan</option>
                    <option value="muatan_lokal" {{ request('kelompok') == 'muatan_lokal' ? 'selected' : '' }}>Muatan Lokal</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button>
                @if(request()->hasAny(['search', 'kelompok']))
                <a href="{{ route('admin.mapel.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel List Mapel -->
<div class="card mb-4">
    <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-journal-bookmark me-2 text-primary"></i>Daftar Mata Pelajaran ({{ $mapelList->total() }})</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Nama Mata Pelajaran</th>
                        <th>Kelompok</th>
                        <th>Jadwal Terdaftar</th>
                        <th class="text-end pe-4">Aksi / Kelola</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mapelList as $mapel)
                    <tr>
                        <td style="color:#6b7280;">{{ $loop->iteration + ($mapelList->currentPage() - 1) * $mapelList->perPage() }}</td>
                        <td><code style="font-size:0.85rem;color:#818cf8;">{{ $mapel->kode }}</code></td>
                        <td style="font-weight:600;font-size:0.88rem;color:#f1f5f9;">{{ $mapel->nama }}</td>
                        <td>
                            @php
                                $badgeKelompok = match($mapel->kelompok) {
                                    'normatif'    => 'bg-primary',
                                    'adaptif'     => 'bg-info text-dark',
                                    'produktif'   => 'bg-success',
                                    'muatan_lokal'=> 'bg-warning text-dark',
                                    default       => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeKelompok }}" style="font-size:0.75rem;">{{ $mapel->kelompok_label }}</span>
                        </td>
                        <td style="font-size:0.8rem;color:#94a3b8;">
                            <span class="badge bg-secondary" style="font-size:0.75rem;">{{ $mapel->jadwal_pelajaran_count }} Jadwal</span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end">
                                <!-- Tombol Edit -->
                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $mapel->id }}" title="Edit Mapel">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>

                                <!-- Tombol Hapus -->
                                <form action="{{ route('admin.mapel.destroy', $mapel->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mapel {{ $mapel->nama }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Mapel">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>

                            <!-- Modal Edit Mapel -->
                            <div class="modal fade text-start" id="editModal{{ $mapel->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.mapel.update', $mapel->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Mapel — {{ $mapel->nama }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-semibold">Kode Mapel</label>
                                                    <input type="text" name="kode" class="form-control" value="{{ $mapel->kode }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-semibold">Nama Mata Pelajaran</label>
                                                    <input type="text" name="nama" class="form-control" value="{{ $mapel->nama }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-semibold">Kelompok</label>
                                                    <select name="kelompok" class="form-select" required>
                                                        <option value="normatif" {{ $mapel->kelompok == 'normatif' ? 'selected' : '' }}>Normatif</option>
                                                        <option value="adaptif" {{ $mapel->kelompok == 'adaptif' ? 'selected' : '' }}>Adaptif</option>
                                                        <option value="produktif" {{ $mapel->kelompok == 'produktif' ? 'selected' : '' }}>Produktif / Kejuruan</option>
                                                        <option value="muatan_lokal" {{ $mapel->kelompok == 'muatan_lokal' ? 'selected' : '' }}>Muatan Lokal</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-semibold">Keterangan (Opsional)</label>
                                                    <textarea name="keterangan" class="form-control" rows="2">{{ $mapel->keterangan }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-warning btn-sm">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada mata pelajaran terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($mapelList->hasPages())
        <div class="p-3">
            {{ $mapelList->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Mapel Baru -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.mapel.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-book-plus text-primary me-2"></i>Tambah Mata Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-weight-semibold">Kode Mapel</label>
                        <input type="text" name="kode" class="form-control" placeholder="Contoh: MP-PWPB-X" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-semibold">Nama Mata Pelajaran</label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Pemrograman Web dan Perangkat Bergerak" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-semibold">Kelompok</label>
                        <select name="kelompok" class="form-select" required>
                            <option value="produktif">Produktif / Kejuruan</option>
                            <option value="normatif">Normatif</option>
                            <option value="adaptif">Adaptif</option>
                            <option value="muatan_lokal">Muatan Lokal</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-semibold">Keterangan (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan deskripsi mapel..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Mapel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
