@extends('layouts.app')

@section('title', 'Tambah Pengaturan Absensi')

@section('content')
<div class="page-header">
    <div><h1 class="page-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Pengaturan Absensi</h1></div>
    <a href="{{ route('admin.pengaturan-absensi.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<div class="card" style="max-width:580px;">
    <div class="card-body p-4">
        <form action="{{ route('admin.pengaturan-absensi.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror">
                        <option value="">-- Global (berlaku untuk semua kelas) --</option>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama }} — {{ $k->jurusan->nama ?? '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jam Batas Masuk <span class="text-danger">*</span></label>
                    <input type="time" name="jam_masuk_batas" class="form-control @error('jam_masuk_batas') is-invalid @enderror"
                        value="{{ old('jam_masuk_batas', '07:00') }}" required>
                    <small style="color:#6b7280;font-size:0.72rem;">Setelah jam ini dianggap Terlambat</small>
                    @error('jam_masuk_batas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jam Tutup Absensi <span class="text-danger">*</span></label>
                    <input type="time" name="jam_absensi_tutup" class="form-control @error('jam_absensi_tutup') is-invalid @enderror"
                        value="{{ old('jam_absensi_tutup', '08:00') }}" required>
                    <small style="color:#6b7280;font-size:0.72rem;">Setelah jam ini akan di-set Alpha otomatis</small>
                    @error('jam_absensi_tutup')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label d-flex align-items-center gap-2">
                        <input type="checkbox" name="aktif_sabtu" value="1" {{ old('aktif_sabtu') ? 'checked' : '' }}>
                        Aktif di hari Sabtu
                    </label>
                    <small style="color:#6b7280;font-size:0.72rem;">Centang jika sekolah masuk pada hari Sabtu</small>
                </div>
            </div>
            <div class="mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,0.07);">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Simpan</button>
                <a href="{{ route('admin.pengaturan-absensi.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
