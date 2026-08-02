@extends('layouts.app')

@section('title', 'Jadwal Pelajaran')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title"><i class="bi bi-calendar-week me-2 text-primary"></i>Jadwal Pelajaran</h1>
        <p class="page-subtitle">Kelola jadwal pelajaran sekolah, pengampu guru, jam pelajaran, dan lokasi ruangan.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
        <i class="bi bi-plus-lg me-1"></i>Tambah Jadwal Pelajaran
    </button>
</div>

<!-- Alert Notifikasi -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Filter & Search Bar Seamless -->
<div class="card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" action="{{ route('admin.jadwal.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="kelas_id" class="form-select form-select-sm">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                        {{ $k->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="hari" class="form-select form-select-sm">
                    <option value="">-- Semua Hari --</option>
                    <option value="senin" {{ request('hari') == 'senin' ? 'selected' : '' }}>Senin</option>
                    <option value="selasa" {{ request('hari') == 'selasa' ? 'selected' : '' }}>Selasa</option>
                    <option value="rabu" {{ request('hari') == 'rabu' ? 'selected' : '' }}>Rabu</option>
                    <option value="kamis" {{ request('hari') == 'kamis' ? 'selected' : '' }}>Kamis</option>
                    <option value="jumat" {{ request('hari') == 'jumat' ? 'selected' : '' }}>Jumat</option>
                    <option value="sabtu" {{ request('hari') == 'sabtu' ? 'selected' : '' }}>Sabtu</option>
                </select>
            </div>
            <div class="col-md-4">
                <select name="guru_id" class="form-select form-select-sm">
                    <option value="">-- Semua Guru Pengampu --</option>
                    @foreach($guruList as $g)
                    <option value="{{ $g->id }}" {{ request('guru_id') == $g->id ? 'selected' : '' }}>
                        {{ $g->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button>
                @if(request()->hasAny(['kelas_id', 'hari', 'guru_id']))
                <a href="{{ route('admin.jadwal.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel Jadwal Pelajaran -->
<div class="card mb-4">
    <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2 text-primary"></i>Daftar Jadwal Pelajaran ({{ $jadwalList->total() }})</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Hari & Jam</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru Pengampu</th>
                        <th>Ruangan</th>
                        <th class="text-end pe-4">Aksi / Kelola</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwalList as $j)
                    <tr>
                        <td style="color:#6b7280;">{{ $loop->iteration + ($jadwalList->currentPage() - 1) * $jadwalList->perPage() }}</td>
                        <td>
                            @php
                                $badgeHari = match($j->hari) {
                                    'senin'  => 'bg-primary',
                                    'selasa' => 'bg-info text-dark',
                                    'rabu'   => 'bg-success',
                                    'kamis'  => 'bg-warning text-dark',
                                    'jumat'  => 'bg-danger',
                                    default  => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeHari }}" style="font-size:0.75rem;">{{ $j->hari_label }}</span>
                            <div style="font-family:monospace;font-size:0.82rem;color:#818cf8;margin-top:2px;">
                                <i class="bi bi-clock me-1"></i>{{ $j->jam_format }}
                            </div>
                        </td>
                        <td><span class="badge bg-secondary" style="font-size:0.78rem;">{{ $j->kelas->nama ?? '-' }}</span></td>
                        <td>
                            <div style="font-weight:600;font-size:0.88rem;color:#f1f5f9;">{{ $j->mataPelajaran->nama ?? '-' }}</div>
                            <div style="font-size:0.72rem;color:#6b7280;">Kode: {{ $j->mataPelajaran->kode ?? '-' }}</div>
                        </td>
                        <td>
                            <div style="font-weight:500;font-size:0.85rem;color:#e2e8f0;">
                                <i class="bi bi-person me-1 text-primary"></i>{{ $j->guru->nama ?? '-' }}
                            </div>
                        </td>
                        <td style="font-size:0.82rem;color:#94a3b8;">
                            <i class="bi bi-geo-alt me-1 text-danger"></i>{{ $j->ruangan ?? 'Kelas Reguler' }}
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end">
                                <!-- Tombol Edit -->
                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $j->id }}" title="Edit Jadwal">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>

                                <!-- Tombol Hapus -->
                                <form action="{{ route('admin.jadwal.destroy', $j->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Jadwal">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>

                            <!-- Modal Edit Jadwal -->
                            <div class="modal fade text-start" id="editModal{{ $j->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.jadwal.update', $j->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Jadwal Pelajaran</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-semibold">Kelas</label>
                                                    <select name="kelas_id" class="form-select" required>
                                                        @foreach($kelasList as $k)
                                                        <option value="{{ $k->id }}" {{ $j->kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-semibold">Mata Pelajaran</label>
                                                    <select name="mata_pelajaran_id" class="form-select" required>
                                                        @foreach($mapelList as $mp)
                                                        <option value="{{ $mp->id }}" {{ $j->mata_pelajaran_id == $mp->id ? 'selected' : '' }}>{{ $mp->nama }} ({{ $mp->kode }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-semibold">Guru Pengampu</label>
                                                    <select name="guru_id" class="form-select" required>
                                                        @foreach($guruList as $g)
                                                        <option value="{{ $g->id }}" {{ $j->guru_id == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label font-weight-semibold">Hari</label>
                                                        <select name="hari" class="form-select" required>
                                                            <option value="senin" {{ $j->hari == 'senin' ? 'selected' : '' }}>Senin</option>
                                                            <option value="selasa" {{ $j->hari == 'selasa' ? 'selected' : '' }}>Selasa</option>
                                                            <option value="rabu" {{ $j->hari == 'rabu' ? 'selected' : '' }}>Rabu</option>
                                                            <option value="kamis" {{ $j->hari == 'kamis' ? 'selected' : '' }}>Kamis</option>
                                                            <option value="jumat" {{ $j->hari == 'jumat' ? 'selected' : '' }}>Jumat</option>
                                                            <option value="sabtu" {{ $j->hari == 'sabtu' ? 'selected' : '' }}>Sabtu</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label font-weight-semibold">Jam Mulai</label>
                                                        <input type="time" name="jam_mulai" class="form-control" value="{{ substr($j->jam_mulai,0,5) }}" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label font-weight-semibold">Jam Selesai</label>
                                                        <input type="time" name="jam_selesai" class="form-control" value="{{ substr($j->jam_selesai,0,5) }}" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-semibold">Ruangan (Opsional)</label>
                                                    <input type="text" name="ruangan" class="form-control" value="{{ $j->ruangan }}">
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
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada jadwal pelajaran terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($jadwalList->hasPages())
        <div class="p-3">
            {{ $jadwalList->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Jadwal Pelajaran Baru -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.jadwal.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-calendar-plus text-primary me-2"></i>Tambah Jadwal Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-weight-semibold">Kelas Tujuan <span class="text-danger">*</span></label>
                        <select name="kelas_id" class="form-select" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelasList as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="mata_pelajaran_id" class="form-select" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($mapelList as $mp)
                            <option value="{{ $mp->id }}">{{ $mp->nama }} ({{ $mp->kode }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-semibold">Guru Pengampu <span class="text-danger">*</span></label>
                        <select name="guru_id" class="form-select" required>
                            <option value="">-- Pilih Guru Pengampu --</option>
                            @foreach($guruList as $g)
                            <option value="{{ $g->id }}">{{ $g->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label font-weight-semibold">Hari <span class="text-danger">*</span></label>
                            <select name="hari" class="form-select" required>
                                <option value="senin">Senin</option>
                                <option value="selasa">Selasa</option>
                                <option value="rabu">Rabu</option>
                                <option value="kamis">Kamis</option>
                                <option value="jumat">Jumat</option>
                                <option value="sabtu">Sabtu</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-weight-semibold">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_mulai" class="form-control" value="07:15" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-weight-semibold">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_selesai" class="form-control" value="08:45" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-semibold">Ruangan (Opsional)</label>
                        <input type="text" name="ruangan" class="form-control" placeholder="Contoh: Lab Komputer 1 / R. 102">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
