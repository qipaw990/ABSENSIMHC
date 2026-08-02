@extends('layouts.app')

@section('title', 'Edit Rincian Tugas')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title"><i class="bi bi-gear me-2 text-warning"></i>Edit Rincian Tugas</h1>
        <p class="page-subtitle">{{ $penilaian->judul_tugas }} &mdash; {{ $penilaian->mata_pelajaran }}</p>
    </div>
    <a href="{{ route('guru.penilaian.show', $penilaian->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card mb-4" style="max-width: 800px;">
    <div class="card-header px-4 py-3">
        <i class="bi bi-pencil-square me-2 text-warning"></i>Form Edit Tugas & Materi
    </div>
    <div class="card-body p-4">
        <form action="{{ route('guru.penilaian.update', $penilaian->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label font-weight-semibold">Kelas Tujuan <span class="text-danger">*</span></label>
                    <select name="kelas_id" class="form-select" required>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ $penilaian->kelas_id == $k->id ? 'selected' : '' }}>
                            {{ $k->nama }} ({{ $k->jurusan->nama ?? '-' }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label font-weight-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                    <input type="text" name="mata_pelajaran" class="form-control" value="{{ old('mata_pelajaran', $penilaian->mata_pelajaran) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label font-weight-semibold">Bab / Topik Materi <span class="text-danger">*</span></label>
                    <input type="text" name="bab_materi" class="form-control" value="{{ old('bab_materi', $penilaian->bab_materi) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label font-weight-semibold">Judul Tugas / Evaluasi <span class="text-danger">*</span></label>
                    <input type="text" name="judul_tugas" class="form-control" value="{{ old('judul_tugas', $penilaian->judul_tugas) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label font-weight-semibold">Jenis Penilaian <span class="text-danger">*</span></label>
                    <select name="jenis" class="form-select" required>
                        <option value="tugas" {{ $penilaian->jenis == 'tugas' ? 'selected' : '' }}>Tugas Harian</option>
                        <option value="uh" {{ $penilaian->jenis == 'uh' ? 'selected' : '' }}>Ulangan Harian (UH)</option>
                        <option value="uts" {{ $penilaian->jenis == 'uts' ? 'selected' : '' }}>UTS / PTS</option>
                        <option value="uas" {{ $penilaian->jenis == 'uas' ? 'selected' : '' }}>UAS / PAS</option>
                        <option value="praktikum" {{ $penilaian->jenis == 'praktikum' ? 'selected' : '' }}>Praktikum Lab</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label font-weight-semibold">Tanggal Penilaian / Deadline <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', $penilaian->tanggal?->format('Y-m-d')) }}" required>
                </div>

                <div class="col-12">
                    <label class="form-label font-weight-semibold">Catatan / Deskripsi Tugas (Opsional)</label>
                    <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $penilaian->keterangan) }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('guru.penilaian.show', $penilaian->id) }}" class="btn btn-secondary btn-sm">Batal</a>
                <button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
