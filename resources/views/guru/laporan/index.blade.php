@extends('layouts.app')

@section('title', 'Laporan Absensi Harian')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Laporan Absensi Harian</h1>
        <p class="page-subtitle">Rekap matriks absensi harian per kelas (1 s/d {{ $jumlahHari }} {{ $tanggalMulai->translatedFormat('F Y') }})</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('guru.laporan.pdf', request()->all()) }}" class="btn btn-outline-danger btn-sm" target="_blank">
            <i class="bi bi-file-pdf me-1"></i>Export PDF
        </a>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body py-3 px-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-select form-select-sm">
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Bulan</label>
                <select name="bulan" class="form-select form-select-sm">
                    @foreach(range(1, 12) as $b)
                    <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tahun</label>
                <select name="tahun" class="form-select form-select-sm">
                    @foreach(range(now()->year - 2, now()->year + 1) as $t)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Keterangan Legend -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2" style="font-size:0.8rem;">
    <div>
        <span class="me-2" style="color:#94a3b8;">Keterangan Kode:</span>
        <span class="badge bg-success me-1">H = Hadir</span>
        <span class="badge bg-warning text-dark me-1">T = Terlambat</span>
        <span class="badge bg-info text-dark me-1">I = Izin</span>
        <span class="badge bg-primary me-1">S = Sakit</span>
        <span class="badge bg-danger me-1">A = Alpha</span>
        <span class="badge bg-dark border text-muted me-1">- = Libur/Kosong</span>
    </div>
    <div style="color:#94a3b8;">
        Kelas: <strong class="text-white">{{ $kelas->nama ?? '-' }}</strong> &bull; Periode: <strong class="text-white">{{ $tanggalMulai->translatedFormat('F Y') }}</strong>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 600px;">
            <table class="table table-bordered table-sm align-middle mb-0" style="font-size:0.78rem;">
                <thead class="table-dark sticky-top text-center">
                    <tr>
                        <th style="width:30px;" rowspan="2">#</th>
                        <th style="min-width:140px;" rowspan="2">Nama Siswa</th>
                        <th colspan="{{ $jumlahHari }}">Tanggal ({{ $tanggalMulai->translatedFormat('F Y') }})</th>
                        <th colspan="5">Rekap Total</th>
                        <th style="width:40px;" rowspan="2">%</th>
                    </tr>
                    <tr>
                        @for($d = 1; $d <= $jumlahHari; $d++)
                        @php
                            $currDate = \Carbon\Carbon::createFromDate($tanggalMulai->year, $tanggalMulai->month, $d);
                            $isSunday = $currDate->isSunday();
                        @endphp
                        <th style="width:28px;" class="{{ $isSunday ? 'bg-danger bg-opacity-25 text-danger-emphasis' : '' }}">
                            {{ $d }}
                        </th>
                        @endfor
                        <th style="color:#22c55e;width:30px;">H</th>
                        <th style="color:#f59e0b;width:30px;">T</th>
                        <th style="color:#3b82f6;width:30px;">I</th>
                        <th style="color:#8b5cf6;width:30px;">S</th>
                        <th style="color:#ef4444;width:30px;">A</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswaList as $item)
                    @php
                        $hadir  = $item['hadir'] ?? 0;
                        $telat  = $item['terlambat'] ?? 0;
                        $izin   = $item['izin'] ?? 0;
                        $sakit  = $item['sakit'] ?? 0;
                        $alpha  = $item['alpha'] ?? 0;
                        $totalRecorded = $hadir + $telat + $izin + $sakit + $alpha;
                        $pct    = $totalRecorded > 0 ? round((($hadir + $telat) / $totalRecorded) * 100) : 0;
                        $color  = $pct >= 80 ? '#22c55e' : ($pct >= 60 ? '#f59e0b' : '#ef4444');
                    @endphp
                    <tr>
                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                        <td>
                            <div style="font-weight:600;color:#e2e8f0;white-space:nowrap;">{{ $item['siswa']->nama }}</div>
                            <div style="font-size:0.7rem;color:#6b7280;">NIS: {{ $item['siswa']->nis }}</div>
                        </td>

                        {{-- Loop Tanggal 1 s/d jumlahHari --}}
                        @for($d = 1; $d <= $jumlahHari; $d++)
                        @php
                            $currDate = \Carbon\Carbon::createFromDate($tanggalMulai->year, $tanggalMulai->month, $d);
                            $isSunday = $currDate->isSunday();
                            $st = $item['daily'][$d] ?? null;
                            $badgeBg = match($st) {
                                'hadir'     => 'bg-success',
                                'terlambat' => 'bg-warning text-dark',
                                'izin'      => 'bg-info text-dark',
                                'sakit'     => 'bg-primary',
                                'alpha'     => 'bg-danger',
                                default     => '',
                            };
                            $code = match($st) {
                                'hadir'     => 'H',
                                'terlambat' => 'T',
                                'izin'      => 'I',
                                'sakit'     => 'S',
                                'alpha'     => 'A',
                                default     => '-',
                            };
                        @endphp
                        <td class="text-center {{ $isSunday ? 'bg-danger bg-opacity-10' : '' }}">
                            @if($st)
                            <span class="badge {{ $badgeBg }}" style="font-size:0.7rem;padding:2px 4px;">{{ $code }}</span>
                            @else
                            <span style="color:#475569;">-</span>
                            @endif
                        </td>
                        @endfor

                        <td class="text-center fw-bold text-success">{{ $hadir }}</td>
                        <td class="text-center fw-bold text-warning">{{ $telat }}</td>
                        <td class="text-center fw-bold text-info">{{ $izin }}</td>
                        <td class="text-center fw-bold" style="color:#8b5cf6;">{{ $sakit }}</td>
                        <td class="text-center fw-bold text-danger">{{ $alpha }}</td>
                        <td class="text-center fw-bold" style="color:{{ $color }};">{{ $pct }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $jumlahHari + 9 }}" class="text-center py-4 text-muted">
                            Tidak ada data siswa untuk kelas ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
