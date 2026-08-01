@extends('layouts.app')

@section('title', 'Scan QR Absensi — ' . $kelas->nama)

@push('styles')
<style>
    #qr-reader { max-width: 100%; }
    #qr-reader__scan_region { border-radius: 12px; }
    #qr-reader img { display: none !important; } /* sembunyikan logo html5-qrcode */

    .scan-overlay {
        position: relative;
        background: var(--bg-card);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        overflow: hidden;
    }

    .scan-status-bar {
        display: flex; align-items: center; gap: 0.5rem;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.82rem;
    }

    .pulse-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 0 0 rgba(34,197,94,0.6);
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0%   { box-shadow: 0 0 0 0 rgba(34,197,94,0.6); }
        70%  { box-shadow: 0 0 0 8px rgba(34,197,94,0); }
        100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
    }

    .confirm-card {
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        border: 2px solid;
        transition: all 0.4s ease;
    }

    .confirm-card.hadir     { background: rgba(34,197,94,0.08);  border-color: rgba(34,197,94,0.4); }
    .confirm-card.terlambat { background: rgba(245,158,11,0.08); border-color: rgba(245,158,11,0.4); }
    .confirm-card.error     { background: rgba(239,68,68,0.08);  border-color: rgba(239,68,68,0.4); }
    .confirm-card.warning   { background: rgba(245,158,11,0.08); border-color: rgba(245,158,11,0.4); }

    .student-avatar {
        width: 80px; height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(99,102,241,0.5);
    }

    .belum-absen-list {
        max-height: 360px;
        overflow-y: auto;
    }

    .belum-absen-item {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.6rem 1rem;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.82rem;
        transition: background 0.2s;
    }

    .belum-absen-item:hover { background: rgba(255,255,255,0.03); }
    .belum-absen-item:last-child { border-bottom: none; }

    .mini-avatar {
        width: 32px; height: 32px; border-radius: 50%;
        background: rgba(99,102,241,0.2);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.7rem; font-weight: 700; color: #c7d2fe;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-qr-code-scan me-2 text-primary"></i>Scan Absensi</h1>
        <p class="page-subtitle">Kelas {{ $kelas->nama }} &mdash; {{ now()->translatedFormat('l, d F Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('guru.absensi.rekap', $kelas->id) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-list-check me-1"></i>Lihat Rekap
        </a>
        <a href="{{ route('guru.absensi.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

<!-- Stat Mini Bar -->
<div class="row g-2 mb-4" id="statsBar">
    <div class="col-6 col-md-3">
        <div class="card text-center py-2">
            <div style="font-size:1.5rem;font-weight:700;color:#22c55e;" id="stat-hadir">{{ $stats['hadir'] }}</div>
            <div style="font-size:0.7rem;color:#6b7280;">Hadir</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-2">
            <div style="font-size:1.5rem;font-weight:700;color:#f59e0b;" id="stat-terlambat">{{ $stats['terlambat'] }}</div>
            <div style="font-size:0.7rem;color:#6b7280;">Terlambat</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-2">
            <div style="font-size:1.5rem;font-weight:700;color:#6366f1;" id="stat-izin">{{ $stats['izin'] + $stats['sakit'] }}</div>
            <div style="font-size:0.7rem;color:#6b7280;">Izin/Sakit</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center py-2">
            <div style="font-size:1.5rem;font-weight:700;color:#ef4444;" id="stat-belum">{{ $stats['belum_absen'] }}</div>
            <div style="font-size:0.7rem;color:#6b7280;">Belum Absen</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- PANEL SCAN QR -->
    <div class="col-12 col-lg-7">
        <div class="scan-overlay">
            <div class="scan-status-bar">
                <div class="pulse-dot" id="scanDot"></div>
                <span id="scanStatus" style="color:#94a3b8;">Kamera aktif — Arahkan QR Code siswa ke kamera</span>
            </div>
            <div class="p-3">
                <div id="qr-reader" style="width:100%;border-radius:12px;overflow:hidden;"></div>
            </div>
        </div>

        <!-- Hasil Scan -->
        <div id="scanResult" class="mt-3" style="display:none;">
            <div class="confirm-card" id="confirmCard">
                <div class="d-flex justify-content-center mb-3">
                    <img src="" alt="Foto Siswa" class="student-avatar" id="siswaFoto">
                </div>
                <h5 id="siswaNama" style="font-weight:700;margin-bottom:0.25rem;"></h5>
                <div id="siswaNis" style="font-size:0.78rem;color:#6b7280;margin-bottom:0.75rem;"></div>
                <div id="scanMessage" style="font-size:0.9rem;"></div>
                <div id="statusBadge" class="mt-2"></div>
            </div>
        </div>
    </div>

    <!-- PANEL DAFTAR BELUM ABSEN -->
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center px-3 py-2">
                <span style="font-size:0.85rem;">
                    <i class="bi bi-person-x me-1 text-warning"></i>
                    Belum Absen (<span id="belumCount">{{ $stats['belum_absen'] }}</span>)
                </span>
                <button class="btn btn-sm btn-outline-secondary" onclick="refreshBelumAbsen()">
                    <i class="bi bi-arrow-clockwise" id="refreshIcon"></i>
                </button>
            </div>
            <div class="belum-absen-list" id="belumAbsenList">
                @forelse($belumAbsen as $siswa)
                <div class="belum-absen-item" id="siswa-row-{{ $siswa->id }}">
                    <div class="mini-avatar">{{ substr($siswa->nama, 0, 1) }}</div>
                    <div>
                        <div style="font-weight:500;color:#e2e8f0;">{{ $siswa->nama }}</div>
                        <div style="font-size:0.72rem;color:#6b7280;">NIS: {{ $siswa->nis }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5" style="color:#6b7280;">
                    <i class="bi bi-check-circle-fill" style="font-size:2rem;color:#22c55e;display:block;margin-bottom:0.5rem;"></i>
                    Semua siswa sudah absen!
                </div>
                @endforelse
            </div>

            <!-- Input Manual Fallback -->
            <div class="p-3 border-top" style="border-color:var(--border-color) !important;">
                <button class="btn btn-sm btn-outline-warning w-100" data-bs-toggle="collapse" data-bs-target="#manualForm">
                    <i class="bi bi-pencil me-1"></i>Input Manual
                </button>
                <div class="collapse mt-2" id="manualForm">
                    <form action="/guru/absensi/manual" method="POST">
                        @csrf
                        <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                        <input type="hidden" name="tanggal" value="{{ today()->toDateString() }}">
                        <select name="siswa_id" class="form-select form-select-sm mb-2" required>
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($belumAbsen as $siswa)
                            <option value="{{ $siswa->id }}">{{ $siswa->nama }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="form-select form-select-sm mb-2" required>
                            <option value="hadir">Hadir</option>
                            <option value="terlambat">Terlambat</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="alpha">Alpha</option>
                        </select>
                        <input type="text" name="keterangan" class="form-control form-control-sm mb-2" placeholder="Keterangan (opsional)">
                        <button type="submit" class="btn btn-sm btn-warning w-100">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- html5-qrcode library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
const KELAS_ID = {{ $kelas->id }};
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
let lastScanned = '';
let scanCooldown = false;

// Inisialisasi scanner
const html5QrCode = new Html5Qrcode("qr-reader");

Html5Qrcode.getCameras().then(cameras => {
    if (!cameras || cameras.length === 0) {
        document.getElementById('scanStatus').textContent = 'Kamera tidak ditemukan!';
        return;
    }

    // Pakai kamera belakang jika ada
    const cameraId = cameras.find(c => c.label.toLowerCase().includes('back'))?.id || cameras[0].id;

    html5QrCode.start(
        cameraId,
        { fps: 10, qrbox: { width: 260, height: 260 } },
        onScanSuccess,
        () => {} // onScanFailure diam-diam
    ).catch(err => {
        document.getElementById('scanStatus').textContent = 'Gagal akses kamera: ' + err;
    });
}).catch(err => {
    document.getElementById('scanStatus').textContent = 'Error kamera: ' + err;
});

function onScanSuccess(decodedText) {
    if (scanCooldown || decodedText === lastScanned) return;

    scanCooldown = true;
    lastScanned = decodedText;

    // Bunyi beep
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = audioCtx.createOscillator();
    osc.connect(audioCtx.destination);
    osc.frequency.value = 800;
    osc.start(); osc.stop(audioCtx.currentTime + 0.1);

    document.getElementById('scanStatus').textContent = 'Memproses...';
    document.getElementById('scanDot').style.background = '#f59e0b';

    // Kirim ke server (pakai relative path murni untuk cegah mixed content)
    fetch('/guru/absensi/proses-scan', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ qr_token: decodedText, kelas_id: KELAS_ID })
    })
    .then(res => res.json())
    .then(data => tampilkanHasil(data))
    .catch(err => {
        tampilkanHasil({ success: false, message: 'Error koneksi: ' + err });
    })
    .finally(() => {
        setTimeout(() => {
            scanCooldown = false;
            lastScanned = '';
            document.getElementById('scanDot').style.background = '#22c55e';
            document.getElementById('scanStatus').textContent = 'Kamera aktif — Arahkan QR Code siswa ke kamera';
        }, 3000);
    });
}

function tampilkanHasil(data) {
    const resultDiv  = document.getElementById('scanResult');
    const card       = document.getElementById('confirmCard');
    const foto       = document.getElementById('siswaFoto');
    const nama       = document.getElementById('siswaNama');
    const nis        = document.getElementById('siswaNis');
    const message    = document.getElementById('scanMessage');
    const badge      = document.getElementById('statusBadge');

    resultDiv.style.display = 'block';

    // Reset class
    card.className = 'confirm-card';

    if (data.siswa) {
        foto.src = data.siswa.foto_url; // sudah berisi SVG avatar jika foto kosong
        foto.style.display = 'block';
        nama.textContent = data.siswa.nama;
        nis.textContent  = 'NIS: ' + data.siswa.nis + ' | Kelas: ' + data.siswa.kelas;
    } else {
        foto.style.display = 'none';
        nama.textContent = '';
        nis.textContent  = '';
    }

    message.textContent = data.message;

    if (data.success && data.absensi) {
        card.classList.add(data.absensi.status);
        const colorMap = { hadir: '#22c55e', terlambat: '#f59e0b' };
        badge.innerHTML = `<span class="badge" style="background:${colorMap[data.absensi.status] || '#6366f1'};font-size:0.85rem;padding:0.5em 1em;">
            ${data.absensi.status_label.toUpperCase()}
        </span>`;

        // Refresh daftar belum absen setelah sukses
        setTimeout(refreshBelumAbsen, 1000);
    } else {
        card.classList.add(data.success ? 'hadir' : 'error');
        badge.innerHTML = '';
    }

    // Auto sembunyi setelah 8 detik (lebih lama agar bisa dibaca)
    setTimeout(() => { resultDiv.style.display = 'none'; }, 8000);

    // Update counter stat hadir/terlambat jika sukses
    if (data.success && data.absensi) {
        const statEl = document.getElementById('stat-' + data.absensi.status);
        if (statEl) statEl.textContent = parseInt(statEl.textContent || '0') + 1;
        const belumEl = document.getElementById('stat-belum');
        if (belumEl) belumEl.textContent = Math.max(0, parseInt(belumEl.textContent || '0') - 1);
    }

} // end tampilkanHasil

// Refresh daftar siswa belum absen via AJAX
function refreshBelumAbsen() {
    const icon = document.getElementById('refreshIcon');
    icon.style.animation = 'spin 1s linear infinite';

    fetch('/guru/absensi/belum-scan/{{ $kelas->id }}')
    .then(r => r.json())
    .then(data => {
        document.getElementById('belumCount').textContent = data.count;
        document.getElementById('stat-belum').textContent = data.count;

        const list = document.getElementById('belumAbsenList');
        if (data.count === 0) {
            list.innerHTML = `<div class="text-center py-5" style="color:#6b7280;">
                <i class="bi bi-check-circle-fill" style="font-size:2rem;color:#22c55e;display:block;margin-bottom:0.5rem;"></i>
                Semua siswa sudah absen!
            </div>`;
        } else {
            list.innerHTML = data.siswa.map(s => `
                <div class="belum-absen-item">
                    <div class="mini-avatar">${s.nama.charAt(0)}</div>
                    <div>
                        <div style="font-weight:500;color:#e2e8f0;">${s.nama}</div>
                        <div style="font-size:0.72rem;color:#6b7280;">NIS: ${s.nis}</div>
                    </div>
                </div>
            `).join('');
        }
    })
    .finally(() => { icon.style.animation = ''; });
}

// Spin animation untuk refresh icon
const style = document.createElement('style');
style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
document.head.appendChild(style);

// Auto refresh setiap 30 detik
setInterval(refreshBelumAbsen, 30000);
</script>
@endpush
