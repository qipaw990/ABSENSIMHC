@extends('layouts.app')

@section('title', 'Detail Absensi')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-calendar-check me-2 text-primary"></i>Detail Absensi</h1>
        <p class="page-subtitle">{{ $absensi->tanggal->translatedFormat('l, d F Y') }}</p>
    </div>
    <a href="{{ route('siswa.riwayat.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row g-4">
    <!-- Info Absensi -->
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-header px-4 py-3">
                <i class="bi bi-info-circle me-2 text-primary"></i>Informasi Absensi
            </div>
            <div class="card-body p-4">
                <!-- Status besar -->
                <div class="text-center mb-4">
                    @php
                        $statusConfig = [
                            'hadir'     => ['icon' => 'bi-check-circle-fill', 'color' => '#22c55e', 'bg' => 'rgba(34,197,94,0.1)',  'label' => 'Hadir'],
                            'terlambat' => ['icon' => 'bi-clock-fill',        'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.1)', 'label' => 'Terlambat'],
                            'izin'      => ['icon' => 'bi-file-earmark-check','color' => '#3b82f6', 'bg' => 'rgba(59,130,246,0.1)', 'label' => 'Izin'],
                            'sakit'     => ['icon' => 'bi-bandaid-fill',      'color' => '#8b5cf6', 'bg' => 'rgba(139,92,246,0.1)', 'label' => 'Sakit'],
                            'alpha'     => ['icon' => 'bi-x-circle-fill',     'color' => '#ef4444', 'bg' => 'rgba(239,68,68,0.1)',  'label' => 'Alpha'],
                        ];
                        $cfg = $statusConfig[$absensi->status] ?? ['icon' => 'bi-dash-circle', 'color' => '#6b7280', 'bg' => 'rgba(107,114,128,0.1)', 'label' => ucfirst($absensi->status)];
                    @endphp
                    <div style="width:80px;height:80px;border-radius:50%;background:{{ $cfg['bg'] }};border:2px solid {{ $cfg['color'] }};display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                        <i class="bi {{ $cfg['icon'] }}" style="font-size:2rem;color:{{ $cfg['color'] }};"></i>
                    </div>
                    <div style="font-size:1.4rem;font-weight:700;color:{{ $cfg['color'] }};">{{ $cfg['label'] }}</div>
                    <div style="font-size:0.82rem;color:#6b7280;margin-top:0.25rem;">Status Kehadiran</div>
                </div>

                <!-- Detail -->
                <table style="width:100%;border-collapse:collapse;">
                    <tr>
                        <td style="padding:0.5rem 0;font-size:0.8rem;color:#6b7280;width:120px;">Tanggal</td>
                        <td style="padding:0.5rem 0;font-size:0.85rem;font-weight:600;color:#e2e8f0;">
                            {{ $absensi->tanggal->translatedFormat('l, d F Y') }}
                        </td>
                    </tr>
                    <tr style="border-top:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:0.5rem 0;font-size:0.8rem;color:#6b7280;">Jam Scan</td>
                        <td style="padding:0.5rem 0;font-size:0.85rem;font-weight:600;color:#e2e8f0;">
                            @if($absensi->jam_scan)
                                <i class="bi bi-clock me-1" style="color:#6366f1;"></i>
                                {{ substr($absensi->jam_scan, 0, 5) }} WIB
                            @else
                                <span style="color:#6b7280;">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr style="border-top:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:0.5rem 0;font-size:0.8rem;color:#6b7280;">Kelas</td>
                        <td style="padding:0.5rem 0;font-size:0.85rem;color:#e2e8f0;">
                            {{ $absensi->kelas->nama ?? '-' }}
                        </td>
                    </tr>
                    @if($absensi->keterangan)
                    <tr style="border-top:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:0.5rem 0;font-size:0.8rem;color:#6b7280;">Keterangan</td>
                        <td style="padding:0.5rem 0;font-size:0.82rem;color:#94a3b8;">
                            {{ $absensi->keterangan }}
                        </td>
                    </tr>
                    @endif
                    <tr style="border-top:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:0.5rem 0;font-size:0.8rem;color:#6b7280;">Notif WA</td>
                        <td style="padding:0.5rem 0;">
                            @if($absensi->notif_terkirim)
                                <span class="badge bg-success">✓ Terkirim ke Ortu</span>
                            @else
                                <span class="badge bg-secondary">Belum terkirim</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Hari Ini dalam Kalender -->
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-header px-4 py-3">
                <i class="bi bi-bar-chart-line me-2 text-primary"></i>Rekap Bulan {{ $absensi->tanggal->translatedFormat('F Y') }}
            </div>
            <div class="card-body p-4">
                @php
                    $rekapBulan = $absensi->siswa->absensi()
                        ->whereYear('tanggal',  $absensi->tanggal->year)
                        ->whereMonth('tanggal', $absensi->tanggal->month)
                        ->selectRaw('status, COUNT(*) as total')
                        ->groupBy('status')
                        ->pluck('total', 'status');
                    $totalHariEfektif = $rekapBulan->sum();
                @endphp

                <div class="row g-3 mb-3">
                    @foreach(['hadir'=>['#22c55e','Hadir'],'terlambat'=>['#f59e0b','Terlambat'],'izin'=>['#3b82f6','Izin'],'sakit'=>['#8b5cf6','Sakit'],'alpha'=>['#ef4444','Alpha']] as $st => [$col, $lbl])
                    <div class="col-6">
                        <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:0.75rem;text-align:center;">
                            <div style="font-size:1.4rem;font-weight:700;color:{{ $col }};">
                                {{ $rekapBulan->get($st, 0) }}
                            </div>
                            <div style="font-size:0.72rem;color:#6b7280;">{{ $lbl }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($totalHariEfektif > 0)
                @php
                    $pct = round((($rekapBulan->get('hadir',0) + $rekapBulan->get('terlambat',0)) / $totalHariEfektif) * 100);
                @endphp
                <div style="margin-top:1rem;">
                    <div class="d-flex justify-content-between" style="font-size:0.78rem;margin-bottom:0.4rem;">
                        <span style="color:#94a3b8;">Kehadiran bulan ini</span>
                        <strong style="color:{{ $pct >= 80 ? '#22c55e' : ($pct >= 60 ? '#f59e0b' : '#ef4444') }};">{{ $pct }}%</strong>
                    </div>
                    <div style="background:rgba(255,255,255,0.08);border-radius:6px;height:8px;overflow:hidden;">
                        <div style="height:100%;width:{{ $pct }}%;background:{{ $pct >= 80 ? '#22c55e' : ($pct >= 60 ? '#f59e0b' : '#ef4444') }};border-radius:6px;transition:width 0.5s;"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
