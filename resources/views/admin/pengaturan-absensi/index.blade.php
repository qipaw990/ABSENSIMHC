@extends('layouts.app')

@section('title', 'Pengaturan Jam Absensi')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-clock-history me-2 text-primary"></i>Pengaturan Jam Absensi</h1>
        <p class="page-subtitle">Konfigurasi jam masuk dan batas scan per kelas</p>
    </div>
    <a href="{{ route('admin.pengaturan-absensi.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Tambah Pengaturan
    </a>
</div>

@if($kelasliput->count() > 0)
<div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:12px;padding:0.85rem 1.2rem;margin-bottom:1.5rem;font-size:0.82rem;color:#94a3b8;">
    <i class="bi bi-exclamation-triangle me-1 text-warning"></i>
    <strong style="color:#f59e0b;">{{ $kelasliput->count() }} kelas</strong> belum memiliki pengaturan khusus dan akan menggunakan pengaturan global default (masuk: 07:00, tutup: 08:00).
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Kelas</th>
                    <th>Jam Batas Masuk</th>
                    <th>Jam Tutup Absensi</th>
                    <th>Aktif Sabtu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengaturan as $p)
                <tr>
                    <td>
                        @if($p->kelas)
                            <strong>{{ $p->kelas->nama }}</strong>
                            <div style="font-size:0.72rem;color:#6b7280;">{{ $p->kelas->jurusan->nama ?? '' }}</div>
                        @else
                            <span class="badge bg-primary">🌐 Global (semua kelas)</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-family:monospace;font-size:0.9rem;color:#22c55e;">
                            {{ substr($p->jam_masuk_batas, 0, 5) }}
                        </span>
                        <div style="font-size:0.72rem;color:#6b7280;">Setelah jam ini → Terlambat</div>
                    </td>
                    <td>
                        <span style="font-family:monospace;font-size:0.9rem;color:#ef4444;">
                            {{ substr($p->jam_absensi_tutup, 0, 5) }}
                        </span>
                        <div style="font-size:0.72rem;color:#6b7280;">Setelah jam ini → Alpha</div>
                    </td>
                    <td>
                        @if($p->aktif_sabtu)
                            <span class="badge bg-success">Ya</span>
                        @else
                            <span class="badge bg-secondary">Tidak</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.pengaturan-absensi.edit', $p) }}"
                                class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.pengaturan-absensi.destroy', $p) }}" method="POST"
                                onsubmit="return confirm('Hapus pengaturan ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4" style="color:#6b7280;">
                        Belum ada pengaturan. Akan menggunakan jam default (masuk 07:00, tutup 08:00).
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($kelasliput->count() > 0)
<div class="card mt-3">
    <div class="card-header px-4 py-3">
        <i class="bi bi-list me-2 text-warning"></i>Kelas Tanpa Pengaturan Khusus (Pakai Default)
    </div>
    <div class="card-body p-3">
        <div class="d-flex flex-wrap gap-2">
            @foreach($kelasliput as $k)
            <span class="badge bg-dark border" style="border-color:var(--border-color)!important;font-size:0.78rem;">
                {{ $k->nama }}
            </span>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection
