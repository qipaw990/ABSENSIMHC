@extends('layouts.app')

@section('title', 'Ajukan Izin/Sakit')

@section('content')
<div class="page-header">
    <div><h1 class="page-title"><i class="bi bi-file-earmark-plus me-2 text-primary"></i>Ajukan Izin/Sakit</h1></div>
    <a href="{{ route('siswa.izin.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-body p-4">
        <form action="{{ route('siswa.izin.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Jenis Pengajuan <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <label style="cursor:pointer;display:flex;align-items:center;gap:0.5rem;padding:0.6rem 1.25rem;border:1px solid rgba(255,255,255,0.1);border-radius:10px;transition:all 0.2s;" id="lbl-izin">
                            <input type="radio" name="jenis" value="izin" {{ old('jenis') !== 'sakit' ? 'checked' : '' }} onchange="updateLabel()">
                            <span>📋 Izin</span>
                        </label>
                        <label style="cursor:pointer;display:flex;align-items:center;gap:0.5rem;padding:0.6rem 1.25rem;border:1px solid rgba(255,255,255,0.1);border-radius:10px;transition:all 0.2s;" id="lbl-sakit">
                            <input type="radio" name="jenis" value="sakit" {{ old('jenis') === 'sakit' ? 'checked' : '' }} onchange="updateLabel()">
                            <span>🤒 Sakit</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                        value="{{ old('tanggal_mulai', today()->toDateString()) }}"
                        min="{{ today()->toDateString() }}" required>
                    @error('tanggal_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror"
                        value="{{ old('tanggal_selesai', today()->toDateString()) }}"
                        min="{{ today()->toDateString() }}" required>
                    @error('tanggal_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Keterangan <span class="text-danger">*</span></label>
                    <textarea name="keterangan" rows="4" class="form-control @error('keterangan') is-invalid @enderror"
                        placeholder="Jelaskan alasan izin/sakit Anda..." required>{{ old('keterangan') }}</textarea>
                    @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Lampiran <small style="color:#6b7280;">(Surat dokter, surat izin orang tua, dll)</small></label>
                    <input type="file" name="lampiran" class="form-control @error('lampiran') is-invalid @enderror"
                        accept=".jpg,.jpeg,.png,.pdf">
                    <small style="color:#6b7280;font-size:0.72rem;">Format: JPG, PNG, PDF. Maks 5MB.</small>
                    @error('lampiran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,0.07);">
                <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:10px;padding:0.85rem 1rem;font-size:0.8rem;color:#94a3b8;margin-bottom:1.25rem;">
                    <i class="bi bi-info-circle me-1 text-warning"></i>
                    Pengajuan akan diproses oleh wali kelas Anda. Absensi akan otomatis diperbarui setelah disetujui.
                </div>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-send me-1"></i>Kirim Pengajuan</button>
                <a href="{{ route('siswa.izin.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateLabel() {
    const izinChecked = document.querySelector('input[value="izin"]').checked;
    document.getElementById('lbl-izin').style.borderColor = izinChecked ? '#6366f1' : 'rgba(255,255,255,0.1)';
    document.getElementById('lbl-izin').style.background   = izinChecked ? 'rgba(99,102,241,0.1)' : 'transparent';
    document.getElementById('lbl-sakit').style.borderColor = !izinChecked ? '#f59e0b' : 'rgba(255,255,255,0.1)';
    document.getElementById('lbl-sakit').style.background   = !izinChecked ? 'rgba(245,158,11,0.1)' : 'transparent';
}
updateLabel();
</script>
@endpush
