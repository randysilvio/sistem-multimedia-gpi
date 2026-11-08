@php
    $logo = \App\Services\Branding::logoUrl();
    $church = \App\Services\Branding::churchName();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#101a2e">
    <title>@yield('title', 'Masuk') · {{ $church }}</title>
    <link rel="icon" href="{{ $logo }}">
    @vite(['resources/css/admin-app.css', 'resources/js/admin.js'])
</head>
<body class="adm-auth">
    <aside class="adm-auth-aside">
        <div class="adm-auth-mark">
            <img src="{{ $logo }}" alt="Logo {{ $church }}" width="150" height="150" onerror="this.style.display='none'">
            <div class="adm-auth-church">{{ $church }}</div>
            <div class="adm-auth-unit">Pelayanan Multimedia</div>
        </div>

        <figure class="adm-auth-verse">
            <blockquote>Nyanyikanlah nyanyian baru bagi TUHAN, menyanyilah bagi TUHAN, hai segenap bumi!</blockquote>
            <figcaption>Mazmur 96:1</figcaption>
        </figure>
    </aside>

    <main class="adm-auth-main">
        <div class="adm-auth-card">
            <div class="adm-auth-mobile-brand">
                <img src="{{ $logo }}" alt="" width="48" height="48" onerror="this.style.display='none'">
                <div>{{ $church }}<div class="adm-muted small fw-normal">Pelayanan Multimedia</div></div>
            </div>

            @if(session('status') && session('status') !== 'verification-link-sent')
                <div class="adm-flash adm-flash-success" role="status"><span>{{ session('status') }}</span></div>
            @endif

            @yield('content')
        </div>
    </main>
</body>
</html>
