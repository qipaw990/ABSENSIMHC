@extends('layouts.app')

@section('title', 'Tambah WA Sender')

@section('content')
<div class="page-header">
    <div><h1 class="page-title"><i class="bi bi-whatsapp me-2" style="color:#25d366;"></i>Tambah WA Sender</h1></div>
    <a href="{{ route('super-admin.wa-sender.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('super-admin.wa-sender.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Kelas</label>
                            <select name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror">
                                <option value="">-- Global (semua kelas) --</option>
                                @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama }} — {{ $kelas->jurusan->nama ?? '' }}
                                </option>
                                @endforeach
                            </select>
                            <small style="color:#6b7280;font-size:0.72rem;">Pilih kelas spesifik atau kosongkan untuk sender global</small>
                            @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nama Device/Nomor <span class="text-danger">*</span></label>
                            <input type="text" name="nama_device" class="form-control @error('nama_device') is-invalid @enderror"
                                value="{{ old('nama_device') }}" placeholder="contoh: WA Kelas XII RPL 1" required>
                            @error('nama_device')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Token Fonnte API <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="token_fonnte" id="tokenInput"
                                    class="form-control @error('token_fonnte') is-invalid @enderror"
                                    value="{{ old('token_fonnte') }}" placeholder="Token dari dashboard Fonnte" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="toggleToken()">
                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                            <small style="color:#6b7280;font-size:0.72rem;">Dapatkan dari: fonnte.com → Dashboard → Device → Token</small>
                            @error('token_fonnte')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,0.07);">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i>Simpan WA Sender
                        </button>
                        <a href="{{ route('super-admin.wa-sender.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Panduan -->
    <div class="col-12 col-lg-5">
        <div class="card">
            <div class="card-header px-4 py-3">
                <i class="bi bi-book me-2 text-primary"></i>Cara Mendapatkan Token Fonnte
            </div>
            <div class="card-body p-4">
                <div class="d-flex flex-column gap-3">
                    @foreach([
                        ['1', '#6366f1', 'Daftar di Fonnte', 'Buka fonnte.com dan buat akun gratis'],
                        ['2', '#22c55e', 'Tambah Device', 'Klik "Add Device" di dashboard Fonnte'],
                        ['3', '#f59e0b', 'Scan QR', 'Buka WhatsApp di HP → Perangkat Tertaut → Tautkan Perangkat → Scan QR'],
                        ['4', '#8b5cf6', 'Salin Token', 'Setelah terhubung, salin token API dari halaman device'],
                        ['5', '#ef4444', 'Tempel di sini', 'Paste token ke kolom di sebelah kiri dan simpan'],
                    ] as $step)
                    <div class="d-flex gap-3">
                        <div style="width:28px;height:28px;border-radius:50%;background:{{ $step[1] }};opacity:0.8;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;color:white;flex-shrink:0;">
                            {{ $step[0] }}
                        </div>
                        <div>
                            <div style="font-size:0.85rem;font-weight:600;color:#e2e8f0;">{{ $step[2] }}</div>
                            <div style="font-size:0.78rem;color:#6b7280;">{{ $step[3] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div style="margin-top:1.5rem;padding:0.85rem;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:10px;font-size:0.78rem;color:#94a3b8;">
                    <i class="bi bi-exclamation-triangle me-1 text-warning"></i>
                    <strong style="color:#e2e8f0;">Tips:</strong> Gunakan nomor HP khusus per kelas (misal nomor lama yang tidak aktif dipakai). Satu nomor per kelas untuk menghindari banned WA.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleToken() {
    const input = document.getElementById('tokenInput');
    const icon = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
@endpush
