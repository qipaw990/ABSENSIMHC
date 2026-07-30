<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password — {{ config('app.nama_sekolah', 'Absensi QR SMK') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #0d0f16; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: rgba(22,27,39,0.9); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 2rem; width: 100%; max-width: 400px; }
        h1 { font-size: 1.2rem; font-weight: 700; color: #f1f5f9; margin-bottom: 0.5rem; }
        p { font-size: 0.82rem; color: #6b7280; margin-bottom: 1.5rem; }
        label { display: block; font-size: 0.8rem; font-weight: 600; color: #94a3b8; margin-bottom: 0.35rem; }
        input { width: 100%; padding: 0.7rem 0.9rem; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #e2e8f0; font-size: 0.9rem; outline: none; margin-bottom: 1rem; font-family: 'Inter', sans-serif; }
        input:focus { border-color: #6366f1; }
        button { width: 100%; padding: 0.75rem; background: linear-gradient(135deg,#6366f1,#4f46e5); border: none; border-radius: 10px; color: white; font-weight: 600; cursor: pointer; font-size: 0.9rem; }
        a { color: #6366f1; text-decoration: none; font-size: 0.82rem; display: inline-block; margin-top: 1rem; }
        .msg { padding: 0.6rem 0.9rem; border-radius: 8px; font-size: 0.82rem; margin-bottom: 1rem; }
        .msg.success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); color: #86efac; }
        .msg.error   { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; }
    </style>
</head>
<body>
<div class="card">
    <h1><i class="bi bi-key me-2" style="color:#6366f1;"></i>Lupa Password</h1>
    <p>Masukkan email Anda dan kami akan mengirimkan link reset password.</p>

    @if(session('status'))
    <div class="msg success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
    <div class="msg error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="email@sekolah.sch.id" required autofocus>
        <button type="submit">Kirim Link Reset Password</button>
    </form>
    <a href="{{ route('login') }}"><i class="bi bi-arrow-left me-1"></i>Kembali ke Login</a>
</div>
</body>
</html>
