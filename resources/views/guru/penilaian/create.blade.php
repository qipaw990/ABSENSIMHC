@extends('layouts.app')

@section('title', 'Tambah Tugas & Bab Materi Baru')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title"><i class="bi bi-journal-plus me-2 text-primary"></i>Tambah Tugas / Bab Baru</h1>
        <p class="page-subtitle">Buat topik materi pembelajaran, bab, dan tugas harian untuk penilaian siswa.</p>
    </div>
    <a href="{{ route('guru.penilaian.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card mb-4" style="max-width: 800px;">
    <div class="card-header px-4 py-3">
        <i class="bi bi-file-earmark-text me-2 text-primary"></i>Form Rincian Tugas & Materi Pembelajaran
    </div>
    <div class="card-body p-4">
        <form action="{{ route('guru.penilaian.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label font-weight-semibold">Kelas Tujuan <span class="text-danger">*</span></label>
                    <select name="kelas_id" class="form-select" required>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama }} ({{ $k->jurusan->nama ?? '-' }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label font-weight-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                    <input type="text" name="mata_pelajaran" class="form-control" placeholder="Contoh: Pemrograman Web / Matematika" value="{{ old('mata_pelajaran') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label font-weight-semibold">Bab / Topik Materi <span class="text-danger">*</span></label>
                    <input type="text" name="bab_materi" class="form-control" placeholder="Contoh: Bab 1 - Dasar HTML & CSS" value="{{ old('bab_materi') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label font-weight-semibold">Judul Tugas / Evaluasi <span class="text-danger">*</span></label>
                    <input type="text" name="judul_tugas" class="form-control" placeholder="Contoh: Tugas 1 - Membuat Layout Flexbox" value="{{ old('judul_tugas') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label font-weight-semibold">Jenis Penilaian <span class="text-danger">*</span></label>
                    <select name="jenis" class="form-select" required>
                        <option value="tugas" {{ old('jenis') == 'tugas' ? 'selected' : '' }}>Tugas Harian</option>
                        <option value="uh" {{ old('jenis') == 'uh' ? 'selected' : '' }}>Ulangan Harian (UH)</option>
                        <option value="uts" {{ old('jenis') == 'uts' ? 'selected' : '' }}>UTS / PTS</option>
                        <option value="uas" {{ old('jenis') == 'uas' ? 'selected' : '' }}>UAS / PAS</option>
                        <option value="praktikum" {{ old('jenis') == 'praktikum' ? 'selected' : '' }}>Praktikum Lab</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label font-weight-semibold">Tanggal Penilaian / Deadline <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                </div>

                <div class="col-12">
                    <label class="form-label font-weight-semibold">Catatan / Deskripsi Tugas (Opsional)</label>
                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Petunjuk pengerjaan tugas atau deskripsi materi...">{{ old('keterangan') }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('guru.penilaian.index') }}" class="btn btn-secondary btn-sm">Batal</a>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-circle me-1"></i>Buat Tugas & Input Nilai</button>
            </div>
        </form>
    </div>
</div>
@endsection
