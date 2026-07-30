@extends('layouts.app')

@section('title', 'Tambah Tahun Ajaran')

@section('content')
<div class="page-header">
    <div><h1 class="page-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Tahun Ajaran</h1></div>
    <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>
<div class="card"><div class="card-body p-4" style="max-width:500px;">
    <form action="{{ route('admin.tahun-ajaran.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nama Tahun Ajaran <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                value="{{ old('nama') }}" placeholder="contoh: 2024/2025" required>
            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="form-label">Semester <span class="text-danger">*</span></label>
            <select name="semester" class="form-select @error('semester') is-invalid @enderror" required>
                <option value="">-- Pilih Semester --</option>
                <option value="ganjil" {{ old('semester') == 'ganjil' ? 'selected' : '' }}>Ganjil (Juli–Desember)</option>
                <option value="genap"  {{ old('semester') == 'genap'  ? 'selected' : '' }}>Genap (Januari–Juni)</option>
            </select>
            @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Simpan</button>
        <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
    </form>
</div></div>
@endsection
