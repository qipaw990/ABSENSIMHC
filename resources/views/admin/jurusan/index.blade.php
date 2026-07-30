@extends('layouts.app')

@section('title', 'Jurusan')

@section('content')
<div class="page-header">
    <div><h1 class="page-title"><i class="bi bi-mortarboard me-2 text-primary"></i>Data Jurusan</h1></div>
    <a href="{{ route('admin.jurusan.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Jurusan</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>#</th><th>Kode</th><th>Nama Jurusan</th><th>Jumlah Kelas</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($jurusan as $j)
                <tr>
                    <td style="color:#6b7280;">{{ $loop->iteration }}</td>
                    <td><span class="badge bg-primary" style="font-family:monospace;font-size:0.85rem;">{{ $j->kode }}</span></td>
                    <td><strong>{{ $j->nama }}</strong></td>
                    <td><span class="badge bg-dark border" style="border-color:var(--border-color)!important;">{{ $j->kelas_count }} kelas</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.jurusan.edit', $j) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.jurusan.destroy', $j) }}" method="POST"
                                onsubmit="return confirm('Hapus jurusan {{ $j->nama }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4" style="color:#6b7280;">Belum ada jurusan. <a href="{{ route('admin.jurusan.create') }}">Tambah sekarang</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($jurusan->hasPages())
    <div class="card-footer px-4 py-3" style="background:transparent;border-top:1px solid var(--border-color);">{{ $jurusan->links() }}</div>
    @endif
</div>
@endsection
