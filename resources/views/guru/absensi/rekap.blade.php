@extends('layouts.app')

@section('title', 'Rekap Absensi — ' . $kelas->nama)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-list-check me-2 text-primary"></i>Rekap Absensi</h1>
        <p class="page-subtitle">{{ $kelas->nama }} &mdash; {{ $tanggal->translatedFormat('l, d F Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('guru.absensi.scan', $kelas->id) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-qr-code-scan me-1"></i>Kembali Scan
        </a>
    </div>
</div>

<!-- Filter tanggal -->
<div class="card mb-3">
    <div class="card-body py-3 px-4">
        <form method="GET" class="d-flex gap-2 align-items-end">
            <div>
                <label class="form-label mb-1">Tanggal</label>
                <input type="date" name="tanggal" class="form-control form-control-sm"
                    value="{{ $tanggal->format('Y-m-d') }}">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
        </form>
    </div>
</div>

<!-- Stat bar -->
<div class="row g-2 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div style="font-size:1.6rem;font-weight:700;color:#22c55e;">{{ $stats['hadir'] }}</div>
            <div style="font-size:0.72rem;color:#6b7280;">Hadir</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div style="font-size:1.6rem;font-weight:700;color:#f59e0b;">{{ $stats['terlambat'] }}</div>
            <div style="font-size:0.72rem;color:#6b7280;">Terlambat</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div style="font-size:1.6rem;font-weight:700;color:#3b82f6;">{{ $stats['izin'] + $stats['sakit'] }}</div>
            <div style="font-size:0.72rem;color:#6b7280;">Izin/Sakit</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div style="font-size:1.6rem;font-weight:700;color:#ef4444;">{{ $stats['alpha'] }}</div>
            <div style="font-size:0.72rem;color:#6b7280;">Alpha</div>
        </div>
    </div>
</div>

<!-- Daftar yang sudah absen -->
<div class="card mb-3">
    <div class="card-header px-4 py-3">
        <i class="bi bi-check-circle me-2 text-success"></i>Sudah Absen ({{ $absensiList->count() }})
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>#</th><th>Siswa</th><th>Jam Scan</th><th>Status</th><th>Keterangan</th><th>Ubah Status</th></tr></thead>
                <tbody>
                    @forelse($absensiList as $abs)
                    <tr>
                        <td style="color:#6b7280;">{{ $loop->iteration }}</td>
                        <td>
                            <div style="font-weight:600;font-size:0.85rem;">{{ $abs->siswa->nama }}</div>
                            <div style="font-size:0.72rem;color:#6b7280;">{{ $abs->siswa->nis }}</div>
                        </td>
                        <td style="font-family:monospace;color:#94a3b8;">{{ $abs->jam_scan ?? '-' }}</td>
                        <td>{!! $abs->status_badge !!}</td>
                        <td style="font-size:0.82rem;color:#94a3b8;">{{ $abs->keterangan ?? '-' }}</td>
                        <td>
                            <form action="{{ route('guru.absensi.update', $abs) }}" method="POST" class="d-flex gap-1">
                                @csrf @method('PATCH')
                                <select name="status" class="form-select form-select-sm" style="width:auto;">
                                    @foreach(['hadir','terlambat','izin','sakit','alpha'] as $st)
                                    <option value="{{ $st }}" {{ $abs->status === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-check-lg"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-3" style="color:#6b7280;">Belum ada yang absen.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Belum absen -->
@if($belumAbsen->count() > 0)
<div class="card">
    <div class="card-header px-4 py-3">
        <i class="bi bi-person-x me-2 text-warning"></i>Belum Absen ({{ $belumAbsen->count() }})
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>#</th><th>Nama</th><th>NIS</th><th>Input Manual</th></tr></thead>
            <tbody>
                @foreach($belumAbsen as $siswa)
                <tr>
                    <td style="color:#6b7280;">{{ $loop->iteration }}</td>
                    <td style="font-weight:600;font-size:0.85rem;">{{ $siswa->nama }}</td>
                    <td style="font-family:monospace;color:#94a3b8;">{{ $siswa->nis }}</td>
                    <td>
                        <form action="{{ route('guru.absensi.manual') }}" method="POST" class="d-flex gap-1">
                            @csrf
                            <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">
                            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                            <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
                            <select name="status" class="form-select form-select-sm" style="width:auto;">
                                <option value="hadir">Hadir</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="alpha" selected>Alpha</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
