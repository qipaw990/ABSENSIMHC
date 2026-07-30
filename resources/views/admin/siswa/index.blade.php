@extends('layouts.app')

@section('title', 'Daftar Siswa')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-people me-2 text-primary"></i>Data Siswa</h1>
        <p class="page-subtitle">Kelola data dan QR Code siswa</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.siswa.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Tambah Siswa
        </a>
        <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-file-earmark-excel me-1"></i>Import Excel
        </button>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body py-3 px-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Cari Siswa</label>
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Nama atau NIS..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Filter Kelas</label>
                <select name="kelas_id" class="form-select form-select-sm">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                        {{ $k->nama }} ({{ $k->jurusan->kode ?? '' }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Siswa -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Nama Siswa</th>
                        <th>NIS</th>
                        <th>Kelas</th>
                        <th>No. WA Ortu</th>
                        <th>Status QR</th>
                        <th style="width:160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswa as $s)
                    <tr>
                        <td style="color:#6b7280;">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $s->foto_url }}" alt="{{ $s->nama }}"
                                    style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:1px solid var(--border-color);">
                                <div>
                                    <div style="font-weight:600;font-size:0.85rem;">{{ $s->nama }}</div>
                                    <div style="font-size:0.72rem;color:#6b7280;">NISN: {{ $s->nisn ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-family:monospace;color:#94a3b8;">{{ $s->nis }}</td>
                        <td>
                            <span class="badge bg-dark border" style="border-color:var(--border-color)!important;font-size:0.72rem;">
                                {{ $s->kelas->nama ?? '-' }}
                            </span>
                        </td>
                        <td style="color:#94a3b8;font-size:0.82rem;">{{ $s->no_wa_ortu ?? '-' }}</td>
                        <td>
                            @if($s->qr_is_active)
                                <span class="badge bg-success"><i class="bi bi-qr-code me-1"></i>Aktif</span>
                            @else
                                <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.siswa.show', $s) }}"
                                    class="btn btn-sm btn-outline-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.siswa.kartu-qr', $s) }}"
                                    class="btn btn-sm btn-outline-primary" title="Cetak Kartu QR" target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <a href="{{ route('admin.siswa.edit', $s) }}"
                                    class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.siswa.destroy', $s) }}" method="POST"
                                    onsubmit="return confirm('Yakin hapus siswa {{ $s->nama }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color:#6b7280;">
                            <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                            Tidak ada data siswa.
                            <a href="{{ route('admin.siswa.create') }}">Tambah siswa</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($siswa->hasPages())
    <div class="card-footer px-4 py-3" style="background:transparent;border-top:1px solid var(--border-color);">
        {{ $siswa->links() }}
    </div>
    @endif
</div>

{{-- Import Error Details --}}
@if(session('import_errors'))
<div class="alert alert-warning mt-3" style="font-size:0.85rem;">
    <strong><i class="bi bi-exclamation-triangle me-1"></i>Detail Baris yang Dilewati:</strong>
    <ul class="mb-0 mt-2">
        @foreach(session('import_errors') as $err)
        <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background:var(--bg-card);border:1px solid var(--border-color);">
            <div class="modal-header" style="border-bottom:1px solid var(--border-color);">
                <h5 class="modal-title"><i class="bi bi-file-earmark-excel me-2"></i>Import Siswa dari Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:0.85rem;color:#94a3b8;">
                    Download template Excel terlebih dahulu, isi data siswa, kemudian upload kembali.
                </p>
                <a href="{{ route('admin.siswa.template-import') }}" class="btn btn-sm btn-outline-success mb-3">
                    <i class="bi bi-download me-1"></i>Download Template Excel
                </a>
                <form action="{{ route('admin.siswa.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Upload File Excel</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-upload me-1"></i>Import Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
