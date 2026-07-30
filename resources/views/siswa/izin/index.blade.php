@extends('layouts.app')

@section('title', 'Pengajuan Izin Saya')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Pengajuan Izin</h1>
        <p class="page-subtitle">Daftar pengajuan izin dan sakit Anda</p>
    </div>
    <a href="{{ route('siswa.izin.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Ajukan Izin/Sakit
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr><th>Tanggal Pengajuan</th><th>Jenis</th><th>Periode</th><th>Keterangan</th><th>Status</th><th>Lampiran</th></tr>
            </thead>
            <tbody>
                @forelse($izinList as $izin)
                <tr>
                    <td style="font-size:0.82rem;color:#94a3b8;">{{ $izin->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge {{ $izin->jenis === 'izin' ? 'bg-info' : 'bg-warning text-dark' }}">{{ ucfirst($izin->jenis) }}</span>
                    </td>
                    <td style="font-size:0.82rem;">
                        {{ $izin->tanggal_mulai->format('d/m/Y') }}
                        @if($izin->tanggal_mulai->ne($izin->tanggal_selesai))
                            — {{ $izin->tanggal_selesai->format('d/m/Y') }}
                        @endif
                    </td>
                    <td style="font-size:0.82rem;color:#94a3b8;">{{ Str::limit($izin->keterangan, 60) }}</td>
                    <td>
                        @if($izin->status === 'pending')
                            <span class="badge bg-warning text-dark">⏳ Menunggu</span>
                        @elseif($izin->status === 'disetujui')
                            <span class="badge bg-success">✅ Disetujui</span>
                        @else
                            <span class="badge bg-danger">❌ Ditolak</span>
                            @if($izin->catatan_penolakan)
                                <div style="font-size:0.72rem;color:#f87171;margin-top:2px;">{{ $izin->catatan_penolakan }}</div>
                            @endif
                        @endif
                    </td>
                    <td>
                        @if($izin->lampiran)
                        <a href="{{ asset('storage/'.$izin->lampiran) }}" target="_blank" class="btn btn-sm btn-outline-info">
                            <i class="bi bi-paperclip"></i>
                        </a>
                        @else
                        <span style="color:#6b7280;font-size:0.78rem;">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5" style="color:#6b7280;">
                        <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                        Belum ada pengajuan izin. <a href="{{ route('siswa.izin.create') }}">Ajukan sekarang</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($izinList->hasPages())
    <div class="card-footer px-4 py-3" style="background:transparent;border-top:1px solid var(--border-color);">
        {{ $izinList->links() }}
    </div>
    @endif
</div>
@endsection
