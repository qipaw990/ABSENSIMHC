@extends('layouts.app')

@section('title', 'Nilai & Evaluasi Pembelajaran')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title"><i class="bi bi-award me-2 text-primary"></i>Nilai & Evaluasi Pembelajaran</h1>
        <p class="page-subtitle">Daftar nilai tugas harian, ulangan, dan catatan/pujian dari guru pengampu.</p>
    </div>
</div>

<!-- Summary Card & Rata-rata Nilai -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-3 border border-emerald-500/30 bg-emerald-950/20 text-center">
            <div class="text-slate-400 small mb-1"><i class="bi bi-graph-up me-1"></i>Rata-Rata Nilai Keseluruhan</div>
            <div class="text-emerald-400 font-weight-bold" style="font-size: 2.2rem;">
                {{ number_format($rataRata, 1) }}
            </div>
            <div class="text-slate-500" style="font-size: 0.75rem;">Dari total {{ $nilaiList->count() }} evaluasi & tugas</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border border-blue-500/30 bg-blue-950/20 text-center">
            <div class="text-slate-400 small mb-1"><i class="bi bi-check-circle me-1"></i>Tugas Tuntas</div>
            <div class="text-blue-400 font-weight-bold" style="font-size: 2.2rem;">
                {{ $nilaiList->where('nilai', '>=', 75)->count() }}
            </div>
            <div class="text-slate-500" style="font-size: 0.75rem;">Nilai &ge; 75 (Lulus KKM)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 border border-amber-500/30 bg-amber-950/20 text-center">
            <div class="text-slate-400 small mb-1"><i class="bi bi-exclamation-circle me-1"></i>Perlu Perbaikan / Remidi</div>
            <div class="text-amber-400 font-weight-bold" style="font-size: 2.2rem;">
                {{ $nilaiList->where('nilai', '<', 75)->count() }}
            </div>
            <div class="text-slate-500" style="font-size: 0.75rem;">Perlu konsultasi dengan guru</div>
        </div>
    </div>
</div>

<!-- Search Bar -->
<div class="card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" action="{{ route('siswa.nilai.index') }}" class="row g-2 align-items-center">
            <div class="col-md-9">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari Mata Pelajaran, Bab Materi, atau Judul Tugas..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Cari</button>
                @if(request()->filled('search'))
                <a href="{{ route('siswa.nilai.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i> Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel Daftar Nilai -->
<div class="card mb-4">
    <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-journal-text me-2 text-primary"></i>Rincian Nilai & Catatan Guru ({{ $nilaiList->count() }})</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Mata Pelajaran & Bab</th>
                        <th>Judul Tugas</th>
                        <th>Jenis</th>
                        <th>Tanggal</th>
                        <th class="text-center">Nilai</th>
                        <th>Catatan Guru / Feedback</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nilaiList as $n)
                    <tr>
                        <td style="color:#6b7280;">{{ $loop->iteration }}</td>
                        <td>
                            <div style="font-weight:600;font-size:0.88rem;color:#f1f5f9;">{{ $n->tugasMateri->mata_pelajaran ?? '-' }}</div>
                            <div style="font-size:0.75rem;color:#94a3b8;"><i class="bi bi-book me-1"></i>{{ $n->tugasMateri->bab_materi ?? '-' }}</div>
                        </td>
                        <td style="font-size:0.85rem;color:#e2e8f0;">{{ $n->tugasMateri->judul_tugas ?? '-' }}</td>
                        <td><span class="badge bg-primary" style="font-size:0.75rem;">{{ $n->tugasMateri->jenis_label ?? 'Tugas' }}</span></td>
                        <td style="font-size:0.8rem;color:#94a3b8;">{{ $n->tugasMateri->tanggal ? $n->tugasMateri->tanggal->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">
                            <span class="font-weight-bold" style="font-size: 1.2rem; color: {{ $n->nilai >= 75 ? '#10b981' : ($n->nilai > 0 ? '#f59e0b' : '#6b7280') }};">
                                {{ number_format($n->nilai, 1) }}
                            </span>
                            <div>
                                @if($n->nilai >= 75)
                                <span class="badge bg-success" style="font-size:0.68rem;">Tuntas</span>
                                @elseif($n->nilai > 0)
                                <span class="badge bg-warning text-dark" style="font-size:0.68rem;">Remidi</span>
                                @else
                                <span class="badge bg-secondary" style="font-size:0.68rem;">Belum</span>
                                @endif
                            </div>
                        </td>
                        <td style="font-size:0.82rem;color:#cbd5e1;">
                            @if($n->catatan_guru)
                            <div class="p-2 rounded bg-slate-800 border border-slate-700">
                                <i class="bi bi-chat-left-text me-1 text-info"></i>{{ $n->catatan_guru }}
                            </div>
                            @else
                            <span class="text-slate-500">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada data nilai atau evaluasi pembelajaran untuk Anda.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
