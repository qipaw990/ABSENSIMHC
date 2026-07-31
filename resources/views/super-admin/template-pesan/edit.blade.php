@extends('layouts.app')

@section('title', 'Edit Template — ' . $templatePesan->kode)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-pencil me-2 text-warning"></i>Edit Template Pesan</h1>
        <p class="page-subtitle">{{ $templatePesan->judul }}</p>
    </div>
    <a href="{{ route('super-admin.template-pesan.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('super-admin.template-pesan.update', $templatePesan) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Kode Template</label>
                        <input type="text" class="form-control" value="{{ $templatePesan->kode }}" disabled
                            style="font-family:monospace;background:rgba(255,255,255,0.03);">
                        <small style="color:#6b7280;font-size:0.72rem;">Kode tidak bisa diubah</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Judul Template <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control"
                            value="{{ old('judul', $templatePesan->judul) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Isi Pesan <span class="text-danger">*</span></label>
                        <textarea name="template" id="templateText" class="form-control" rows="10"
                            style="font-family:monospace;resize:vertical;" required>{{ old('template', $templatePesan->template) }}</textarea>
                        <small style="color:#6b7280;font-size:0.72rem;">Klik placeholder di kanan untuk menyisipkan ke cursor</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-flex align-items-center gap-2">
                            <input type="checkbox" name="is_aktif" value="1" {{ $templatePesan->is_aktif ? 'checked' : '' }}>
                            Aktifkan template ini
                        </label>
                    </div>

                    <button type="submit" class="btn btn-warning px-4"><i class="bi bi-check-lg me-1"></i>Simpan Template</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar: Placeholder + Preview -->
    <div class="col-12 col-lg-5">
        <div class="card mb-3">
            <div class="card-header px-4 py-3"><i class="bi bi-braces me-2 text-primary"></i>Placeholder Tersedia</div>
            <div class="card-body p-3">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($placeholders as $ph)
                    <button type="button" class="btn btn-sm btn-outline-primary"
                        onclick="insertPlaceholder('{{ $ph }}')" style="font-family:monospace;font-size:0.75rem;">
                        {{ $ph }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header px-4 py-3 d-flex justify-content-between">
                <span><i class="bi bi-eye me-2 text-primary"></i>Preview Live</span>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="loadPreview()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                </button>
            </div>
            <div class="card-body p-4">
                <div style="background:rgba(37,211,102,0.05);border:1px solid rgba(37,211,102,0.15);border-radius:10px;padding:1rem;">
                    <div style="font-size:0.7rem;color:#6b7280;margin-bottom:0.5rem;">
                        <i class="bi bi-whatsapp me-1" style="color:#25d366;"></i>Preview (data dummy)
                    </div>
                    <div id="previewBox" style="font-size:0.85rem;color:#e2e8f0;white-space:pre-wrap;line-height:1.6;min-height:100px;">
                        Klik Refresh untuk preview...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const textarea = document.getElementById('templateText');

function insertPlaceholder(text) {
    const start = textarea.selectionStart;
    const end   = textarea.selectionEnd;
    const value = textarea.value;
    textarea.value = value.substring(0, start) + text + value.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + text.length;
    textarea.focus();
}

function loadPreview() {
    fetch('{{ route('super-admin.template-pesan.preview', $templatePesan, false) }}')
        .then(r => r.json())
        .then(d => { document.getElementById('previewBox').textContent = d.preview; })
        .catch(() => { document.getElementById('previewBox').textContent = 'Gagal memuat preview.'; });
}
</script>
@endpush
