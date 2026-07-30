@extends('layouts.app')

@section('title', 'Detail Siswa — ' . $siswa->nama)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Detail Siswa</h1>
        <p class="page-subtitle">{{ $siswa->nama }} — {{ $siswa->kelas->nama ?? '-' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.siswa.edit', $siswa) }}" class="btn btn-outline-warning btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <a href="{{ route('admin.siswa.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Info Siswa + QR -->
    <div class="col-12 col-md-4">
        <div class="card text-center p-4">
            <img src="{{ $siswa->foto_url }}" alt="{{ $siswa->nama }}"
                style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid rgba(99,102,241,0.4);margin:0 auto 1rem;">
            <h5 style="font-weight:700;margin-bottom:0.25rem;">{{ $siswa->nama }}</h5>
            <div style="color:#6b7280;font-size:0.82rem;margin-bottom:1rem;">
                {{ $siswa->kelas->nama ?? '-' }} &bull; NIS: {{ $siswa->nis }}
            </div>

            <!-- QR CODE -->
            <div style="background:white;border-radius:12px;padding:16px;display:inline-block;margin:0 auto 1rem;">
                {{-- $qrBase64 sudah berisi full data URI (svg/png) dari controller --}}
                <img src="{{ $qrBase64 }}" alt="QR Code"
                    style="width:180px;height:180px;display:block;">
            </div>

            <div style="font-size:0.72rem;color:#6b7280;margin-bottom:1rem;font-family:monospace;">
                Token: {{ substr($siswa->qr_token, 0, 16) }}...
            </div>

            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('admin.siswa.kartu-qr', $siswa) }}"
                    class="btn btn-primary btn-sm" target="_blank">
                    <i class="bi bi-printer me-1"></i>Cetak Kartu
                </a>
                <form action="{{ route('admin.siswa.regenerate-qr', $siswa) }}" method="POST"
                    onsubmit="return confirm('QR lama akan nonaktif. Lanjutkan?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-arrow-repeat me-1"></i>Regenerate QR
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Detail + Riwayat -->
    <div class="col-12 col-md-8">
        <div class="card mb-3">
            <div class="card-header px-4 py-3">
                <i class="bi bi-person-vcard me-2 text-primary"></i>Informasi Lengkap
            </div>
            <div class="card-body px-4 py-3">
                <div class="row g-3">
                    <div class="col-6">
                        <div style="font-size:0.72rem;color:#6b7280;">NIS</div>
                        <div style="font-weight:600;font-family:monospace;">{{ $siswa->nis }}</div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:0.72rem;color:#6b7280;">NISN</div>
                        <div style="font-weight:600;font-family:monospace;">{{ $siswa->nisn ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:0.72rem;color:#6b7280;">Kelas</div>
                        <div style="font-weight:600;">{{ $siswa->kelas->nama ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:0.72rem;color:#6b7280;">Jurusan</div>
                        <div style="font-weight:600;">{{ $siswa->kelas->jurusan->nama ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:0.72rem;color:#6b7280;">Nama Orang Tua</div>
                        <div style="font-weight:600;">{{ $siswa->nama_ortu ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:0.72rem;color:#6b7280;">WA Orang Tua</div>
                        <div style="font-weight:600;color:#25d366;">{{ $siswa->no_wa_ortu ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:0.72rem;color:#6b7280;">Status QR</div>
                        <div>
                            @if($siswa->qr_is_active)
                                <span class="badge bg-success">QR Aktif</span>
                            @else
                                <span class="badge bg-danger">QR Nonaktif</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:0.72rem;color:#6b7280;">Akun Login</div>
                        <div style="font-size:0.82rem;color:#94a3b8;">{{ $siswa->user->email ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat 30 Absensi Terakhir -->
        <div class="card">
            <div class="card-header px-4 py-3">
                <i class="bi bi-calendar-check me-2 text-primary"></i>30 Absensi Terakhir
            </div>
            <div class="card-body p-0" style="max-height:300px;overflow-y:auto;">
                <table class="table mb-0">
                    <thead><tr><th>Tanggal</th><th>Jam</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($siswa->absensi as $abs)
                        <tr>
                            <td style="font-size:0.82rem;">{{ $abs->tanggal->format('d/m/Y') }}</td>
                            <td style="font-size:0.82rem;color:#94a3b8;">{{ $abs->jam_scan ?? '-' }}</td>
                            <td>{!! $abs->status_badge !!}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-3" style="color:#6b7280;">Belum ada data absensi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
