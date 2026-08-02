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

<!-- Alert Pesan -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

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
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Siswa</th>
                        <th>Jam Scan</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th class="text-end pe-4">Aksi / Kelola</th>
                    </tr>
                </thead>
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
                        <td class="text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end">
                                <!-- Tombol Edit Modal -->
                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $abs->id }}" title="Edit Data Absensi">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>

                                <!-- Tombol Hapus -->
                                <form action="{{ route('guru.absensi.destroy', $abs->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data absensi {{ $abs->siswa->nama }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Data Absensi">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>

                            <!-- Modal Edit Absensi -->
                            <div class="modal fade text-start" id="editModal{{ $abs->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $abs->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('guru.absensi.update', $abs->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editModalLabel{{ $abs->id }}">
                                                    <i class="bi bi-pencil-square me-2 text-warning"></i>Edit Absensi — {{ $abs->siswa->nama }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-semibold">Status Kehadiran</label>
                                                    <select name="status" class="form-select">
                                                        <option value="hadir" {{ $abs->status === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                                        <option value="terlambat" {{ $abs->status === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                                                        <option value="izin" {{ $abs->status === 'izin' ? 'selected' : '' }}>Izin</option>
                                                        <option value="sakit" {{ $abs->status === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                                        <option value="alpha" {{ $abs->status === 'alpha' ? 'selected' : '' }}>Alpha</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-semibold">Jam Scan / Masuk</label>
                                                    <input type="text" name="jam_scan" class="form-control" value="{{ $abs->jam_scan }}" placeholder="HH:MM:SS (contoh: 07:15:00)">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-semibold">Keterangan / Catatan</label>
                                                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Tambahkan catatan jika ada">{{ $abs->keterangan }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-warning btn-sm">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4" style="color:#6b7280;">Belum ada data absensi pada tanggal ini.</td></tr>
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
                                <option value="terlambat">Terlambat</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="alpha" selected>Alpha</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg"></i> Simpan</button>
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
