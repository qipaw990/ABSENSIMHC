@extends('layouts.app')

@section('title', 'Edit Pengaturan Absensi')

@section('content')
<div class="page-header">
    <div><h1 class="page-title"><i class="bi bi-pencil me-2 text-warning"></i>Edit Pengaturan Absensi</h1>
    <p class="page-subtitle">{{ $pengaturanAbsensi->kelas->nama ?? 'Global' }}</p></div>
    <a href="{{ route('admin.pengaturan-absensi.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<div class="card" style="max-width:580px;">
    <div class="card-body p-4">
        <form action="{{ route('admin.pengaturan-absensi.update', $pengaturanAbsensi) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Kelas</label>
                    <input type="text" class="form-control" value="{{ $pengaturanAbsensi->kelas->nama ?? 'Global (semua kelas)' }}" disabled style="background:rgba(255,255,255,0.03);">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jam Batas Masuk <span class="text-danger">*</span></label>
                    <input type="time" name="jam_masuk_batas" class="form-control"
                        value="{{ old('jam_masuk_batas', substr($pengaturanAbsensi->jam_masuk_batas, 0, 5)) }}" required>
                    <small style="color:#6b7280;font-size:0.72rem;">Setelah jam ini dianggap Terlambat</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jam Tutup Absensi <span class="text-danger">*</span></label>
                    <input type="time" name="jam_absensi_tutup" class="form-control"
                        value="{{ old('jam_absensi_tutup', substr($pengaturanAbsensi->jam_absensi_tutup, 0, 5)) }}" required>
                    <small style="color:#6b7280;font-size:0.72rem;">Setelah jam ini akan di-set Alpha otomatis</small>
                </div>
                <div class="col-12">
                    <label class="form-label d-flex align-items-center gap-2">
                        <input type="checkbox" name="aktif_sabtu" value="1" {{ $pengaturanAbsensi->aktif_sabtu ? 'checked' : '' }}>
                        Aktif di hari Sabtu
                    </label>
                </div>
            </div>
            <div class="mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,0.07);">
                <button type="submit" class="btn btn-warning px-4"><i class="bi bi-check-lg me-1"></i>Perbarui</button>
                <a href="{{ route('admin.pengaturan-absensi.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
