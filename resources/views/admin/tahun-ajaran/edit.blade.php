@extends('layouts.app')

@section('title', 'Edit Tahun Ajaran')

@section('content')
<div class="page-header">
    <div><h1 class="page-title"><i class="bi bi-pencil me-2 text-warning"></i>Edit Tahun Ajaran</h1></div>
    <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>
<div class="card"><div class="card-body p-4" style="max-width:500px;">
    <form action="{{ route('admin.tahun-ajaran.update', $tahunAjaran) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">Nama Tahun Ajaran <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama', $tahunAjaran->nama) }}" required>
        </div>
        <div class="mb-4">
            <label class="form-label">Semester <span class="text-danger">*</span></label>
            <select name="semester" class="form-select" required>
                <option value="ganjil" {{ $tahunAjaran->semester === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                <option value="genap"  {{ $tahunAjaran->semester === 'genap'  ? 'selected' : '' }}>Genap</option>
            </select>
        </div>
        <button type="submit" class="btn btn-warning px-4"><i class="bi bi-check-lg me-1"></i>Perbarui</button>
        <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
    </form>
</div></div>
@endsection
