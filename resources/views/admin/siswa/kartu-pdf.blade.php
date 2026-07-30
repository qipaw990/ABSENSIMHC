<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8">
<title>Kartu QR</title>
<style>
html,body{margin:0;padding:0;width:85.6mm;font-size:0;}
*{box-sizing:border-box;}
.card{width:85.6mm;background:#1a1a2e;border-left:3px solid #F5C800;}
.hdr{background:#F5C800;width:100%;border-collapse:collapse;}
.hdr td{border:none;padding:0;height:11mm;}
.hl{width:12mm;text-align:center;vertical-align:middle;padding-left:1.5mm;}
.hl img{width:9mm;height:9mm;display:block;margin:auto;}
.ht{vertical-align:middle;padding-left:1.5mm;}
.hs{font-size:6.5pt;font-weight:900;color:#1a1a2e;text-transform:uppercase;line-height:1.2;}
.hx{font-size:3.5pt;color:#3a2e00;margin-top:0.5mm;}
.hb{width:16mm;text-align:center;vertical-align:middle;padding-right:2mm;}
.hbadge{background:#1a1a2e;color:#F5C800;font-size:4pt;font-weight:900;padding:2px 4px;text-transform:uppercase;border-radius:2px;line-height:1.6;display:inline-block;}
.bdy{width:100%;border-collapse:collapse;}
.bdy td{border:none;padding:0;}
.ba{width:3px;background:#F5C800;}
.bi{vertical-align:top;padding:3mm 2mm 3mm 3mm;width:52%;}
.bn{font-size:8pt;font-weight:900;color:#F5C800;text-transform:uppercase;margin-bottom:3mm;line-height:1.2;display:block;}
.br{border-collapse:collapse;width:100%;}
.br td{border:none;padding:0;}
.ll{font-size:4.5pt;color:#aaa;padding:0 1.5mm 1.5mm 0;white-space:nowrap;vertical-align:top;}
.ls{font-size:4.5pt;color:#F5C800;padding:0 1mm 1.5mm 0;vertical-align:top;}
.lv{font-size:5pt;color:#fff;font-weight:700;padding:0 0 1.5mm 0;vertical-align:top;}
.lv2{font-size:4.5pt;color:#ccc;padding:0 0 1.5mm 0;vertical-align:top;}
.bst{background:#155724;color:#90ee90;font-size:4pt;font-weight:700;padding:2px 6px;border-radius:8px;border:0.5px solid #90ee90;display:inline-block;margin-top:3mm;}
.bd{width:1px;background:#2a2a4e;}
.bq{vertical-align:middle;text-align:center;padding:3mm 3mm;}
.qw{background:#fff;padding:2px;border:2px solid #F5C800;display:inline-block;line-height:0;}
.qw img{width:32mm;height:32mm;display:block;}
.ql{font-size:4pt;color:#F5C800;font-weight:700;text-align:center;margin-top:1.5mm;letter-spacing:0.3pt;}
.ftr{background:#0d0d1a;border-top:1px solid #F5C800;width:100%;border-collapse:collapse;}
.ftr td{border:none;padding:0;height:5mm;vertical-align:middle;}
.fl{font-size:4pt;color:#888;padding-left:3mm;}
.fr{font-size:4pt;color:#F5C800;font-weight:700;text-align:right;padding-right:3mm;}
</style>
</head><body><div class="card">
<table class="hdr"><tr>
<td class="hl"><img src="{{ $logoBase64 }}" alt=""></td>
<td class="ht"><div class="hs">SMK Muthia Harapan Cicalengka</div><div class="hx">Jl. Cicalengka &bull; Kabupaten Bandung &bull; Jawa Barat</div></td>
<td class="hb"><span class="hbadge">KARTU<br>PELAJAR</span></td>
</tr></table>
<table class="bdy"><tr>
<td class="ba">&nbsp;</td>
<td class="bi">
<span class="bn">{{ $siswa->nama }}</span>
<table class="br">
<tr><td class="ll">NIS</td><td class="ls">:</td><td class="lv">{{ $siswa->nis }}</td></tr>
@if($siswa->nisn)<tr><td class="ll">NISN</td><td class="ls">:</td><td class="lv2">{{ $siswa->nisn }}</td></tr>@endif
<tr><td class="ll">Kelas</td><td class="ls">:</td><td class="lv">{{ $siswa->kelas->nama ?? '-' }}</td></tr>
<tr><td class="ll">Jurusan</td><td class="ls">:</td><td class="lv2">{{ $siswa->kelas->jurusan->nama ?? '-' }}</td></tr>
</table>
<span class="bst">AKTIF</span>
</td>
<td class="bd">&nbsp;</td>
<td class="bq"><div class="qw"><img src="{{ $qrBase64 }}" alt="QR"></div><div class="ql">SCAN UNTUK ABSEN</div></td>
</tr></table>
<table class="ftr"><tr>
<td class="fl">SISTEM ABSENSI QR &bull; {{ strtoupper($namaSekolah) }}</td>
<td class="fr">TA. {{ \App\Models\TahunAjaran::where('is_aktif',true)->value('nama') ?? now()->format('Y') }}</td>
</tr></table>
</div></body></html>
