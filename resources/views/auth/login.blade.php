<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ config('app.nama_sekolah', 'Absensi QR SMK') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #0d0f16;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background orbs */
        body::before, body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: float 8s ease-in-out infinite;
        }

        body::before {
            width: 500px; height: 500px;
            background: radial-gradient(circle, #6366f1, #4f46e5);
            top: -100px; left: -100px;
        }

        body::after {
            width: 400px; height: 400px;
            background: radial-gradient(circle, #8b5cf6, #7c3aed);
            bottom: -80px; right: -80px;
            animation-delay: -4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50%       { transform: translateY(-30px) scale(1.05); }
        }

        .login-card {
            background: rgba(22, 27, 39, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 10;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        }

        .brand {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-icon {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
            box-shadow: 0 8px 20px rgba(99,102,241,0.4);
        }

        .brand h1 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 0.25rem;
        }

        .brand p {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .form-group { margin-bottom: 1.25rem; }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 0.4rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 0.95rem;
        }

        .form-input {
            width: 100%;
            padding: 0.7rem 0.9rem 0.7rem 2.5rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            color: #e2e8f0;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: #6366f1;
            background: rgba(99,102,241,0.08);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }

        .form-input::placeholder { color: #4b5563; }

        .remember-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .check-label {
            display: flex; align-items: center; gap: 0.5rem;
            font-size: 0.82rem; color: #94a3b8; cursor: pointer;
        }

        .check-label input { accent-color: #6366f1; }

        .forgot-link {
            font-size: 0.82rem; color: #6366f1; text-decoration: none;
        }
        .forgot-link:hover { color: #818cf8; }

        .btn-login {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99,102,241,0.4);
        }

        .btn-login:active { transform: translateY(0); }

        .error-msg {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 8px;
            padding: 0.6rem 0.9rem;
            color: #fca5a5;
            font-size: 0.82rem;
            margin-bottom: 1rem;
            display: flex; align-items: center; gap: 0.5rem;
        }

        .divider {
            text-align: center;
            font-size: 0.72rem;
            color: #374151;
            margin: 1.25rem 0;
            position: relative;
        }

        .divider::before, .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background: rgba(255,255,255,0.06);
        }
        .divider::before { left: 0; }
        .divider::after { right: 0; }

        .demo-info {
            background: rgba(99,102,241,0.08);
            border: 1px solid rgba(99,102,241,0.2);
            border-radius: 10px;
            padding: 0.85rem 1rem;
            font-size: 0.78rem;
            color: #94a3b8;
        }

        .demo-info strong { color: #c7d2fe; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand">
        <div class="brand-icon">
            <i class="bi bi-qr-code-scan"></i>
        </div>
        <h1>Sistem Absensi QR</h1>
        <p>{{ config('app.nama_sekolah', 'SMK') }}</p>
    </div>

    {{-- Error Messages --}}
    @if($errors->any())
    <div class="error-msg">
        <i class="bi bi-exclamation-circle-fill"></i>
        {{ $errors->first() }}
    </div>
    @endif

    @if(session('status'))
    <div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);border-radius:8px;padding:0.6rem 0.9rem;color:#86efac;font-size:0.82rem;margin-bottom:1rem;">
        {{ session('status') }}
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <div class="input-wrapper">
                <i class="bi bi-envelope input-icon"></i>
                <input type="email" id="email" name="email" class="form-input"
                    placeholder="admin@sekolah.sch.id"
                    value="{{ old('email') }}" required autofocus autocomplete="email">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="input-wrapper">
                <i class="bi bi-lock input-icon"></i>
                <input type="password" id="password" name="password" class="form-input"
                    placeholder="••••••••" required autocomplete="current-password">
            </div>
        </div>

        <div class="remember-row">
            <label class="check-label">
                <input type="checkbox" name="remember">
                Ingat saya
            </label>
            @if(Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="forgot-link">Lupa password?</a>
            @endif
        </div>

        <button type="submit" class="btn-login">
            <i class="bi bi-box-arrow-in-right"></i>
            Masuk ke Sistem
        </button>
    </form>

    <div class="divider">akun default</div>

    <div class="demo-info">
        <div><strong>Super Admin:</strong> superadmin@absensi.sch.id</div>
        <div><strong>Password:</strong> password123</div>
        <div style="margin-top:0.4rem;color:#6b7280;font-size:0.72rem;">
            ⚠️ Ganti password setelah login pertama
        </div>
    </div>
</div>
</body>
</html>
