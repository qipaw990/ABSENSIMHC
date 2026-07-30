<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}body{font-family:'Inter',sans-serif;background:#0d0f16;min-height:100vh;display:flex;align-items:center;justify-content:center}
        .card{background:rgba(22,27,39,.9);border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:2rem;width:100%;max-width:400px}
        h1{font-size:1.2rem;font-weight:700;color:#f1f5f9;margin-bottom:.5rem}p{font-size:.82rem;color:#6b7280;margin-bottom:1.5rem}
        label{display:block;font-size:.8rem;font-weight:600;color:#94a3b8;margin-bottom:.35rem}
        input{width:100%;padding:.7rem .9rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#e2e8f0;font-size:.9rem;outline:none;margin-bottom:1rem;font-family:'Inter',sans-serif}
        input:focus{border-color:#6366f1}
        button{width:100%;padding:.75rem;background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;border-radius:10px;color:white;font-weight:600;cursor:pointer;font-size:.9rem}
    </style>
</head>
<body>
<div class="card">
    <h1>🔒 Konfirmasi Password</h1>
    <p>Area ini memerlukan konfirmasi password Anda untuk keamanan.</p>
    @if($errors->any())<div style="padding:.6rem;border-radius:8px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;font-size:.82rem;margin-bottom:1rem;">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <label>Password</label>
        <input type="password" name="password" required autocomplete="current-password" placeholder="Password Anda">
        <button type="submit">Konfirmasi</button>
    </form>
</div>
</body>
</html>
