@extends('layouts.app')

@section('title', 'Log Pengiriman WA')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-journal-text me-2 text-primary"></i>Log Pengiriman WhatsApp</h1>
        <p class="page-subtitle">Riwayat semua notifikasi yang dikirim ke orang tua/wali</p>
    </div>
</div>

<!-- Stat Mini -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div style="font-size:1.6rem;font-weight:700;color:#22c55e;">{{ $stats->get('terkirim', 0) }}</div>
            <div style="font-size:0.72rem;color:#6b7280;">Terkirim</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div style="font-size:1.6rem;font-weight:700;color:#ef4444;">{{ $stats->get('gagal', 0) }}</div>
            <div style="font-size:0.72rem;color:#6b7280;">Gagal</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div style="font-size:1.6rem;font-weight:700;color:#f59e0b;">{{ $stats->get('pending', 0) }}</div>
            <div style="font-size:0.72rem;color:#6b7280;">Pending</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-3">
            <div style="font-size:1.6rem;font-weight:700;color:#e2e8f0;">{{ $waLogs->total() }}</div>
            <div style="font-size:0.72rem;color:#6b7280;">Total Log</div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body py-3 px-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="terkirim" {{ request('status') == 'terkirim' ? 'selected' : '' }}>Terkirim</option>
                    <option value="gagal"    {{ request('status') == 'gagal'    ? 'selected' : '' }}>Gagal</option>
                    <option value="pending"  {{ request('status') == 'pending'  ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control form-select-sm" value="{{ request('tanggal') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Siswa</th>
                        <th>Target WA</th>
                        <th>Template</th>
                        <th>Sender</th>
                        <th>Status</th>
                        <th>Pesan Error</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($waLogs as $log)
                    <tr>
                        <td style="font-size:0.78rem;color:#6b7280;white-space:nowrap;">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div style="font-size:0.82rem;font-weight:600;">{{ $log->absensi->siswa->nama ?? '-' }}</div>
                            <div style="font-size:0.7rem;color:#6b7280;">{{ $log->absensi->siswa->kelas->nama ?? '' }}</div>
                        </td>
                        <td style="font-family:monospace;font-size:0.78rem;color:#94a3b8;">{{ $log->target }}</td>
                        <td style="font-size:0.75rem;color:#6b7280;">{{ $log->template_kode ?? '-' }}</td>
                        <td style="font-size:0.75rem;color:#6b7280;">{{ $log->waSender->nama_device ?? 'Global' }}</td>
                        <td>
                            @if($log->status === 'terkirim')
                                <span class="badge bg-success">✓ Terkirim</span>
                            @elseif($log->status === 'gagal')
                                <span class="badge bg-danger">✗ Gagal</span>
                            @else
                                <span class="badge bg-warning text-dark">⏳ Pending</span>
                            @endif
                        </td>
                        <td style="font-size:0.72rem;color:#ef4444;max-width:200px;">
                            {{ $log->pesan_error ?? '' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color:#6b7280;">
                            <i class="bi bi-journal-x" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                            Belum ada log pengiriman.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($waLogs->hasPages())
    <div class="card-footer px-4 py-3" style="background:transparent;border-top:1px solid var(--border-color);">
        {{ $waLogs->links() }}
    </div>
    @endif
</div>
@endsection
