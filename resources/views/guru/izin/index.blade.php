@extends('layouts.app')

@section('title', 'Pengajuan Izin & Sakit')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-file-earmark-check me-2 text-primary"></i>Izin &amp; Sakit</h1>
        <p class="page-subtitle">Pengajuan yang perlu diproses</p>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body py-3 px-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Jenis</label>
                <select name="jenis" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="izin" {{ request('jenis') == 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ request('jenis') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button>
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
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Jenis</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                        <th>Lampiran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($izinList as $izin)
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:0.85rem;">{{ $izin->siswa->nama }}</div>
                            <div style="font-size:0.72rem;color:#6b7280;">{{ $izin->siswa->nis }}</div>
                        </td>
                        <td style="font-size:0.82rem;">{{ $izin->siswa->kelas->nama ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $izin->jenis === 'izin' ? 'bg-info' : 'bg-warning text-dark' }}">
                                {{ ucfirst($izin->jenis) }}
                            </span>
                        </td>
                        <td style="font-size:0.82rem;">
                            {{ $izin->tanggal_mulai->format('d/m/Y') }}
                            @if($izin->tanggal_mulai->ne($izin->tanggal_selesai))
                                — {{ $izin->tanggal_selesai->format('d/m/Y') }}
                            @endif
                        </td>
                        <td style="font-size:0.82rem;max-width:200px;color:#94a3b8;">
                            {{ Str::limit($izin->keterangan, 60) }}
                        </td>
                        <td>
                            @if($izin->status === 'pending')
                                <span class="badge bg-warning text-dark">⏳ Menunggu</span>
                            @elseif($izin->status === 'disetujui')
                                <span class="badge bg-success">✅ Disetujui</span>
                            @else
                                <span class="badge bg-danger">❌ Ditolak</span>
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
                        <td>
                            @if($izin->isPending())
                            <div class="d-flex gap-1">
                                <form action="{{ route('guru.izin.setujui', $izin) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Setujui">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <button class="btn btn-sm btn-outline-danger" title="Tolak"
                                    data-bs-toggle="modal" data-bs-target="#tolakModal{{ $izin->id }}">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <!-- Modal Tolak -->
                            <div class="modal fade" id="tolakModal{{ $izin->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content" style="background:var(--bg-card);border:1px solid var(--border-color);">
                                        <div class="modal-header" style="border-color:var(--border-color);">
                                            <h5 class="modal-title">Tolak Pengajuan</h5>
                                            <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('guru.izin.tolak', $izin) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <div class="modal-body">
                                                <p style="font-size:0.85rem;color:#94a3b8;">
                                                    Tolak pengajuan <strong style="color:#e2e8f0;">{{ ucfirst($izin->jenis) }}</strong>
                                                    dari <strong style="color:#e2e8f0;">{{ $izin->siswa->nama }}</strong>?
                                                </p>
                                                <label class="form-label">Alasan Penolakan (opsional)</label>
                                                <textarea name="catatan_penolakan" class="form-control" rows="3" placeholder="Tambahkan alasan penolakan..."></textarea>
                                            </div>
                                            <div class="modal-footer" style="border-color:var(--border-color);">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger btn-sm">Tolak Pengajuan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @else
                                <span style="font-size:0.75rem;color:#6b7280;">Sudah diproses</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5" style="color:#6b7280;">
                            <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                            Tidak ada pengajuan izin/sakit.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($izinList->hasPages())
    <div class="card-footer px-4 py-3" style="background:transparent;border-top:1px solid var(--border-color);">
        {{ $izinList->links() }}
    </div>
    @endif
</div>
@endsection
