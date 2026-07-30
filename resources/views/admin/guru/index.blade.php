@extends('layouts.app')

@section('title', 'Data Guru')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-person-badge me-2 text-primary"></i>Data Guru</h1>
        <p class="page-subtitle">Kelola data guru dan wali kelas</p>
    </div>
    <a href="{{ route('admin.guru.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Tambah Guru
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-3 px-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-8">
                <label class="form-label">Cari Guru</label>
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Nama atau NIP..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Guru</th>
                        <th>NIP</th>
                        <th>Email</th>
                        <th>No. WA</th>
                        <th>Wali Kelas</th>
                        <th style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guru as $g)
                    <tr>
                        <td style="color:#6b7280;">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#4f46e5);display:flex;align-items:center;justify-content:center;font-weight:700;color:white;font-size:0.85rem;flex-shrink:0;">
                                    {{ substr($g->nama, 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:0.85rem;">{{ $g->nama }}</div>
                                    <div style="font-size:0.72rem;color:#6b7280;">{{ $g->user->email ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-family:monospace;color:#94a3b8;font-size:0.82rem;">{{ $g->nip ?? '-' }}</td>
                        <td style="color:#94a3b8;font-size:0.82rem;">{{ $g->user->email ?? '-' }}</td>
                        <td style="color:#94a3b8;font-size:0.82rem;">{{ $g->no_wa ?? '-' }}</td>
                        <td>
                            @forelse($g->kelasWali as $kelas)
                                <span class="badge bg-dark border" style="border-color:var(--border-color)!important;font-size:0.7rem;">
                                    {{ $kelas->nama }}
                                </span>
                            @empty
                                <span style="color:#6b7280;font-size:0.78rem;">-</span>
                            @endforelse
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.guru.edit', $g) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.guru.destroy', $g) }}" method="POST"
                                    onsubmit="return confirm('Yakin hapus guru {{ $g->nama }}? Akun loginnya juga akan dihapus.')">
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
                            Belum ada data guru. <a href="{{ route('admin.guru.create') }}">Tambah sekarang</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($guru->hasPages())
    <div class="card-footer px-4 py-3" style="background:transparent;border-top:1px solid var(--border-color);">
        {{ $guru->links() }}
    </div>
    @endif
</div>
@endsection
