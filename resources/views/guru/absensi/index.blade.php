@extends('layouts.app')

@section('title', 'Scan QR Absensi — Pilih Kelas')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-qr-code-scan me-2 text-primary"></i>Absensi QR</h1>
        <p class="page-subtitle">Pilih kelas untuk memulai scan absensi</p>
    </div>
</div>

@if($kelasList->isEmpty())
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-exclamation-circle" style="font-size:3rem;color:#f59e0b;display:block;margin-bottom:1rem;"></i>
        <h5 style="color:#e2e8f0;">Belum ada kelas yang dapat dikelola</h5>
        <p style="color:#6b7280;font-size:0.85rem;">Hubungi admin untuk menetapkan Anda sebagai wali kelas.</p>
    </div>
</div>
@else
<div class="row g-3">
    @foreach($kelasList as $kelas)
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100" style="cursor:pointer;transition:all 0.25s ease;"
            onclick="window.location='{{ route('guru.absensi.scan', $kelas->id) }}'">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div style="width:48px;height:48px;background:linear-gradient(135deg,#6366f1,#4f46e5);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                        📋
                    </div>
                    @if($kelas->waSender)
                        <span class="badge" style="background:rgba(34,197,94,0.15);color:#22c55e;border:1px solid rgba(34,197,94,0.3);">
                            <i class="bi bi-whatsapp me-1"></i>WA Aktif
                        </span>
                    @else
                        <span class="badge" style="background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3);">
                            <i class="bi bi-exclamation-triangle me-1"></i>No WA
                        </span>
                    @endif
                </div>

                <h5 style="font-weight:700;font-size:1.1rem;color:#f1f5f9;margin-bottom:0.25rem;">{{ $kelas->nama }}</h5>
                <p style="font-size:0.8rem;color:#6b7280;margin-bottom:1rem;">
                    {{ $kelas->jurusan->nama ?? '-' }} &bull; {{ $kelas->siswa()->count() }} siswa
                </p>

                @php
                    $hadirHariIni = $kelas->absensi()->whereDate('tanggal', today())->count();
                    $totalSiswa   = $kelas->siswa()->count();
                    $pct = $totalSiswa > 0 ? round(($hadirHariIni / $totalSiswa) * 100) : 0;
                @endphp

                <div style="margin-bottom:0.5rem;">
                    <div class="d-flex justify-content-between" style="font-size:0.75rem;margin-bottom:4px;">
                        <span style="color:#6b7280;">Hadir hari ini</span>
                        <span style="color:#e2e8f0;font-weight:600;">{{ $hadirHariIni }}/{{ $totalSiswa }}</span>
                    </div>
                    <div style="background:rgba(255,255,255,0.05);border-radius:99px;height:5px;overflow:hidden;">
                        <div style="width:{{ $pct }}%;height:100%;background:linear-gradient(90deg,#6366f1,#8b5cf6);border-radius:99px;transition:width 0.5s;"></div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('guru.absensi.scan', $kelas->id) }}"
                        class="btn btn-primary btn-sm flex-fill" onclick="event.stopPropagation()">
                        <i class="bi bi-qr-code-scan me-1"></i>Mulai Scan
                    </a>
                    <a href="{{ route('guru.absensi.rekap', $kelas->id) }}"
                        class="btn btn-outline-secondary btn-sm" onclick="event.stopPropagation()" title="Lihat Rekap">
                        <i class="bi bi-list-check"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
