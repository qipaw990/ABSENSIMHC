@extends('layouts.app')

@section('title', 'Tambah Jurusan')

@section('content')
<div class="page-header">
    <div><h1 class="page-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Jurusan</h1></div>
    <a href="{{ route('admin.jurusan.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>
<div class="card"><div class="card-body p-4" style="max-width:500px;">
    <form action="{{ route('admin.jurusan.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Kode Jurusan <span class="text-danger">*</span></label>
            <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                value="{{ old('kode') }}" placeholder="RPL, TKJ, AKL, dll" style="text-transform:uppercase;" required>
            @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="form-label">Nama Jurusan <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                value="{{ old('nama') }}" placeholder="Rekayasa Perangkat Lunak" required>
            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Simpan</button>
        <a href="{{ route('admin.jurusan.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
    </form>
</div></div>
@endsection
