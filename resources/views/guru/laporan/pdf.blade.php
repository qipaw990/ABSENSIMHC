<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Absensi Harian — {{ $kelas->nama ?? '' }}</title>
<style>
    @page {
        margin: 6mm 8mm 6mm 8mm;
    }
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 7.5pt;
        color: #0f172a;
        line-height: 1.1;
    }
    
    /* Header Kop */
    .kop-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 3px;
    }
    .kop-logo {
        width: 18mm;
        vertical-align: middle;
        text-align: center;
        padding-right: 4mm;
    }
    .kop-logo img {
        width: 16mm;
        height: 16mm;
        object-fit: contain;
    }
    .kop-info {
        vertical-align: middle;
        text-align: center;
    }
    .kop-sekolah {
        font-size: 13pt;
        font-weight: 900;
        text-transform: uppercase;
        color: #0f172a;
        letter-spacing: 0.5pt;
    }
    .kop-alamat {
        font-size: 7.5pt;
        color: #475569;
        margin-top: 1px;
    }
    .kop-divider {
        border: none;
        border-top: 3px solid #0f172a;
        border-bottom: 1px solid #0f172a;
        height: 2px;
        margin: 3px 0 5px 0;
    }
    .doc-title {
        font-size: 10pt;
        font-weight: bold;
        text-transform: uppercase;
        color: #0f172a;
        text-align: center;
        margin-bottom: 1px;
    }
    .doc-subtitle {
        font-size: 8pt;
        color: #334155;
        text-align: center;
        margin-bottom: 4px;
    }

    /* Info Table */
    .meta-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 5px;
    }
    .meta-table td {
        border: none;
        padding: 1px 0;
        font-size: 7.5pt;
        vertical-align: top;
    }
    .meta-label { color: #64748b; width: 10%; }
    .meta-colon { width: 1.5%; color: #64748b; }
    .meta-value { font-weight: bold; color: #0f172a; width: 38.5%; }

    /* Legend Bar */
    .legend-bar {
        font-size: 7pt;
        margin-bottom: 5px;
        color: #475569;
    }

    /* Main Data Table */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
    }
    .data-table th, .data-table td {
        border: 0.5px solid #64748b;
        padding: 2px 1px;
        font-size: 6.8pt;
        text-align: center;
    }
    .data-table th {
        background-color: #f1f5f9;
        color: #0f172a;
        font-weight: bold;
        font-size: 6.5pt;
    }
    .data-table th.weekend {
        background-color: #fecdd3;
        color: #9f1239;
    }
    .data-table td.weekend {
        background-color: #fff1f2;
    }
    .data-table tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }
    .text-left { text-align: left !important; padding-left: 3px !important; }

    /* Status Codes */
    .code-h { color: #16a34a; font-weight: bold; }
    .code-t { color: #d97706; font-weight: bold; }
    .code-i { color: #2563eb; font-weight: bold; }
    .code-s { color: #7c3aed; font-weight: bold; }
    .code-a { color: #dc2626; font-weight: bold; }
    .code-empty { color: #cbd5e1; }

    /* Signature Section */
    .ttd-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
        page-break-inside: avoid;
    }
    .ttd-table td {
        border: none;
        padding: 0;
        vertical-align: top;
        text-align: center;
        font-size: 7.5pt;
    }

    .footer-note {
        margin-top: 4px;
        font-size: 6.5pt;
        color: #94a3b8;
        text-align: right;
        border-top: 0.5px solid #cbd5e1;
        padding-top: 2px;
    }
</style>
</head>
<body>

{{-- KOP SURAT RESMI --}}
<table class="kop-table">
    <tr>
        <td class="kop-logo">
            <img src="{{ public_path('images/logo-smk.png') }}" alt="Logo SMK MHC">
        </td>
        <td class="kop-info">
            <div class="kop-sekolah">{{ config('app.nama_sekolah', 'SMK Muthia Harapan Cicalengka') }}</div>
            <div class="kop-alamat">Jl. Cicalengka &bull; Kabupaten Bandung &bull; Jawa Barat</div>
        </td>
        <td style="width:18mm;"></td>
    </tr>
</table>

<div class="kop-divider"></div>

<div class="doc-title">LAPORAN REKAPITULASI ABSENSI HARIAN SISWA</div>
<div class="doc-subtitle">Periode: {{ $tanggalMulai->translatedFormat('F Y') }}</div>

<table class="meta-table">
    <tr>
        <td class="meta-label">Kelas</td>
        <td class="meta-colon">:</td>
        <td class="meta-value">{{ $kelas->nama ?? '-' }} ({{ $kelas->jurusan->nama ?? '-' }})</td>
        <td class="meta-label">Total Siswa</td>
        <td class="meta-colon">:</td>
        <td class="meta-value">{{ count($siswaList) }} Siswa</td>
    </tr>
    <tr>
        <td class="meta-label">Wali Kelas</td>
        <td class="meta-colon">:</td>
        <td class="meta-value">{{ $kelas->waliKelas->nama ?? '-' }}</td>
        <td class="meta-label">Tgl Cetak</td>
        <td class="meta-colon">:</td>
        <td class="meta-value">{{ now()->translatedFormat('d F Y, H:i') }} WIB</td>
    </tr>
</table>

<!-- Keterangan Kode -->
<div class="legend-bar">
    <strong>Keterangan:</strong>
    <span class="code-h">H</span> = Hadir &bull;
    <span class="code-t">T</span> = Terlambat &bull;
    <span class="code-i">I</span> = Izin &bull;
    <span class="code-s">S</span> = Sakit &bull;
    <span class="code-a">A</span> = Alpha &bull;
    <span style="color:#94a3b8;">-</span> = Belum Absen / Libur
</div>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 14px;" rowspan="2">#</th>
            <th style="width: 50px;" rowspan="2">NIS</th>
            <th style="width: 110px;" rowspan="2">Nama Siswa</th>
            <th colspan="{{ $jumlahHari }}">Tanggal ({{ $tanggalMulai->translatedFormat('F Y') }})</th>
            <th colspan="5">Rekap</th>
            <th style="width: 24px;" rowspan="2">%</th>
        </tr>
        <tr>
            @for($d = 1; $d <= $jumlahHari; $d++)
            @php
                $currDate = \Carbon\Carbon::createFromDate($tanggalMulai->year, $tanggalMulai->month, $d);
                $isSunday = $currDate->isSunday();
            @endphp
            <th style="width: 14px;" class="{{ $isSunday ? 'weekend' : '' }}">{{ $d }}</th>
            @endfor
            <th style="width: 15px;" class="code-h">H</th>
            <th style="width: 15px;" class="code-t">T</th>
            <th style="width: 15px;" class="code-i">I</th>
            <th style="width: 15px;" class="code-s">S</th>
            <th style="width: 15px;" class="code-a">A</th>
        </tr>
    </thead>
    <tbody>
        @foreach($siswaList as $item)
        @php
            $hadir  = $item['hadir'] ?? 0;
            $telat  = $item['terlambat'] ?? 0;
            $izin   = $item['izin'] ?? 0;
            $sakit  = $item['sakit'] ?? 0;
            $alpha  = $item['alpha'] ?? 0;
            $totalRecorded = $hadir + $telat + $izin + $sakit + $alpha;
            $pct    = $totalRecorded > 0 ? round((($hadir + $telat) / $totalRecorded) * 100) : 0;
            $cls    = $pct >= 80 ? 'code-h' : ($pct >= 60 ? 'code-t' : 'code-a');
        @endphp
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item['siswa']->nis }}</td>
            <td class="text-left">{{ $item['siswa']->nama }}</td>
            
            {{-- Tanggal 1..jumlahHari --}}
            @for($d = 1; $d <= $jumlahHari; $d++)
            @php
                $currDate = \Carbon\Carbon::createFromDate($tanggalMulai->year, $tanggalMulai->month, $d);
                $isSunday = $currDate->isSunday();
                $st = $item['daily'][$d] ?? null;
                $code = match($st) {
                    'hadir'     => 'H',
                    'terlambat' => 'T',
                    'izin'      => 'I',
                    'sakit'     => 'S',
                    'alpha'     => 'A',
                    default     => '-',
                };
                $codeClass = match($st) {
                    'hadir'     => 'code-h',
                    'terlambat' => 'code-t',
                    'izin'      => 'code-i',
                    'sakit'     => 'code-s',
                    'alpha'     => 'code-a',
                    default     => 'code-empty',
                };
            @endphp
            <td class="{{ $isSunday ? 'weekend' : '' }} {{ $codeClass }}">
                {{ $code }}
            </td>
            @endfor

            {{-- Total Rekap --}}
            <td class="code-h">{{ $hadir }}</td>
            <td class="code-t">{{ $telat }}</td>
            <td class="code-i">{{ $izin }}</td>
            <td class="code-s">{{ $sakit }}</td>
            <td class="code-a">{{ $alpha }}</td>
            <td class="{{ $cls }}">{{ $pct }}%</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="ttd-table">
    <tr>
        <td style="width: 75%;"></td>
        <td style="width: 25%;">
            <p>Mengetahui,</p>
            <p>Wali Kelas {{ $kelas->nama ?? '' }}</p>
            <br><br>
            <p style="font-weight: bold; text-decoration: underline;">{{ $kelas->waliKelas->nama ?? '_______________________' }}</p>
            @if(isset($kelas->waliKelas->nip))
            <p style="font-size: 7pt; color: #64748b;">NIP. {{ $kelas->waliKelas->nip }}</p>
            @endif
        </td>
    </tr>
</table>

<div class="footer-note">
    Dicetak otomatis oleh Sistem Absensi QR — {{ config('app.nama_sekolah', 'SMK') }} pada {{ now()->translatedFormat('d F Y H:i') }} WIB
</div>

</body>
</html>
