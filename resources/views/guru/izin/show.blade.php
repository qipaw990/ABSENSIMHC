@extends('layouts.app')

@section('title', 'Detail Pengajuan Izin')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-file-earmark-check me-2 text-primary"></i>Detail Pengajuan Izin</h1>
        <p class="page-subtitle">Diajukan {{ $izinSakit->created_at->translatedFormat('d F Y, H:i') }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($izinSakit->isPending())
            <form action="{{ route('guru.izin.setujui', $izinSakit) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Setujui pengajuan ini?')">
                    <i class="bi bi-check-lg me-1"></i>Setujui
                </button>
            </form>
            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalTolak">
                <i class="bi bi-x-lg me-1"></i>Tolak
            </button>
        @endif
        <a href="{{ route('guru.izin.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Info Utama -->
    <div class="col-12 col-md-7">
        <div class="card">
            <div class="card-body p-4">
                <!-- Status Badge -->
                <div class="d-flex align-items-center gap-3 mb-4 pb-4" style="border-bottom:1px solid var(--border-color);">
                    <div style="width:56px;height:56px;border-radius:12px;
                        background:{{ $izinSakit->status === 'disetujui' ? 'rgba(34,197,94,0.1)' : ($izinSakit->status === 'ditolak' ? 'rgba(239,68,68,0.1)' : 'rgba(245,158,11,0.1)') }};
                        display:flex;align-items:center;justify-content:center;font-size:1.6rem;">
                        @if($izinSakit->status === 'disetujui') ✅
                        @elseif($izinSakit->status === 'ditolak') ❌
                        @else ⏳
                        @endif
                    </div>
                    <div>
                        <div style="font-size:1rem;font-weight:700;color:#e2e8f0;">
                            {!! $izinSakit->status_badge !!}
                            <span class="ms-2 badge {{ $izinSakit->jenis === 'izin' ? 'bg-info' : 'bg-warning text-dark' }}">
                                {{ ucfirst($izinSakit->jenis) }}
                            </span>
                        </div>
                        <div style="font-size:0.78rem;color:#6b7280;margin-top:0.25rem;">
                            {{ $izinSakit->jumlah_hari }} hari ({{ $izinSakit->tanggal_mulai->format('d/m/Y') }}
                            @if(!$izinSakit->tanggal_mulai->eq($izinSakit->tanggal_selesai))
                                — {{ $izinSakit->tanggal_selesai->format('d/m/Y') }}
                            @endif)
                        </div>
                    </div>
                </div>

                <!-- Detail Siswa -->
                <h6 style="font-size:0.75rem;text-transform:uppercase;color:#6b7280;letter-spacing:0.08em;margin-bottom:1rem;">
                    Data Siswa
                </h6>
                <div class="d-flex align-items-center gap-3 mb-4">
                    <img src="{{ $izinSakit->siswa->foto_url }}"
                         style="width:50px;height:50px;border-radius:50%;object-fit:cover;border:2px solid rgba(99,102,241,0.3);">
                    <div>
                        <div style="font-weight:700;color:#e2e8f0;">{{ $izinSakit->siswa->nama }}</div>
                        <div style="font-size:0.78rem;color:#6b7280;">
                            NIS: {{ $izinSakit->siswa->nis }} &bull;
                            {{ $izinSakit->siswa->kelas->nama ?? '-' }}
                        </div>
                    </div>
                </div>

                <!-- Keterangan -->
                <h6 style="font-size:0.75rem;text-transform:uppercase;color:#6b7280;letter-spacing:0.08em;margin-bottom:0.75rem;">
                    Keterangan Siswa
                </h6>
                <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:1rem;font-size:0.88rem;color:#94a3b8;line-height:1.6;margin-bottom:1.5rem;">
                    {{ $izinSakit->keterangan }}
                </div>

                <!-- Lampiran -->
                @if($izinSakit->lampiran)
                <h6 style="font-size:0.75rem;text-transform:uppercase;color:#6b7280;letter-spacing:0.08em;margin-bottom:0.75rem;">
                    Lampiran
                </h6>
                <a href="{{ asset('storage/'.$izinSakit->lampiran) }}" target="_blank"
                   class="btn btn-outline-info btn-sm">
                    <i class="bi bi-paperclip me-1"></i>Lihat Lampiran
                </a>
                @endif

                <!-- Catatan penolakan -->
                @if($izinSakit->status === 'ditolak' && $izinSakit->catatan_penolakan)
                <div class="mt-3" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:10px;padding:0.85rem 1rem;">
                    <div style="font-size:0.75rem;color:#f87171;font-weight:600;margin-bottom:0.3rem;">
                        <i class="bi bi-x-circle me-1"></i>Alasan Penolakan
                    </div>
                    <div style="font-size:0.85rem;color:#94a3b8;">{{ $izinSakit->catatan_penolakan }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar info -->
    <div class="col-12 col-md-5">
        <div class="card">
            <div class="card-header px-4 py-3"><i class="bi bi-clock-history me-2 text-primary"></i>Timeline</div>
            <div class="card-body p-4">
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex gap-3">
                        <div style="width:28px;height:28px;border-radius:50%;background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi bi-send" style="font-size:0.7rem;color:#6366f1;"></i>
                        </div>
                        <div>
                            <div style="font-size:0.82rem;font-weight:600;color:#e2e8f0;">Pengajuan Dikirim</div>
                            <div style="font-size:0.72rem;color:#6b7280;">{{ $izinSakit->created_at->translatedFormat('d F Y, H:i') }}</div>
                        </div>
                    </div>

                    @if($izinSakit->status !== 'pending')
                    <div class="d-flex gap-3">
                        <div style="width:28px;height:28px;border-radius:50%;background:{{ $izinSakit->isDisetujui() ? 'rgba(34,197,94,0.15)' : 'rgba(239,68,68,0.15)' }};border:1px solid {{ $izinSakit->isDisetujui() ? 'rgba(34,197,94,0.3)' : 'rgba(239,68,68,0.3)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi {{ $izinSakit->isDisetujui() ? 'bi-check' : 'bi-x' }}" style="font-size:0.8rem;color:{{ $izinSakit->isDisetujui() ? '#22c55e' : '#ef4444' }};"></i>
                        </div>
                        <div>
                            <div style="font-size:0.82rem;font-weight:600;color:#e2e8f0;">
                                {{ $izinSakit->isDisetujui() ? 'Disetujui' : 'Ditolak' }}
                            </div>
                            <div style="font-size:0.72rem;color:#6b7280;">{{ $izinSakit->updated_at->translatedFormat('d F Y, H:i') }}</div>
                            @if($izinSakit->disetujuiOleh)
                            <div style="font-size:0.72rem;color:#6b7280;">oleh {{ $izinSakit->disetujuiOleh->name }}</div>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="d-flex gap-3" style="opacity:0.4;">
                        <div style="width:28px;height:28px;border-radius:50%;border:1px dashed rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi bi-hourglass" style="font-size:0.7rem;color:#6b7280;"></i>
                        </div>
                        <div>
                            <div style="font-size:0.82rem;color:#6b7280;">Menunggu keputusan guru</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tolak -->
@if($izinSakit->isPending())
<div class="modal fade" id="modalTolak" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--bg-card);border:1px solid var(--border-color);">
            <div class="modal-header" style="border-color:var(--border-color);">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2 text-danger"></i>Tolak Pengajuan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('guru.izin.tolak', $izinSakit) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <p style="font-size:0.85rem;color:#94a3b8;">
                        Masukkan alasan penolakan. Siswa akan diberitahu melalui sistem.
                    </p>
                    <textarea name="catatan_penolakan" class="form-control" rows="3"
                        placeholder="Contoh: Surat dokter tidak dilampirkan..." required></textarea>
                </div>
                <div class="modal-footer" style="border-color:var(--border-color);">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-x-circle me-1"></i>Tolak Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
