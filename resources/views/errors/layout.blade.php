{{-- Halaman galat mandiri: tanpa database, tanpa Vite, tetap tampil walau server bermasalah. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('code') · @yield('title')</title>
    <link rel="icon" href="{{ asset('images/logo-default.svg') }}">
    <style>
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:#172033;
             background:#f3f5f9}
        .card{width:100%;max-width:480px;background:#fff;border:1px solid #e3e7ee;border-radius:16px;padding:36px 32px;box-shadow:0 10px 30px rgba(16,24,40,.08);text-align:center}
        .logo{width:56px;height:56px;margin:0 auto 18px;display:block}
        .code{font-size:13px;font-weight:700;letter-spacing:.12em;color:#64708a;text-transform:uppercase}
        h1{font-size:24px;margin:6px 0 8px;letter-spacing:-.01em}
        p{margin:0 0 24px;color:#64708a;line-height:1.55}
        .actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
        a.btn{display:inline-block;padding:10px 18px;border-radius:8px;font-weight:600;text-decoration:none;border:1px solid #cfd6e3;color:#172033;background:#fff}
        a.btn.primary{background:#101a2e;border-color:#101a2e;color:#fff}
        a.btn:hover{filter:brightness(.96)}
    </style>
</head>
<body>
    <main class="card">
        <div class="code">Galat @yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <div class="actions">
            @section('actions')
                <a class="btn" href="javascript:history.back()">Kembali</a>
                <a class="btn primary" href="{{ url('/dashboard') }}">Ke Dashboard</a>
            @show
        </div>
    </main>
</body>
</html>
