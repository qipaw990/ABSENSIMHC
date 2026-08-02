@extends('layouts.app')

@section('title', 'Input Nilai — ' . $penilaian->judul_tugas)

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title"><i class="bi bi-pencil-square me-2 text-primary"></i>Input Nilai Siswa</h1>
        <p class="page-subtitle">{{ $penilaian->mata_pelajaran }} &mdash; {{ $penilaian->bab_materi }} ({{ $penilaian->kelas->nama ?? '-' }})</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('guru.penilaian.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
        </a>
    </div>
</div>

<!-- Alert Notifikasi -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Info Card Rincian Tugas -->
<div class="card mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="badge bg-primary mb-2">{{ $penilaian->jenis_label }}</span>
                <h4 class="mb-1 text-white font-weight-bold">{{ $penilaian->judul_tugas }}</h4>
                <p class="text-muted small mb-0">
                    <i class="bi bi-book me-1"></i>{{ $penilaian->bab_materi }} &bull; 
                    <i class="bi bi-building me-1"></i>Kelas: <strong>{{ $penilaian->kelas->nama ?? '-' }}</strong> &bull; 
                    <i class="bi bi-calendar me-1"></i>Tanggal: <strong>{{ $penilaian->tanggal ? $penilaian->tanggal->format('d F Y') : '-' }}</strong>
                </p>
                @if($penilaian->keterangan)
                <p class="mt-2 text-slate-300 small bg-slate-800 p-2 rounded">{{ $penilaian->keterangan }}</p>
                @endif
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="p-3 bg-slate-800 rounded border border-slate-700 text-center d-inline-block">
                    <div class="text-muted small">Rata-rata Nilai Kelas</div>
                    <div class="text-emerald-400 font-weight-bold" style="font-size: 1.8rem;">
                        {{ number_format($nilaiList->avg('nilai') ?? 0, 1) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Batch Input Nilai -->
<form action="{{ route('guru.penilaian.store-nilai-batch', $penilaian->id) }}" method="POST">
    @csrf

    <div class="card mb-4">
        <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-person-lines-fill me-2 text-primary"></i>Daftar Siswa Kelas {{ $penilaian->kelas->nama ?? '-' }} ({{ $nilaiList->count() }} Siswa)</span>
            <button type="submit" class="btn btn-success btn-sm font-weight-bold">
                <i class="bi bi-save me-1"></i>Simpan Seluruh Nilai
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="min-width: 200px;">Siswa</th>
                            <th style="width: 140px;">Nilai (0 - 100)</th>
                            <th>Catatan Guru / Feedback</th>
                            <th style="width: 100px;" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($nilaiList as $item)
                        <tr>
                            <td style="color:#6b7280;">{{ $loop->iteration }}</td>
                            <td>
                                <div style="font-weight:600;font-size:0.88rem;color:#f1f5f9;">{{ $item->siswa->nama }}</div>
                                <div style="font-size:0.75rem;color:#6b7280;">NIS: {{ $item->siswa->nis }}</div>
                            </td>
                            <td>
                                <input type="number" name="nilai[{{ $item->id }}]" class="form-control form-control-sm text-center font-weight-bold text-emerald-400" 
                                    style="font-size: 1rem; background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.3);"
                                    value="{{ old('nilai.' . $item->id, (float) $item->nilai) }}" min="0" max="100" step="0.5" required>
                            </td>
                            <td>
                                <input type="text" name="catatan_guru[{{ $item->id }}]" class="form-control form-control-sm" 
                                    placeholder="Catatan perbaikan / pujian (opsional)" 
                                    value="{{ old('catatan_guru.' . $item->id, $item->catatan_guru) }}">
                            </td>
                            <td class="text-center">
                                @if($item->nilai >= 75)
                                <span class="badge bg-success" style="font-size:0.72rem;">Tuntas</span>
                                @elseif($item->nilai > 0)
                                <span class="badge bg-warning text-dark" style="font-size:0.72rem;">Remidi</span>
                                @else
                                <span class="badge bg-secondary" style="font-size:0.72rem;">Belum</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada siswa terdaftar di kelas ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer px-4 py-3 bg-slate-800 text-end">
            <button type="submit" class="btn btn-success btn-sm font-weight-bold px-4">
                <i class="bi bi-save me-1"></i>Simpan Seluruh Nilai
            </button>
        </div>
    </div>
</form>
@endsection
