@extends('layouts.app')

@section('title', 'Template Pesan WA')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-chat-quote me-2 text-primary"></i>Template Pesan WhatsApp</h1>
        <p class="page-subtitle">Kelola template notifikasi yang dikirim ke orang tua/wali</p>
    </div>
</div>

<div style="background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.2);border-radius:12px;padding:0.85rem 1.2rem;margin-bottom:1.5rem;font-size:0.82rem;color:#94a3b8;">
    <i class="bi bi-info-circle me-1 text-primary"></i>
    <strong style="color:#e2e8f0;">Placeholder tersedia:</strong>
    @foreach(['{nama_siswa}','{nama_ortu}','{jam}','{tanggal}','{status}','{kelas}','{nama_sekolah}','{keterangan}'] as $ph)
    <code style="background:rgba(99,102,241,0.15);padding:2px 6px;border-radius:4px;font-size:0.78rem;margin:2px;">{{ $ph }}</code>
    @endforeach
</div>

<div class="row g-3">
    @foreach($templates as $tpl)
    @php
        // Preview dengan data dummy — dirender di server, tidak perlu AJAX
        $dummyData = [
            'nama_siswa'   => 'Ahmad Rizky Pratama',
            'nama_ortu'    => 'Bapak Rizky',
            'jam'          => '07:05',
            'tanggal'      => now()->translatedFormat('l, d F Y'),
            'status'       => strtoupper($tpl->kode),
            'nama_sekolah' => config('app.nama_sekolah', 'SMK'),
            'kelas'        => 'XII RPL 1',
            'keterangan'   => 'Sakit demam',
        ];
        $previewText = $tpl->render($dummyData);
    @endphp
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-dark border me-2" style="border-color:var(--border-color)!important;font-family:monospace;">{{ $tpl->kode }}</span>
                    <strong style="font-size:0.88rem;">{{ $tpl->judul }}</strong>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge {{ $tpl->is_aktif ? 'bg-success' : 'bg-secondary' }}" style="font-size:0.7rem;">
                        {{ $tpl->is_aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    <a href="{{ route('super-admin.template-pesan.edit', $tpl) }}"
                        class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                {{-- Template raw --}}
                <pre style="background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.05);border-radius:10px;padding:1rem;font-size:0.8rem;color:#94a3b8;white-space:pre-wrap;word-break:break-word;font-family:'Inter',sans-serif;max-height:180px;overflow-y:auto;">{{ $tpl->template }}</pre>

                {{-- Preview toggle --}}
                <button class="btn btn-sm btn-outline-primary mt-2"
                    onclick="togglePreview({{ $tpl->id }})"
                    id="btn-preview-{{ $tpl->id }}">
                    <i class="bi bi-eye me-1"></i>Lihat Preview
                </button>

                {{-- Preview box (server-rendered, hidden by default) --}}
                <div id="preview-{{ $tpl->id }}" style="display:none;margin-top:0.75rem;">
                    <div style="background:rgba(37,211,102,0.06);border:1px solid rgba(37,211,102,0.2);border-radius:10px;padding:1rem;">
                        <div style="font-size:0.72rem;color:#6b7280;margin-bottom:0.5rem;">
                            <i class="bi bi-whatsapp me-1" style="color:#25d366;"></i>Preview dengan data dummy
                        </div>
                        <div style="font-size:0.85rem;color:#e2e8f0;white-space:pre-wrap;line-height:1.65;font-family:'Inter',sans-serif;">{{ $previewText }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
function togglePreview(id) {
    const box = document.getElementById('preview-' + id);
    const btn = document.getElementById('btn-preview-' + id);
    const isHidden = box.style.display === 'none';
    box.style.display = isHidden ? 'block' : 'none';
    btn.innerHTML = isHidden
        ? '<i class="bi bi-eye-slash me-1"></i>Sembunyikan Preview'
        : '<i class="bi bi-eye me-1"></i>Lihat Preview';
}
</script>
@endpush
