@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title"><i class="bi bi-people-fill me-2 text-primary"></i>Manajemen User</h1>
        <p class="page-subtitle">Kelola akun pengguna, hak akses role, dan reset password sistem.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
        <i class="bi bi-person-plus-fill me-1"></i>Tambah User Baru
    </button>
</div>

<!-- Alert Notifikasi -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Filter & Search Bar -->
<div class="card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" action="{{ route('super-admin.user.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama atau email user..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="role" class="form-select form-select-sm">
                    <option value="">-- Semua Role --</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                        {{ strtoupper($role->name) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button>
                @if(request()->hasAny(['search', 'role']))
                <a href="{{ route('super-admin.user.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel List User -->
<div class="card mb-4">
    <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-person-lines-fill me-2 text-primary"></i>Daftar User ({{ $users->total() }})</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role Akses</th>
                        <th>Terdaftar</th>
                        <th class="text-end pe-4">Aksi / Kelola</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td style="color:#6b7280;">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2 font-weight-bold" style="width:36px;height:36px;font-size:0.9rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:0.88rem;">{{ $user->name }}</div>
                                    @if($user->id === Auth::id())
                                    <span class="badge bg-success" style="font-size:0.65rem;">Akun Anda</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="font-size:0.85rem;color:#94a3b8;">{{ $user->email }}</td>
                        <td>
                            @foreach($user->roles as $r)
                                @php
                                    $badgeColor = match($r->name) {
                                        'super_admin' => 'bg-danger',
                                        'admin'       => 'bg-warning text-dark',
                                        'guru'        => 'bg-primary',
                                        'siswa'       => 'bg-info text-dark',
                                        default       => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeColor }}" style="font-size:0.75rem;">{{ strtoupper($r->name) }}</span>
                            @endforeach
                        </td>
                        <td style="font-size:0.8rem;color:#6b7280;">{{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}</td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end">
                                <!-- Reset Password Modal Button -->
                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#resetModal{{ $user->id }}" title="Reset Password">
                                    <i class="bi bi-key"></i> Reset Pass
                                </button>

                                <!-- Edit Modal Button -->
                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $user->id }}" title="Edit User">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>

                                <!-- Hapus Button -->
                                @if($user->id !== Auth::id())
                                <form action="{{ route('super-admin.user.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus User">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                                @endif
                            </div>

                            <!-- Modal Edit User -->
                            <div class="modal fade text-start" id="editModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('super-admin.user.update', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="bi bi-pencil-square text-warning me-2"></i>Edit User — {{ $user->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-semibold">Nama Lengkap</label>
                                                    <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-semibold">Alamat Email</label>
                                                    <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-semibold">Role Hak Akses</label>
                                                    <select name="role" class="form-select" required>
                                                        @foreach($roles as $role)
                                                        <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                                            {{ strtoupper($role->name) }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-semibold">Password Baru (Opsional)</label>
                                                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-warning btn-sm">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Reset Password -->
                            <div class="modal fade text-start" id="resetModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('super-admin.user.reset-password', $user->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="bi bi-key text-info me-2"></i>Reset Password — {{ $user->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-muted small">Masukkan password baru untuk user <strong>{{ $user->email }}</strong>.</p>
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-semibold">Password Baru</label>
                                                    <input type="text" name="new_password" class="form-control" value="Password123!" required>
                                                    <small class="form-text text-muted">Default: <code>Password123!</code></small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-info btn-sm text-white">Reset Password</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Tidak ada data user ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="p-3">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Tambah User Baru -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('super-admin.user.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus-fill text-primary me-2"></i>Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-weight-semibold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Santoso, S.Pd." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-semibold">Alamat Email</label>
                        <input type="email" name="email" class="form-control" placeholder="budi@sekolah.sch.id" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-semibold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-semibold">Role Hak Akses</label>
                        <select name="role" class="form-select" required>
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ strtoupper($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
