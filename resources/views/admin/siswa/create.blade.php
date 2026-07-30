@extends('layouts.app')

@section('title', 'Tambah Siswa')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-person-plus me-2 text-primary"></i>Tambah Siswa</h1>
        <p class="page-subtitle">Isi data siswa baru</p>
    </div>
    <a href="{{ route('admin.siswa.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('admin.siswa.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">NIS <span class="text-danger">*</span></label>
                    <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror"
                        value="{{ old('nis') }}" placeholder="Nomor Induk Siswa" required>
                    @error('nis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">NISN</label>
                    <input type="text" name="nisn" class="form-control @error('nisn') is-invalid @enderror"
                        value="{{ old('nisn') }}" placeholder="Nomor Induk Siswa Nasional">
                    @error('nisn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                        value="{{ old('nama') }}" placeholder="Nama lengkap siswa" required>
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kelas <span class="text-danger">*</span></label>
                    <select name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                            {{ $kelas->nama }} ({{ $kelas->jurusan->kode ?? '' }})
                        </option>
                        @endforeach
                    </select>
                    @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Foto Siswa</label>
                    <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror"
                        accept="image/*">
                    @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Orang Tua/Wali</label>
                    <input type="text" name="nama_ortu" class="form-control"
                        value="{{ old('nama_ortu') }}" placeholder="Nama orang tua/wali">
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. WhatsApp Orang Tua</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:rgba(255,255,255,0.05);border-color:rgba(255,255,255,0.07);color:#94a3b8;">+62</span>
                        <input type="text" name="no_wa_ortu" class="form-control @error('no_wa_ortu') is-invalid @enderror"
                            value="{{ old('no_wa_ortu') }}" placeholder="81234567890">
                    </div>
                    <small style="color:#6b7280;font-size:0.72rem;">Format: 628xxx atau 08xxx (otomatis dikonversi)</small>
                    @error('no_wa_ortu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,0.07);">
                <div style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.2);border-radius:10px;padding:0.85rem 1rem;font-size:0.8rem;color:#94a3b8;margin-bottom:1.25rem;">
                    <i class="bi bi-info-circle me-1 text-primary"></i>
                    Akun login siswa akan dibuat otomatis dengan:<br>
                    <strong style="color:#c7d2fe;">Email:</strong> {NIS}@siswa.sch.id &nbsp;|&nbsp;
                    <strong style="color:#c7d2fe;">Password:</strong> sama dengan NIS
                </div>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-1"></i>Simpan Data Siswa
                </button>
                <a href="{{ route('admin.siswa.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
