@extends('layouts.app')

@section('title', 'Riwayat Absensi Saya')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-calendar-check me-2 text-primary"></i>Riwayat Absensi</h1>
        <p class="page-subtitle">{{ $siswa->nama }} &mdash; {{ $siswa->kelas->nama ?? '' }}</p>
    </div>
</div>

<!-- Filter bulan -->
<div class="card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
            <div>
                <label class="form-label">Bulan</label>
                <select name="bulan" class="form-select form-select-sm">
                    @foreach(range(1, 12) as $b)
                    <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Tahun</label>
                <select name="tahun" class="form-select form-select-sm">
                    @foreach(range(now()->year - 2, now()->year) as $t)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
        </form>
    </div>
</div>

<!-- Rekap Bulanan -->
<div class="row g-3 mb-4">
    @php
        $statColors = ['hadir'=>'#22c55e','terlambat'=>'#f59e0b','izin'=>'#3b82f6','sakit'=>'#8b5cf6','alpha'=>'#ef4444'];
        $statLabels = ['hadir'=>'Hadir','terlambat'=>'Terlambat','izin'=>'Izin','sakit'=>'Sakit','alpha'=>'Alpha'];
    @endphp
    @foreach($statColors as $key => $color)
    <div class="col-6 col-md">
        <div class="stat-card text-center" style="--stat-color:{{ $color }};">
            <div style="font-size:1.8rem;font-weight:700;color:{{ $color }};">{{ $rekap->get($key, 0) }}</div>
            <div style="font-size:0.75rem;color:#6b7280;">{{ $statLabels[$key] }}</div>
        </div>
    </div>
    @endforeach
</div>

<!-- Tabel Riwayat -->
<div class="card">
    <div class="card-header px-4 py-3">
        <i class="bi bi-calendar3 me-2 text-primary"></i>
        Riwayat {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }} {{ $tahun }}
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Tanggal</th><th>Hari</th><th>Jam Masuk</th><th>Status</th><th>Keterangan</th></tr></thead>
            <tbody>
                @forelse($riwayat as $abs)
                <tr>
                    <td style="font-size:0.85rem;">{{ $abs->tanggal->format('d/m/Y') }}</td>
                    <td style="color:#94a3b8;font-size:0.82rem;">{{ $abs->tanggal->translatedFormat('l') }}</td>
                    <td style="font-family:monospace;color:#94a3b8;">{{ $abs->jam_scan ?? '-' }}</td>
                    <td>{!! $abs->status_badge !!}</td>
                    <td style="font-size:0.82rem;color:#94a3b8;">{{ $abs->keterangan ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4" style="color:#6b7280;">
                        Tidak ada data absensi untuk bulan ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
