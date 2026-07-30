@extends('layouts.app')

@section('title', 'Dashboard Super Admin')

@push('styles')
<style>
    .tren-chart-container { position: relative; height: 200px; }
    .progress-bar-custom {
        height: 6px; border-radius: 99px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
    }
    .kelas-row { transition: all 0.2s; }
    .kelas-row:hover { background: rgba(99,102,241,0.05) !important; }
</style>
@endpush

@section('content')
<div class="page-header fade-in-up">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Rekap kehadiran hari ini — {{ now()->translatedFormat('l, d F Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-primary px-3 py-2" style="font-size:0.75rem;">
            <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;color:#22c55e;"></i>
            {{ $waSenderAktif }}/{{ $waSenders->count() }} WA Aktif
        </span>
    </div>
</div>

<!-- STAT CARDS ROW 1 -->
<div class="row g-3 mb-4 fade-in-up">
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="--stat-color:#22c55e;">
            <div class="stat-icon" style="background:rgba(34,197,94,0.15);color:#22c55e;">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="stat-value" style="color:#22c55e;">{{ $statsHariIni->get('hadir', 0) }}</div>
            <div class="stat-label">Hadir Tepat Waktu</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="--stat-color:#f59e0b;">
            <div class="stat-icon" style="background:rgba(245,158,11,0.15);color:#f59e0b;">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-value" style="color:#f59e0b;">{{ $statsHariIni->get('terlambat', 0) }}</div>
            <div class="stat-label">Terlambat</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="--stat-color:#3b82f6;">
            <div class="stat-icon" style="background:rgba(59,130,246,0.15);color:#3b82f6;">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div class="stat-value" style="color:#3b82f6;">{{ ($statsHariIni->get('izin', 0) + $statsHariIni->get('sakit', 0)) }}</div>
            <div class="stat-label">Izin / Sakit</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="--stat-color:#ef4444;">
            <div class="stat-icon" style="background:rgba(239,68,68,0.15);color:#ef4444;">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <div class="stat-value" style="color:#ef4444;">{{ $statsHariIni->get('alpha', 0) + $belumAbsen }}</div>
            <div class="stat-label">Alpha / Belum Absen</div>
        </div>
    </div>
</div>

<!-- ROW 2: Chart + WA Status -->
<div class="row g-3 mb-4">
    <!-- Tren Mingguan -->
    <div class="col-12 col-lg-8">
        <div class="card fade-in-up">
            <div class="card-header d-flex justify-content-between align-items-center px-4 py-3">
                <span><i class="bi bi-bar-chart-line me-2 text-primary"></i>Tren Kehadiran 7 Hari Terakhir</span>
            </div>
            <div class="card-body p-4">
                <div class="tren-chart-container">
                    <canvas id="trenChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Summary -->
    <div class="col-12 col-lg-4">
        <div class="card fade-in-up h-100">
            <div class="card-header px-4 py-3">
                <span><i class="bi bi-info-circle me-2 text-primary"></i>Ringkasan Sekolah</span>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: var(--border-color) !important;">
                    <span style="font-size:0.85rem;color:#94a3b8;">Total Siswa</span>
                    <span style="font-weight:700;color:#e2e8f0;">{{ $totalSiswa }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: var(--border-color) !important;">
                    <span style="font-size:0.85rem;color:#94a3b8;">Total Kelas</span>
                    <span style="font-weight:700;color:#e2e8f0;">{{ $totalKelas }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: var(--border-color) !important;">
                    <span style="font-size:0.85rem;color:#94a3b8;">Sudah Absen</span>
                    <span style="font-weight:700;color:#22c55e;">{{ $sudahAbsen }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color: var(--border-color) !important;">
                    <span style="font-size:0.85rem;color:#94a3b8;">Belum Absen</span>
                    <span style="font-weight:700;color:#f59e0b;">{{ $belumAbsen }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span style="font-size:0.85rem;color:#94a3b8;">WA Terkirim Hari Ini</span>
                    <span style="font-weight:700;color:#6366f1;">{{ $waLogHariIni->get('terkirim', 0) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ROW 3: Status WA Sender per Kelas -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card fade-in-up">
            <div class="card-header d-flex justify-content-between align-items-center px-4 py-3">
                <span><i class="bi bi-whatsapp me-2" style="color:#25d366;"></i>Status WA Sender per Kelas</span>
                <a href="{{ route('super-admin.wa-sender.index') }}" class="btn btn-sm btn-outline-primary">Kelola</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Kelas</th>
                                <th>Nama Device</th>
                                <th>Nomor WA</th>
                                <th>Status</th>
                                <th>Terakhir Dicek</th>
                                <th>Hadir Hari Ini</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($waSenders as $sender)
                            <tr>
                                <td><strong>{{ $sender->kelas->nama ?? '-' }}</strong></td>
                                <td style="color:#94a3b8;">{{ $sender->nama_device }}</td>
                                <td style="color:#94a3b8;">{{ $sender->nomor_wa ?? '-' }}</td>
                                <td>
                                    <span class="wa-status-dot {{ $sender->status }}"></span>
                                    <span style="font-size:0.8rem;">{{ ucfirst($sender->status) }}</span>
                                </td>
                                <td style="color:#6b7280;font-size:0.78rem;">
                                    {{ $sender->last_check_at ? $sender->last_check_at->diffForHumans() : '-' }}
                                </td>
                                <td>
                                    @php
                                        $hadirKelas = $statsPerKelas->firstWhere('id', $sender->kelas_id);
                                    @endphp
                                    <span style="color:#22c55e;font-weight:600;">
                                        {{ $hadirKelas?->hadir_count ?? 0 }}
                                    </span>
                                    <span style="color:#6b7280;font-size:0.78rem;">
                                        / {{ $hadirKelas?->total_siswa ?? 0 }} siswa
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4" style="color:#6b7280;">
                                    <i class="bi bi-exclamation-circle me-1"></i>
                                    Belum ada WA Sender. <a href="{{ route('super-admin.wa-sender.create') }}">Tambah sekarang</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ROW 4: Rekap Per Kelas -->
<div class="card fade-in-up">
    <div class="card-header px-4 py-3">
        <span><i class="bi bi-building me-2 text-primary"></i>Rekap Kehadiran Per Kelas Hari Ini</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Kelas</th>
                        <th>Total Siswa</th>
                        <th>Hadir</th>
                        <th>Terlambat</th>
                        <th>Alpha</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statsPerKelas as $kelas)
                    <tr class="kelas-row">
                        <td><strong>{{ $kelas->nama }}</strong></td>
                        <td>{{ $kelas->total_siswa }}</td>
                        <td><span class="badge bg-success">{{ $kelas->hadir_count }}</span></td>
                        <td><span class="badge bg-warning text-dark">{{ $kelas->terlambat_count }}</span></td>
                        <td><span class="badge bg-danger">{{ $kelas->alpha_count }}</span></td>
                        <td style="min-width:120px;">
                            @php
                                $pct = $kelas->total_siswa > 0
                                    ? round((($kelas->hadir_count + $kelas->terlambat_count) / $kelas->total_siswa) * 100)
                                    : 0;
                            @endphp
                            <div style="background:rgba(255,255,255,0.05);border-radius:99px;height:6px;overflow:hidden;">
                                <div class="progress-bar-custom" style="width:{{ $pct }}%;height:100%;"></div>
                            </div>
                            <div style="font-size:0.7rem;color:#6b7280;margin-top:4px;">{{ $pct }}%</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const trenData = @json($trenMingguan);

const ctx = document.getElementById('trenChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: trenData.map(d => d.tanggal),
        datasets: [
            {
                label: 'Hadir',
                data: trenData.map(d => d.hadir),
                backgroundColor: 'rgba(34,197,94,0.7)',
                borderRadius: 6,
            },
            {
                label: 'Terlambat',
                data: trenData.map(d => d.terlambat),
                backgroundColor: 'rgba(245,158,11,0.7)',
                borderRadius: 6,
            },
            {
                label: 'Alpha',
                data: trenData.map(d => d.alpha),
                backgroundColor: 'rgba(239,68,68,0.7)',
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: { color: '#94a3b8', font: { size: 11 } }
            }
        },
        scales: {
            x: { ticks: { color: '#6b7280' }, grid: { color: 'rgba(255,255,255,0.05)' } },
            y: { ticks: { color: '#6b7280' }, grid: { color: 'rgba(255,255,255,0.05)' }, beginAtZero: true }
        }
    }
});
</script>
@endpush
