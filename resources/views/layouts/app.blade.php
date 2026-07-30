<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.nama_sekolah', 'Absensi QR SMK') }}</title>
    <meta name="description" content="Sistem Absensi QR Code SMK dengan notifikasi WhatsApp otomatis">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --bg-sidebar: #0f1117;
            --bg-main: #0d0f16;
            --bg-card: #161b27;
            --bg-card-hover: #1e2535;
            --border-color: rgba(255,255,255,0.07);
            --text-muted-custom: #6b7280;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-main);
            color: #e2e8f0;
            margin: 0;
            overflow-x: hidden;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 1.5rem 1.25rem 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-brand h5 {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .sidebar-brand small {
            color: var(--text-muted-custom);
            font-size: 0.72rem;
        }

        .sidebar-nav {
            padding: 1rem 0.75rem;
            flex: 1;
        }

        .nav-label {
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-muted-custom);
            padding: 0.5rem 0.75rem 0.25rem;
            margin-top: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.55rem 0.75rem;
            border-radius: 8px;
            color: #94a3b8;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 2px;
        }

        .nav-link:hover {
            background: rgba(99,102,241,0.1);
            color: #e2e8f0;
        }

        .nav-link.active {
            background: linear-gradient(135deg, rgba(99,102,241,0.25), rgba(79,70,229,0.15));
            color: #c7d2fe;
            border-left: 3px solid var(--primary);
        }

        .nav-link i { font-size: 1rem; width: 20px; text-align: center; }

        /* ===== TOPBAR ===== */
        .topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: 60px;
            background: var(--bg-main);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            justify-content: space-between;
            z-index: 999;
            backdrop-filter: blur(8px);
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: 60px;
            min-height: 100vh;
        }

        .content-wrapper {
            padding: 1.75rem;
        }

        /* ===== CARDS ===== */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .card:hover { border-color: rgba(99,102,241,0.3); }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* ===== STAT CARDS ===== */
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 80px; height: 80px;
            border-radius: 50%;
            opacity: 0.08;
            background: var(--stat-color, var(--primary));
            transform: translate(20px, -20px);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            border-color: rgba(99,102,241,0.3);
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        }

        .stat-icon {
            width: 44px; height: 44px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 0.75rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.78rem;
            color: var(--text-muted-custom);
            font-weight: 500;
        }

        /* ===== BADGE STATUS ===== */
        .badge {
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.35em 0.65em;
            border-radius: 6px;
        }

        /* ===== TABLE ===== */
        .table {
            color: #e2e8f0;
            font-size: 0.85rem;
        }

        .table thead th {
            background: rgba(255,255,255,0.03);
            color: #94a3b8;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
        }

        .table tbody td {
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
            vertical-align: middle;
        }

        .table tbody tr:hover td { background: rgba(255,255,255,0.02); }

        /* ===== FORM ===== */
        .form-control, .form-select {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border-color);
            color: #e2e8f0;
            border-radius: 8px;
            font-size: 0.875rem;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255,255,255,0.08);
            border-color: var(--primary);
            color: #e2e8f0;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }

        .form-control::placeholder { color: var(--text-muted-custom); }

        .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 0.35rem;
        }

        /* ===== BUTTONS ===== */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(99,102,241,0.4);
        }

        /* ===== ALERT ===== */
        .alert {
            border-radius: 10px;
            border: 1px solid;
            font-size: 0.875rem;
            padding: 0.75rem 1rem;
        }

        /* ===== PAGE HEADER ===== */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #f1f5f9;
            margin: 0;
        }

        .page-subtitle {
            font-size: 0.8rem;
            color: var(--text-muted-custom);
            margin-top: 0.2rem;
        }

        /* ===== SIDEBAR USER ===== */
        .sidebar-user {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-avatar {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }

        /* ===== WA STATUS INDICATOR ===== */
        .wa-status-dot {
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .wa-status-dot.aktif { background: #22c55e; box-shadow: 0 0 6px #22c55e; }
        .wa-status-dot.terputus { background: #ef4444; }
        .wa-status-dot.nonaktif { background: #6b7280; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1050; /* Lebih tinggi dari overlay */
            }
            .sidebar.show { transform: translateX(0); }
            .topbar { left: 0; }
            .main-content { margin-left: 0; }

            /* Tombol close di dalam sidebar */
            .sidebar-close-btn { display: flex !important; }

            /* Overlay gelap saat sidebar terbuka */
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.55);
                z-index: 1040;
                backdrop-filter: blur(2px);
                -webkit-backdrop-filter: blur(2px);
                cursor: pointer;
            }
            .sidebar-overlay.show { display: block; }

            /* Animasi overlay */
            @keyframes overlayIn {
                from { opacity: 0; }
                to   { opacity: 1; }
            }
            .sidebar-overlay.show { animation: overlayIn 0.25s ease; }
        }

        /* Default: sembunyikan tombol close di desktop */
        .sidebar-close-btn {
            display: none;
            align-items: center;
            justify-content: center;
            width: 32px; height: 32px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            cursor: pointer;
            color: #94a3b8;
            flex-shrink: 0;
            transition: all 0.2s;
        }
        .sidebar-close-btn:hover {
            background: rgba(239,68,68,0.15);
            border-color: rgba(239,68,68,0.3);
            color: #f87171;
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .fade-in-up { animation: fadeInUp 0.4s ease forwards; }

        /* ===== SCAN PAGE SPECIFIC ===== */
        #qr-reader { border-radius: 12px; overflow: hidden; }
        #qr-reader video { border-radius: 12px; }

        .scan-result-card {
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .scan-result-card.success { background: rgba(34,197,94,0.1); border: 2px solid rgba(34,197,94,0.3); }
        .scan-result-card.error   { background: rgba(239,68,68,0.1); border: 2px solid rgba(239,68,68,0.3); }
        .scan-result-card.warning { background: rgba(245,158,11,0.1); border: 2px solid rgba(245,158,11,0.3); }

        .siswa-foto {
            width: 80px; height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid rgba(99,102,241,0.5);
        }
    </style>

    @stack('styles')
</head>
<body>

<!-- Overlay (klik di luar sidebar untuk menutup — mobile only) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="d-flex align-items-center gap-2 mb-1">
            <div style="width:32px;height:32px;background:linear-gradient(135deg,#6366f1,#4f46e5);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-qr-code-scan text-white" style="font-size:1rem;"></i>
            </div>
            <h5 class="mb-0 flex-fill">AbsensiQR</h5>
            {{-- Tombol X hanya tampil di mobile --}}
            <button class="sidebar-close-btn" id="sidebarClose" aria-label="Tutup menu">
                <i class="bi bi-x-lg" style="font-size:0.9rem;"></i>
            </button>
        </div>
        <small>{{ config('app.nama_sekolah', 'SMK') }}</small>
    </div>

    <nav class="sidebar-nav">
        @auth
            {{-- SUPER ADMIN --}}
            @role('super_admin')
            <div class="nav-label">Super Admin</div>
            <a href="{{ route('super-admin.dashboard') }}" class="nav-link {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('super-admin.wa-sender.index') }}" class="nav-link {{ request()->routeIs('super-admin.wa-sender.*') ? 'active' : '' }}">
                <i class="bi bi-whatsapp"></i> WA Sender
            </a>
            <a href="{{ route('super-admin.wa-log.index') }}" class="nav-link {{ request()->routeIs('super-admin.wa-log.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i> Log WA
            </a>
            <a href="{{ route('super-admin.template-pesan.index') }}" class="nav-link {{ request()->routeIs('super-admin.template-pesan.*') ? 'active' : '' }}">
                <i class="bi bi-chat-quote"></i> Template Pesan
            </a>
            @endrole

            {{-- ADMIN --}}
            @hasanyrole('admin|super_admin')
            <div class="nav-label">Data Master</div>
            <a href="{{ route('admin.tahun-ajaran.index') }}" class="nav-link {{ request()->routeIs('admin.tahun-ajaran.*') ? 'active' : '' }}">
                <i class="bi bi-calendar3"></i> Tahun Ajaran
            </a>
            <a href="{{ route('admin.jurusan.index') }}" class="nav-link {{ request()->routeIs('admin.jurusan.*') ? 'active' : '' }}">
                <i class="bi bi-mortarboard"></i> Jurusan
            </a>
            <a href="{{ route('admin.kelas.index') }}" class="nav-link {{ request()->routeIs('admin.kelas.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Kelas
            </a>
            <a href="{{ route('admin.guru.index') }}" class="nav-link {{ request()->routeIs('admin.guru.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Guru
            </a>
            <a href="{{ route('admin.siswa.index') }}" class="nav-link {{ request()->routeIs('admin.siswa.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Siswa & QR
            </a>
            <a href="{{ route('admin.pengaturan-absensi.index') }}" class="nav-link {{ request()->routeIs('admin.pengaturan-absensi.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Jam Absensi
            </a>
            @endhasanyrole

            {{-- GURU --}}
            @hasanyrole('guru|admin|super_admin')
            <div class="nav-label">Absensi</div>
            <a href="{{ route('guru.absensi.index') }}" class="nav-link {{ request()->routeIs('guru.absensi.index') ? 'active' : '' }}">
                <i class="bi bi-qr-code-scan"></i> Scan QR Absensi
            </a>
            <a href="{{ route('guru.izin.index') }}" class="nav-link {{ request()->routeIs('guru.izin.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-check"></i> Izin & Sakit
            </a>
            <a href="{{ route('guru.laporan.index') }}" class="nav-link {{ request()->routeIs('guru.laporan.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Laporan
            </a>
            @endhasanyrole

            {{-- SISWA --}}
            @role('siswa')
            <div class="nav-label">Siswa</div>
            <a href="{{ route('siswa.riwayat.index') }}" class="nav-link {{ request()->routeIs('siswa.riwayat.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i> Riwayat Absensi
            </a>
            <a href="{{ route('siswa.izin.index') }}" class="nav-link {{ request()->routeIs('siswa.izin.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Pengajuan Izin
            </a>
            @endrole
        @endauth
    </nav>

    <!-- User Info -->
    @auth
    <div class="sidebar-user">
        <div class="sidebar-avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
        <div style="min-width:0;">
            <div style="font-size:0.82rem;font-weight:600;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ Auth::user()->name }}
            </div>
            <div style="font-size:0.7rem;color:#6b7280;">
                {{ Auth::user()->getRoleNames()->first() ?? 'User' }}
            </div>
        </div>
    </div>
    @endauth
</aside>

<!-- ===== TOPBAR ===== -->
<header class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <div>
            <div style="font-size:0.8rem;font-weight:600;color:#e2e8f0;">@yield('title', 'Dashboard')</div>
            <div style="font-size:0.7rem;color:#6b7280;">
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        {{-- Dropdown Profil --}}
        <div class="dropdown">
            <button class="btn btn-sm d-flex align-items-center gap-2"
                style="background:rgba(255,255,255,0.05);border:1px solid var(--border-color);border-radius:10px;padding:0.35rem 0.75rem;"
                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div style="width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#4f46e5);display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;color:white;flex-shrink:0;">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>
                <span style="font-size:0.78rem;color:#e2e8f0;max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    {{ Auth::user()->name ?? 'User' }}
                </span>
                <i class="bi bi-chevron-down" style="font-size:0.65rem;color:#6b7280;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:0.5rem;min-width:180px;">
                <li>
                    <div style="padding:0.5rem 0.75rem;border-bottom:1px solid var(--border-color);margin-bottom:0.25rem;">
                        <div style="font-size:0.8rem;font-weight:600;color:#e2e8f0;">{{ Auth::user()->name ?? '' }}</div>
                        <div style="font-size:0.7rem;color:#6b7280;">{{ Auth::user()->getRoleNames()->first() ?? '' }}</div>
                    </div>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}"
                        style="border-radius:8px;font-size:0.82rem;color:#94a3b8;padding:0.5rem 0.75rem;">
                        <i class="bi bi-person me-2"></i>Profil Saya
                    </a>
                </li>
                <li><hr class="dropdown-divider" style="border-color:var(--border-color);margin:0.25rem 0;"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger"
                            style="border-radius:8px;font-size:0.82rem;padding:0.5rem 0.75rem;">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
    </div>
</header>

<!-- ===== MAIN CONTENT ===== -->
<main class="main-content">
    <div class="content-wrapper">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-x-circle-fill"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-info-circle-fill"></i>
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // ===== SIDEBAR MOBILE =====
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('sidebarOverlay');
    const btnOpen   = document.getElementById('sidebarToggle');
    const btnClose  = document.getElementById('sidebarClose');

    function openSidebar() {
        sidebar.classList.add('show');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden'; // Cegah scroll body
    }

    function closeSidebar() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    // Buka sidebar
    btnOpen?.addEventListener('click', openSidebar);

    // Tutup dengan tombol X di dalam sidebar
    btnClose?.addEventListener('click', closeSidebar);

    // Tutup dengan klik overlay (area di luar sidebar)
    overlay?.addEventListener('click', closeSidebar);

    // Tutup dengan tombol Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('show')) {
            closeSidebar();
        }
    });

    // Otomatis tutup sidebar saat link diklik (UX mobile)
    sidebar?.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });

    // Auto-dismiss alerts setelah 5 detik
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
            bsAlert?.close();
        });
    }, 5000);
</script>

@stack('scripts')
</body>
</html>
