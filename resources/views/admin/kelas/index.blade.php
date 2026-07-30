@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-building me-2 text-primary"></i>Data Kelas</h1>
        <p class="page-subtitle">Kelola kelas, jurusan, dan wali kelas</p>
    </div>
    <a href="{{ route('admin.kelas.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Tambah Kelas
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Kelas</th>
                        <th>Tingkat</th>
                        <th>Jurusan</th>
                        <th>Wali Kelas</th>
                        <th>Tahun Ajaran</th>
                        <th>Jumlah Siswa</th>
                        <th>WA Sender</th>
                        <th style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelas as $k)
                    <tr>
                        <td style="color:#6b7280;">{{ $loop->iteration }}</td>
                        <td><strong>{{ $k->nama }}</strong></td>
                        <td><span class="badge bg-dark border" style="border-color:var(--border-color)!important;">Kelas {{ $k->tingkat }}</span></td>
                        <td style="color:#94a3b8;font-size:0.82rem;">{{ $k->jurusan->nama ?? '-' }}</td>
                        <td style="font-size:0.82rem;">{{ $k->waliKelas->nama ?? '<span style="color:#6b7280;">Belum ditentukan</span>' }}</td>
                        <td style="color:#94a3b8;font-size:0.82rem;">{{ $k->tahunAjaran->nama ?? '-' }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $k->siswa_count }} siswa</span>
                        </td>
                        <td>
                            @if($k->waSender)
                                <span class="wa-status-dot {{ $k->waSender->status }}"></span>
                                <span style="font-size:0.78rem;">{{ ucfirst($k->waSender->status) }}</span>
                            @else
                                <span style="color:#6b7280;font-size:0.78rem;"><i class="bi bi-exclamation-triangle me-1 text-warning"></i>Belum ada</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.kelas.siswa', $k) }}" class="btn btn-sm btn-outline-info" title="Lihat Siswa">
                                    <i class="bi bi-people"></i>
                                </a>
                                <a href="{{ route('admin.kelas.edit', $k) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.kelas.destroy', $k) }}" method="POST"
                                    onsubmit="return confirm('Yakin hapus kelas {{ $k->nama }}?')">
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
                        <td colspan="9" class="text-center py-5" style="color:#6b7280;">
                            <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                            Belum ada data kelas. <a href="{{ route('admin.kelas.create') }}">Tambah sekarang</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($kelas->hasPages())
    <div class="card-footer px-4 py-3" style="background:transparent;border-top:1px solid var(--border-color);">
        {{ $kelas->links() }}
    </div>
    @endif
</div>
@endsection
