<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kartu QR Batch — {{ $kelas->nama }}</title>
<style>
    @page { margin: 10mm 10mm 10mm 10mm; }
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: Arial, Helvetica, sans-serif;
        background-color: #f5f5f0;
    }

    /* === PAGE HEADER === */
    .page-header {
        width: 100%;
        margin-bottom: 6mm;
    }

    .page-header-table {
        width: 100%;
        border-collapse: collapse;
        border-bottom: 3px solid #F5C800;
        padding-bottom: 3mm;
    }

    .ph-logo-td {
        width: 20mm;
        text-align: center;
        vertical-align: middle;
        padding-right: 4mm;
    }

    .ph-logo-td img {
        width: 18mm;
        height: 18mm;
    }

    .ph-info-td {
        vertical-align: middle;
    }

    .ph-school {
        font-size: 14pt;
        font-weight: 900;
        color: #1a1a2e;
        text-transform: uppercase;
        letter-spacing: 0.5pt;
    }

    .ph-sub {
        font-size: 9pt;
        color: #4a3800;
        font-weight: 600;
        margin-top: 1mm;
    }

    .ph-meta {
        font-size: 7.5pt;
        color: #666;
        margin-top: 1.5mm;
    }

    /* === GRID KARTU === */
    /* Dompdf tidak support flexbox — gunakan table */
    .cards-row {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 4mm;
    }

    .card-cell {
        padding: 2mm;
        vertical-align: top;
        width: 50%;
    }

    /* === KARTU INDIVIDUAL === */
    .kartu {
        width: 85.6mm;
        border: 1.5px solid #F5C800;
        background-color: #1a1a2e;
        page-break-inside: avoid;
    }

    /* Header kartu */
    .k-header {
        background-color: #F5C800;
        height: 11mm;
    }

    .k-header-table {
        width: 100%;
        height: 11mm;
        border-collapse: collapse;
    }

    .k-logo-td {
        width: 11mm;
        text-align: center;
        vertical-align: middle;
        padding-left: 1.5mm;
    }

    .k-logo-td img {
        width: 9mm;
        height: 9mm;
    }

    .k-school-td {
        vertical-align: middle;
        padding-left: 1.5mm;
    }

    .k-school {
        font-size: 5.5pt;
        font-weight: 900;
        color: #1a1a2e;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .k-school-sub {
        font-size: 3.5pt;
        color: #3a2e00;
        margin-top: 0.3mm;
    }

    .k-badge-td {
        width: 15mm;
        text-align: center;
        vertical-align: middle;
        padding-right: 1.5mm;
    }

    .k-badge {
        background-color: #1a1a2e;
        color: #F5C800;
        font-size: 4pt;
        font-weight: 900;
        padding: 1.5px 4px;
        letter-spacing: 0.4pt;
        text-transform: uppercase;
        border-radius: 2px;
    }

    /* Body kartu */
    .k-body-table {
        width: 100%;
        border-collapse: collapse;
    }

    .k-accent-td {
        width: 3px;
        background-color: #F5C800;
        padding: 0;
    }

    .k-info-td {
        vertical-align: top;
        padding: 2mm 2.5mm 2mm 2.5mm;
        width: 55%;
    }

    .k-nama {
        font-size: 7pt;
        font-weight: 900;
        color: #F5C800;
        text-transform: uppercase;
        margin-bottom: 2mm;
        line-height: 1.2;
    }

    .k-rows {
        border-collapse: collapse;
    }

    .k-lbl {
        font-size: 4.5pt;
        color: #aaa;
        padding-right: 1.5mm;
        white-space: nowrap;
        padding-bottom: 0.8mm;
        vertical-align: top;
    }

    .k-sep {
        font-size: 4.5pt;
        color: #F5C800;
        padding-right: 1mm;
        vertical-align: top;
        padding-bottom: 0.8mm;
    }

    .k-val {
        font-size: 5pt;
        color: #ffffff;
        font-weight: 700;
        vertical-align: top;
        padding-bottom: 0.8mm;
    }

    .k-val-sm {
        font-size: 4.5pt;
        color: #cccccc;
        font-weight: 400;
        vertical-align: top;
        padding-bottom: 0.8mm;
    }

    .k-status {
        background-color: #155724;
        color: #90ee90;
        font-size: 4pt;
        font-weight: 700;
        padding: 1px 4px;
        border-radius: 8px;
        border: 0.5px solid #90ee90;
    }

    .k-sep-td {
        width: 0.5mm;
        background-color: rgba(245,200,0,0.3);
        padding: 0;
    }

    .k-qr-td {
        vertical-align: middle;
        text-align: center;
        padding: 1.5mm 2mm;
    }

    .k-qr-wrap {
        background-color: #ffffff;
        padding: 2px;
        border: 1.5px solid #F5C800;
        display: inline-block;
    }

    .k-qr-wrap img {
        width: 33mm;
        height: 33mm;
        display: block;
    }

    .k-qr-label {
        font-size: 4pt;
        color: #F5C800;
        text-align: center;
        margin-top: 0.8mm;
        font-weight: 700;
        letter-spacing: 0.2pt;
    }

    /* Footer kartu */
    .k-footer {
        background-color: #0d0d1a;
        border-top: 1px solid #F5C800;
        padding: 1mm 2.5mm;
    }

    .k-footer-table {
        width: 100%;
        border-collapse: collapse;
    }

    .k-footer-left {
        font-size: 4pt;
        color: #aaa;
        vertical-align: middle;
    }

    .k-footer-right {
        font-size: 4pt;
        color: #F5C800;
        font-weight: 700;
        text-align: right;
        vertical-align: middle;
    }
</style>
</head>
<body>

{{-- PAGE HEADER --}}
<div class="page-header">
    <table class="page-header-table">
        <tr>
            <td class="ph-logo-td">
                <img src="{{ $logoBase64 }}" alt="Logo SMK MHC">
            </td>
            <td class="ph-info-td">
                <div class="ph-school">{{ strtoupper($namaSekolah) }}</div>
                <div class="ph-sub">Daftar Kartu QR Absensi &mdash; {{ $kelas->nama }} ({{ $kelas->jurusan->nama ?? '' }})</div>
                <div class="ph-meta">
                    Tahun Ajaran: {{ $tahunAjaran }}
                    &bull; Dicetak: {{ now()->format('d F Y') }}
                    &bull; Total: {{ $siswaList->count() }} Siswa
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- KARTU GRID (2 kolom, table-based untuk Dompdf) --}}
@php $chunks = $siswaList->chunk(2); @endphp

@foreach($chunks as $row)
<table class="cards-row">
    <tr>
        @foreach($row as $siswa)
        <td class="card-cell">
            <div class="kartu">

                {{-- Header Kuning --}}
                <div class="k-header">
                    <table class="k-header-table">
                        <tr>
                            <td class="k-logo-td">
                                <img src="{{ $logoBase64 }}" alt="Logo">
                            </td>
                            <td class="k-school-td">
                                <div class="k-school">SMK Muthia Harapan Cicalengka</div>
                                <div class="k-school-sub">Jl. Cicalengka &bull; Kab. Bandung</div>
                            </td>
                            <td class="k-badge-td">
                                <span class="k-badge">KARTU<br>PELAJAR</span>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- Body --}}
                <table class="k-body-table">
                    <tr>
                        <td class="k-accent-td">&nbsp;</td>
                        <td class="k-info-td">
                            <div class="k-nama">{{ $siswa->nama }}</div>
                            <table class="k-rows">
                                <tr>
                                    <td class="k-lbl">NIS</td>
                                    <td class="k-sep">:</td>
                                    <td class="k-val">{{ $siswa->nis }}</td>
                                </tr>
                                @if($siswa->nisn)
                                <tr>
                                    <td class="k-lbl">NISN</td>
                                    <td class="k-sep">:</td>
                                    <td class="k-val-sm">{{ $siswa->nisn }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="k-lbl">Kelas</td>
                                    <td class="k-sep">:</td>
                                    <td class="k-val">{{ $kelas->nama }}</td>
                                </tr>
                                <tr>
                                    <td class="k-lbl">Jurusan</td>
                                    <td class="k-sep">:</td>
                                    <td class="k-val-sm">{{ $kelas->jurusan->nama ?? '' }}</td>
                                </tr>
                            </table>
                            <br>
                            <span class="k-status">&#9679; AKTIF</span>
                        </td>
                        <td class="k-sep-td">&nbsp;</td>
                        <td class="k-qr-td">
                            <div class="k-qr-wrap">
                                <img src="{{ $qrCodes[$siswa->id] }}" alt="QR">
                            </div>
                            <div class="k-qr-label">SCAN UNTUK ABSEN</div>
                        </td>
                    </tr>
                </table>

                {{-- Footer --}}
                <div class="k-footer">
                    <table class="k-footer-table">
                        <tr>
                            <td class="k-footer-left">SISTEM ABSENSI QR &bull; {{ strtoupper($namaSekolah) }}</td>
                            <td class="k-footer-right">TA. {{ $tahunAjaran }}</td>
                        </tr>
                    </table>
                </div>

            </div>
        </td>
        @endforeach

        {{-- Jika baris ganjil, tambah cell kosong --}}
        @if($row->count() < 2)
        <td class="card-cell"></td>
        @endif
    </tr>
</table>
@endforeach

</body>
</html>
