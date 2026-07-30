@extends('layouts.app')

@section('title', 'Daftar Siswa — ' . $kelas->nama)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-people me-2 text-primary"></i>Siswa Kelas {{ $kelas->nama }}</h1>
        <p class="page-subtitle">{{ $kelas->jurusan->nama ?? '' }} &bull; {{ $siswa->total() }} siswa</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.siswa.cetak-batch', $kelas->id) }}" class="btn btn-outline-danger btn-sm" target="_blank">
            <i class="bi bi-file-pdf me-1"></i>Cetak Semua Kartu
        </a>
        <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Siswa</th>
                    <th>NIS</th>
                    <th>No WA Ortu</th>
                    <th>Status QR</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($siswa as $s)
                <tr>
                    <td style="color:#6b7280;">{{ $loop->iteration }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $s->foto_url }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:1px solid rgba(99,102,241,0.3);">
                            <span style="font-weight:600;font-size:0.85rem;">{{ $s->nama }}</span>
                        </div>
                    </td>
                    <td style="font-family:monospace;color:#94a3b8;font-size:0.82rem;">{{ $s->nis }}</td>
                    <td style="font-size:0.82rem;color:#94a3b8;">{{ $s->no_wa_ortu ?? '-' }}</td>
                    <td>
                        @if($s->qr_is_active)
                            <span class="badge bg-success">✓ Aktif</span>
                        @else
                            <span class="badge bg-danger">✗ Tidak Aktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.siswa.show', $s) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.siswa.kartu-qr', $s) }}" class="btn btn-sm btn-outline-secondary" title="Kartu QR" target="_blank">
                                <i class="bi bi-qr-code"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4" style="color:#6b7280;">
                        Belum ada siswa di kelas ini.
                        <a href="{{ route('admin.siswa.create') }}">Tambah siswa</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($siswa->hasPages())
    <div class="card-footer px-4 py-3" style="background:transparent;border-top:1px solid var(--border-color);">
        {{ $siswa->links() }}
    </div>
    @endif
</div>
@endsection
