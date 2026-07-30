@extends('layouts.app')

@section('title', 'Tambah Kelas')

@section('content')
<div class="page-header">
    <div><h1 class="page-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Kelas</h1></div>
    <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>
<div class="card"><div class="card-body p-4">
    <form action="{{ route('admin.kelas.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                    value="{{ old('nama') }}" placeholder="contoh: XII RPL 1" required>
                @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Tingkat <span class="text-danger">*</span></label>
                <select name="tingkat" class="form-select @error('tingkat') is-invalid @enderror" required>
                    <option value="">-- Pilih Tingkat --</option>
                    <option value="10" {{ old('tingkat') == 10 ? 'selected' : '' }}>Kelas 10</option>
                    <option value="11" {{ old('tingkat') == 11 ? 'selected' : '' }}>Kelas 11</option>
                    <option value="12" {{ old('tingkat') == 12 ? 'selected' : '' }}>Kelas 12</option>
                </select>
                @error('tingkat')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Jurusan <span class="text-danger">*</span></label>
                <select name="jurusan_id" class="form-select @error('jurusan_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Jurusan --</option>
                    @foreach($jurusan as $j)
                    <option value="{{ $j->id }}" {{ old('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama }} ({{ $j->kode }})</option>
                    @endforeach
                </select>
                @error('jurusan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                <select name="tahun_ajaran_id" class="form-select @error('tahun_ajaran_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Tahun Ajaran --</option>
                    @foreach($tahunAjaran as $ta)
                    <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id') == $ta->id ? 'selected' : '' }}>
                        {{ $ta->nama }} - {{ ucfirst($ta->semester) }} {{ $ta->is_aktif ? '(Aktif)' : '' }}
                    </option>
                    @endforeach
                </select>
                @error('tahun_ajaran_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Wali Kelas</label>
                <select name="wali_kelas_id" class="form-select">
                    <option value="">-- Belum ditentukan --</option>
                    @foreach($guru as $g)
                    <option value="{{ $g->id }}" {{ old('wali_kelas_id') == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,0.07);">
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Simpan Kelas</button>
            <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
        </div>
    </form>
</div></div>
@endsection
