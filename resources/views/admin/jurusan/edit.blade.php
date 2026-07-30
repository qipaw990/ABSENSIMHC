@extends('layouts.app')

@section('title', 'Edit Jurusan')

@section('content')
<div class="page-header">
    <div><h1 class="page-title"><i class="bi bi-pencil me-2 text-warning"></i>Edit Jurusan</h1></div>
    <a href="{{ route('admin.jurusan.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>
<div class="card"><div class="card-body p-4" style="max-width:500px;">
    <form action="{{ route('admin.jurusan.update', $jurusan) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">Kode Jurusan <span class="text-danger">*</span></label>
            <input type="text" name="kode" class="form-control" value="{{ old('kode', $jurusan->kode) }}" required style="text-transform:uppercase;">
        </div>
        <div class="mb-4">
            <label class="form-label">Nama Jurusan <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama', $jurusan->nama) }}" required>
        </div>
        <button type="submit" class="btn btn-warning px-4"><i class="bi bi-check-lg me-1"></i>Perbarui</button>
        <a href="{{ route('admin.jurusan.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
    </form>
</div></div>
@endsection
