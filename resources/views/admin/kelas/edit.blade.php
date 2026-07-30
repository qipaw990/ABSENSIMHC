@extends('layouts.app')

@section('title', 'Edit Kelas — ' . $kelas->nama)

@section('content')
<div class="page-header">
    <div><h1 class="page-title"><i class="bi bi-pencil me-2 text-warning"></i>Edit Kelas — {{ $kelas->nama }}</h1></div>
    <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>
<div class="card"><div class="card-body p-4">
    <form action="{{ route('admin.kelas.update', $kelas->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama', $kelas->nama) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tingkat <span class="text-danger">*</span></label>
                <select name="tingkat" class="form-select" required>
                    <option value="10" {{ $kelas->tingkat == 10 ? 'selected' : '' }}>Kelas 10</option>
                    <option value="11" {{ $kelas->tingkat == 11 ? 'selected' : '' }}>Kelas 11</option>
                    <option value="12" {{ $kelas->tingkat == 12 ? 'selected' : '' }}>Kelas 12</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Jurusan <span class="text-danger">*</span></label>
                <select name="jurusan_id" class="form-select" required>
                    @foreach($jurusan as $j)
                    <option value="{{ $j->id }}" {{ $kelas->jurusan_id == $j->id ? 'selected' : '' }}>{{ $j->nama }} ({{ $j->kode }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                <select name="tahun_ajaran_id" class="form-select" required>
                    @foreach($tahunAjaran as $ta)
                    <option value="{{ $ta->id }}" {{ $kelas->tahun_ajaran_id == $ta->id ? 'selected' : '' }}>
                        {{ $ta->nama }} - {{ ucfirst($ta->semester) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Wali Kelas</label>
                <select name="wali_kelas_id" class="form-select">
                    <option value="">-- Belum ditentukan --</option>
                    @foreach($guru as $g)
                    <option value="{{ $g->id }}" {{ $kelas->wali_kelas_id == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,0.07);">
            <button type="submit" class="btn btn-warning px-4"><i class="bi bi-check-lg me-1"></i>Perbarui Kelas</button>
            <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
        </div>
    </form>
</div></div>
@endsection
