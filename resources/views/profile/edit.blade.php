@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-person-circle me-2 text-primary"></i>Profil Saya</h1>
        <p class="page-subtitle">Kelola informasi akun Anda</p>
    </div>
</div>

@if(session('status') === 'profile-updated')
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-check-circle-fill"></i> Profil berhasil diperbarui.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('status') === 'password-updated')
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-shield-check"></i> Password berhasil diubah.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    <!-- Info Profil -->
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header px-4 py-3">
                <i class="bi bi-person me-2 text-primary"></i>Informasi Profil
            </div>
            <div class="card-body p-4">
                <!-- Avatar besar -->
                <div class="text-center mb-4">
                    <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#4f46e5);display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;font-size:2rem;font-weight:700;color:white;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div style="font-size:0.78rem;color:#6b7280;">
                        <span class="badge bg-dark border" style="border-color:var(--border-color)!important;">
                            {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', auth()->user()->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', auth()->user()->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i>Simpan Profil
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Ganti Password -->
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header px-4 py-3">
                <i class="bi bi-shield-lock me-2 text-warning"></i>Ganti Password
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini</label>
                        <input type="password" name="current_password"
                            class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                            autocomplete="current-password">
                        @error('current_password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password"
                            class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                            autocomplete="new-password" placeholder="Min. 8 karakter">
                        @error('password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation"
                            class="form-control" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="bi bi-shield-check me-1"></i>Ganti Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Hapus Akun (hanya non-super_admin) -->
        @unless(auth()->user()->hasRole('super_admin'))
        <div class="card mt-4" style="border-color:rgba(239,68,68,0.2);">
            <div class="card-header px-4 py-3" style="border-color:rgba(239,68,68,0.2);">
                <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Zona Berbahaya
            </div>
            <div class="card-body p-4">
                <p style="font-size:0.82rem;color:#94a3b8;">
                    Menghapus akun akan menghapus semua data terkait secara permanen.
                </p>
                <button type="button" class="btn btn-outline-danger btn-sm"
                    data-bs-toggle="modal" data-bs-target="#modalHapusAkun">
                    <i class="bi bi-trash me-1"></i>Hapus Akun Saya
                </button>
            </div>
        </div>
        @endunless
    </div>
</div>

<!-- Modal Hapus Akun -->
<div class="modal fade" id="modalHapusAkun" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--bg-card);border:1px solid rgba(239,68,68,0.3);">
            <div class="modal-header" style="border-color:rgba(239,68,68,0.3);">
                <h5 class="modal-title text-danger"><i class="bi bi-trash me-2"></i>Hapus Akun</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf @method('DELETE')
                <div class="modal-body">
                    <p style="font-size:0.85rem;color:#94a3b8;">
                        Masukkan password Anda untuk mengkonfirmasi penghapusan akun.
                        <strong style="color:#f87171;">Tindakan ini tidak dapat dibatalkan.</strong>
                    </p>
                    <input type="password" name="password" class="form-control" placeholder="Password Anda" required>
                </div>
                <div class="modal-footer" style="border-color:rgba(239,68,68,0.3);">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm">Hapus Akun Saya</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
