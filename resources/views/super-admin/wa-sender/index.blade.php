@extends('layouts.app')

@section('title', 'WA Sender')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-whatsapp me-2" style="color:#25d366;"></i>WA Sender per Kelas</h1>
        <p class="page-subtitle">Kelola akun WhatsApp pengirim notifikasi per kelas</p>
    </div>
    <a href="{{ route('super-admin.wa-sender.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Tambah Sender
    </a>
</div>

<!-- Info Box -->
<div style="background:rgba(37,211,102,0.06);border:1px solid rgba(37,211,102,0.2);border-radius:12px;padding:0.85rem 1.2rem;margin-bottom:1.5rem;font-size:0.82rem;color:#94a3b8;">
    <i class="bi bi-info-circle me-1" style="color:#25d366;"></i>
    <strong style="color:#e2e8f0;">Multi-Sender per Kelas (Custom WhatsApp Gateway):</strong>
    Setiap kelas terhubung ke WhatsApp Gateway resmi sekolah (<a href="https://api-gateway.smkmuthiaharapanclk.com" target="_blank" style="color:#25d366;">api-gateway.smkmuthiaharapanclk.com</a>).
    Anda dapat mengisi API Key khusus per project atau membiarkannya menggunakan Default Global API Key dari sistem.
</div>

<div class="row g-3">
    @forelse($waSenders as $sender)
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div style="font-size:0.7rem;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Kelas</div>
                        <div style="font-weight:700;font-size:1rem;color:#e2e8f0;">{{ $sender->kelas->nama ?? 'Global' }}</div>
                        <div style="font-size:0.75rem;color:#6b7280;">{{ $sender->kelas->jurusan->nama ?? '' }}</div>
                    </div>
                    <span class="badge px-3 py-2" style="font-size:0.75rem;
                        background: {{ $sender->status === 'aktif' ? 'rgba(34,197,94,0.15)' : ($sender->status === 'terputus' ? 'rgba(239,68,68,0.15)' : 'rgba(107,114,128,0.15)') }};
                        color: {{ $sender->status === 'aktif' ? '#22c55e' : ($sender->status === 'terputus' ? '#ef4444' : '#6b7280') }};
                        border: 1px solid {{ $sender->status === 'aktif' ? 'rgba(34,197,94,0.3)' : ($sender->status === 'terputus' ? 'rgba(239,68,68,0.3)' : 'rgba(107,114,128,0.3)') }};">
                        <span class="wa-status-dot {{ $sender->status }}"></span>{{ ucfirst($sender->status) }}
                    </span>
                </div>

                <div class="mb-3">
                    <div style="font-size:0.72rem;color:#6b7280;">Nama Device</div>
                    <div style="font-size:0.85rem;font-weight:500;color:#e2e8f0;">{{ $sender->nama_device }}</div>
                </div>

                @if($sender->nomor_wa)
                <div class="mb-3">
                    <div style="font-size:0.72rem;color:#6b7280;">Nomor WA</div>
                    <div style="font-size:0.85rem;font-family:monospace;color:#25d366;">{{ $sender->nomor_wa }}</div>
                </div>
                @endif

                <div class="mb-3">
                    <div style="font-size:0.72rem;color:#6b7280;">API Key Gateway</div>
                    <div style="font-size:0.75rem;font-family:monospace;color:#6b7280;word-break:break-all;">
                        ••••••{{ substr($sender->token_fonnte_plain ?? '????', -6) }}
                    </div>
                </div>

                @if($sender->last_check_at)
                <div class="mb-3" style="font-size:0.72rem;color:#6b7280;">
                    <i class="bi bi-clock me-1"></i>Dicek {{ $sender->last_check_at->diffForHumans() }}
                </div>
                @endif

                <div class="d-flex gap-1 mt-2">
                    <form action="{{ route('super-admin.wa-sender.cek-status', $sender) }}" method="POST" class="flex-fill">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success w-100">
                            <i class="bi bi-wifi me-1"></i>Cek Status
                        </button>
                    </form>
                    <a href="{{ route('super-admin.wa-sender.edit', $sender) }}" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('super-admin.wa-sender.destroy', $sender) }}" method="POST"
                        onsubmit="return confirm('Hapus WA Sender ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </div>

                <!-- Test Kirim WA -->
                <div class="mt-2">
                    <button class="btn btn-sm btn-outline-secondary w-100" data-bs-toggle="collapse"
                        data-bs-target="#testWa{{ $sender->id }}">
                        <i class="bi bi-send me-1"></i>Test Kirim WA
                    </button>
                    <div class="collapse mt-2" id="testWa{{ $sender->id }}">
                        <form action="{{ route('super-admin.wa-sender.test', $sender) }}" method="POST">
                            @csrf
                            <div class="mb-1">
                                <input type="text" name="target_nomor" class="form-control form-control-sm"
                                    placeholder="Nomor tujuan: 628xxxxxxxxx" required>
                            </div>
                            <div class="mb-1">
                                <textarea name="pesan" class="form-control form-control-sm" rows="2"
                                    placeholder="Isi pesan uji coba..." required>Halo! Ini pesan test dari Sistem Absensi QR SMK. ✅</textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-success w-100">
                                <i class="bi bi-send me-1"></i>Kirim Test
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card text-center py-5">
            <div style="font-size:3rem;margin-bottom:1rem;">📱</div>
            <h5 style="color:#e2e8f0;">Belum Ada WA Sender</h5>
            <p style="color:#6b7280;font-size:0.85rem;">Tambahkan WA Sender untuk setiap kelas agar notifikasi absensi dapat terkirim.</p>
            <div>
                <a href="{{ route('super-admin.wa-sender.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Tambah WA Sender
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection
