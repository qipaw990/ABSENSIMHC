<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter',sans-serif; background: #0d0f16; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card { background: rgba(22,27,39,0.9); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 2rem; width:100%; max-width:420px; text-align:center; }
        h1 { font-size:1.2rem; font-weight:700; color:#f1f5f9; margin-bottom:0.75rem; }
        p { font-size:0.85rem; color:#6b7280; margin-bottom:1.5rem; line-height:1.6; }
        button { padding:0.7rem 1.5rem; background:linear-gradient(135deg,#6366f1,#4f46e5); border:none; border-radius:10px; color:white; font-weight:600; cursor:pointer; font-size:0.9rem; }
        a { color:#6366f1; text-decoration:none; font-size:0.82rem; }
        .msg { padding:0.6rem 0.9rem; border-radius:8px; font-size:0.82rem; margin-bottom:1rem; background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.3); color:#86efac; }
    </style>
</head>
<body>
<div class="card">
    <div style="font-size:3rem;margin-bottom:1rem;">📧</div>
    <h1>Verifikasi Email Anda</h1>
    <p>Terima kasih sudah mendaftar! Sebelum memulai, mohon verifikasi email Anda dengan mengklik link yang sudah kami kirimkan.</p>
    @if(session('status') === 'verification-link-sent')
    <div class="msg">Link verifikasi baru sudah dikirim ke email Anda.</div>
    @endif
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit">Kirim Ulang Email Verifikasi</button>
    </form>
    <br>
    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
        @csrf
        <button type="submit" style="background:none;color:#6b7280;border:none;cursor:pointer;font-size:0.82rem;">Logout</button>
    </form>
</div>
</body>
</html>
