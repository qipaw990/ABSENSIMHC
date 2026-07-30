@extends('layouts.app')

@section('title', 'Tahun Ajaran')

@section('content')
<div class="page-header">
    <div><h1 class="page-title"><i class="bi bi-calendar3 me-2 text-primary"></i>Tahun Ajaran</h1></div>
    <a href="{{ route('admin.tahun-ajaran.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr><th>#</th><th>Nama</th><th>Semester</th><th>Status</th><th>Jumlah Kelas</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($tahunAjaran as $ta)
                <tr>
                    <td style="color:#6b7280;">{{ $loop->iteration }}</td>
                    <td><strong>{{ $ta->nama }}</strong></td>
                    <td><span class="badge {{ $ta->semester === 'ganjil' ? 'bg-info' : 'bg-warning text-dark' }}">{{ ucfirst($ta->semester) }}</span></td>
                    <td>
                        @if($ta->is_aktif)
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                        @else
                            <span class="badge bg-secondary">Tidak Aktif</span>
                        @endif
                    </td>
                    <td><span class="badge bg-primary">{{ $ta->kelas_count }} kelas</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            @if(!$ta->is_aktif)
                            <form action="{{ route('admin.tahun-ajaran.aktifkan', $ta) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Aktifkan">
                                    <i class="bi bi-toggle-on"></i>
                                </button>
                            </form>
                            @endif
                            <a href="{{ route('admin.tahun-ajaran.edit', $ta) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.tahun-ajaran.destroy', $ta) }}" method="POST"
                                onsubmit="return confirm('Hapus tahun ajaran {{ $ta->nama }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4" style="color:#6b7280;">Belum ada tahun ajaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
