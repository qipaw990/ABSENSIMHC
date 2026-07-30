@extends('layouts.app')

@section('title', 'Edit Guru — ' . $guru->nama)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-pencil me-2 text-warning"></i>Edit Guru</h1>
        <p class="page-subtitle">{{ $guru->nama }}</p>
    </div>
    <a href="{{ route('admin.guru.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('admin.guru.update', $guru) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                        value="{{ old('nama', $guru->nama) }}" required>
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror"
                        value="{{ old('nip', $guru->nip) }}">
                    @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. WhatsApp</label>
                    <input type="text" name="no_wa" class="form-control" value="{{ old('no_wa', $guru->no_wa) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Foto</label>
                    @if($guru->foto)
                        <div class="mb-2">
                            <img src="{{ asset('storage/'.$guru->foto) }}" style="width:48px;height:48px;border-radius:50%;object-fit:cover;">
                            <small style="color:#6b7280;margin-left:0.5rem;">Foto saat ini</small>
                        </div>
                    @endif
                    <input type="file" name="foto" class="form-control" accept="image/*">
                    <small style="color:#6b7280;font-size:0.72rem;">Kosongkan jika tidak ingin mengganti</small>
                </div>
            </div>
            <div class="mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,0.07);">
                <button type="submit" class="btn btn-warning px-4">
                    <i class="bi bi-check-lg me-1"></i>Perbarui Data
                </button>
                <a href="{{ route('admin.guru.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
