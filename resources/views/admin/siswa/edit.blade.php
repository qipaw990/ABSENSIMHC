@extends('layouts.app')

@section('title', 'Edit Siswa — ' . $siswa->nama)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-pencil me-2 text-warning"></i>Edit Siswa</h1>
        <p class="page-subtitle">{{ $siswa->nama }}</p>
    </div>
    <a href="{{ route('admin.siswa.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('admin.siswa.update', $siswa) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">NIS <span class="text-danger">*</span></label>
                    <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror"
                        value="{{ old('nis', $siswa->nis) }}" required>
                    @error('nis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">NISN</label>
                    <input type="text" name="nisn" class="form-control"
                        value="{{ old('nisn', $siswa->nisn) }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                        value="{{ old('nama', $siswa->nama) }}" required>
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kelas <span class="text-danger">*</span></label>
                    <select name="kelas_id" class="form-select" required>
                        @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->id }}" {{ $siswa->kelas_id == $kelas->id ? 'selected' : '' }}>
                            {{ $kelas->nama }} ({{ $kelas->jurusan->kode ?? '' }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Foto Siswa</label>
                    @if($siswa->foto)
                    <div class="mb-2">
                        <img src="{{ $siswa->foto_url }}" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid rgba(99,102,241,0.4);">
                        <small style="color:#6b7280;margin-left:0.5rem;">Foto saat ini</small>
                    </div>
                    @endif
                    <input type="file" name="foto" class="form-control" accept="image/*">
                    <small style="color:#6b7280;font-size:0.72rem;">Kosongkan jika tidak ingin mengganti foto</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Orang Tua/Wali</label>
                    <input type="text" name="nama_ortu" class="form-control"
                        value="{{ old('nama_ortu', $siswa->nama_ortu) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. WhatsApp Orang Tua</label>
                    <input type="text" name="no_wa_ortu" class="form-control"
                        value="{{ old('no_wa_ortu', $siswa->no_wa_ortu) }}" placeholder="628xxxxxxxxx">
                </div>
            </div>
            <div class="mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,0.07);">
                <button type="submit" class="btn btn-warning px-4">
                    <i class="bi bi-check-lg me-1"></i>Perbarui Data
                </button>
                <a href="{{ route('admin.siswa.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
