@extends('layouts.app')

@section('title', 'Edit WA Sender')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-pencil me-2 text-warning"></i>Edit WA Sender</h1>
        <p class="page-subtitle">{{ $waSender->nama_device }}</p>
    </div>
    <a href="{{ route('super-admin.wa-sender.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card" style="max-width:600px;">
    <div class="card-body p-4">
        <form action="{{ route('super-admin.wa-sender.update', $waSender) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Kelas <span class="text-danger">*</span></label>
                    <select name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror" required>
                        @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->id }}" {{ old('kelas_id', $waSender->kelas_id) == $kelas->id ? 'selected' : '' }}>
                            {{ $kelas->nama }} — {{ $kelas->jurusan->nama ?? '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Nama Device <span class="text-danger">*</span></label>
                    <input type="text" name="nama_device" class="form-control @error('nama_device') is-invalid @enderror"
                        value="{{ old('nama_device', $waSender->nama_device) }}" placeholder="contoh: WA Kelas XII RPL 1" required>
                    @error('nama_device')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Nomor WA Sender (Opsional)</label>
                    <input type="text" name="nomor_wa" class="form-control @error('nomor_wa') is-invalid @enderror"
                        value="{{ old('nomor_wa', $waSender->nomor_wa) }}" placeholder="contoh: 08123456789">
                    @error('nomor_wa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">API Key WhatsApp Gateway Baru</label>
                    <div class="input-group">
                        <input type="password" name="token_fonnte" id="tokenInput"
                            class="form-control @error('token_fonnte') is-invalid @enderror"
                            placeholder="Kosongkan jika tidak ingin mengganti API Key">
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleToken()">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <small style="color:#6b7280;font-size:0.72rem;">Kosongkan jika API Key tidak diubah</small>
                    @error('token_fonnte')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Status Device</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="aktif"    {{ old('status', $waSender->status) === 'aktif'    ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status', $waSender->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        <option value="terputus" {{ old('status', $waSender->status) === 'terputus' ? 'selected' : '' }}>Terputus</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,0.07);">
                <button type="submit" class="btn btn-warning px-4">
                    <i class="bi bi-check-lg me-1"></i>Perbarui WA Sender
                </button>
                <a href="{{ route('super-admin.wa-sender.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
            </div>
        </form>
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
