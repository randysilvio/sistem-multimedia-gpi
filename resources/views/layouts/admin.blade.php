@php
    $logo = \App\Services\Branding::logoUrl();
    $church = \App\Services\Branding::churchName();
    $nav = [
        ['label' => 'Ibadah', 'items' => [
            ['route' => 'dashboard', 'icon' => 'home', 'text' => 'Dashboard', 'active' => request()->routeIs('dashboard')],
            ['route' => 'liturgy.gallery', 'icon' => 'calendar', 'text' => 'Jadwal & Presentasi', 'active' => request()->routeIs('liturgy.gallery', 'liturgy.create', 'liturgy.builder', 'liturgy.edit')],
            ['route' => 'liturgy.import', 'icon' => 'upload', 'text' => 'Buat dari Buletin', 'active' => request()->routeIs('liturgy.import*')],
            ['route' => 'liturgy.template.create', 'icon' => 'layers', 'text' => 'Template Liturgi', 'active' => request()->routeIs('liturgy.template.*')],
            ['route' => 'announcement.index', 'icon' => 'image', 'text' => 'Warta Sinode', 'active' => request()->routeIs('announcement.*')],
        ]],
        ['label' => 'Data', 'items' => [
            ['route' => 'songs.index', 'icon' => 'music', 'text' => 'Database Lagu', 'active' => request()->routeIs('songs.*')],
        ]],
        ['label' => 'Sistem', 'items' => [
            ['route' => 'settings.edit', 'icon' => 'settings', 'text' => 'Pengaturan & Logo', 'active' => request()->routeIs('settings.*')],
            ['route' => 'users.index', 'icon' => 'users', 'text' => 'Pengguna', 'active' => request()->routeIs('users.*')],
            ['route' => 'activity.index', 'icon' => 'book', 'text' => 'Riwayat', 'active' => request()->routeIs('activity.*')],
            ['route' => 'profile.edit', 'icon' => 'user', 'text' => 'Profil Saya', 'active' => request()->routeIs('profile.*')],
        ]],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="song-list-url" content="{{ route('api.daftar_lagu') }}">
    <meta name="theme-color" content="#0f1729">
    <title>@yield('title', 'Dashboard') · Sistem Multimedia {{ $church }}</title>
    <link rel="icon" href="{{ $logo }}">
    <link rel="apple-touch-icon" href="{{ $logo }}">
    @vite(['resources/css/admin-app.css', 'resources/js/admin.js'])
    @stack('styles')
</head>
<body class="adm" data-admin-layout>
<a href="#konten" class="skip-link">Lewati ke konten</a>
<div class="adm-shell">
    <aside class="adm-sidebar offcanvas-lg offcanvas-start" id="admSidebar" tabindex="-1" aria-label="Navigasi utama">
        <div class="adm-brand">
            <a href="{{ route('dashboard') }}" class="adm-brand-link">
                <span class="adm-brand-logo"><img src="{{ $logo }}" alt="" width="40" height="40" onerror="this.style.display='none'"></span>
                <span class="adm-brand-text">
                    <strong>Sistem Multimedia</strong>
                    <span>{{ $church }}</span>
                </span>
            </a>
            <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#admSidebar" aria-label="Tutup menu"></button>
        </div>

        <nav class="adm-nav">
            @foreach($nav as $group)
                <div class="adm-nav-label">{{ $group['label'] }}</div>
                @foreach($group['items'] as $item)
                    <a href="{{ route($item['route']) }}" @class(['adm-nav-link', 'is-active' => $item['active']]) @if($item['active']) aria-current="page" @endif>
                        <span>{{ $item['text'] }}</span>
                    </a>
                @endforeach
            @endforeach
        </nav>

        <div class="adm-user">
            <div class="adm-user-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(Auth::user()->name ?? 'A', 0, 1)) }}</div>
            <div class="adm-user-info">
                <strong>{{ Auth::user()->name }}</strong>
                <span>{{ Auth::user()->email }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="adm-logout" data-no-lock>Keluar</button>
            </form>
        </div>
    </aside>

    <div class="adm-main">
        <header class="adm-topbar">
            <button type="button" class="adm-icon-btn d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#admSidebar" aria-controls="admSidebar" aria-label="Buka menu">
                <x-admin-icon name="menu" />
            </button>
            <div class="adm-titles">
                @hasSection('eyebrow')<div class="adm-eyebrow">@yield('eyebrow')</div>@endif
                <h1>@yield('title', 'Dashboard')</h1>
            </div>
            @hasSection('actions')<div class="adm-actions">@yield('actions')</div>@endif
        </header>

        <main class="adm-content" id="konten" tabindex="-1">
            @if(session('success'))
                <div class="adm-flash adm-flash-success" role="status"><span>{{ session('success') }}</span></div>
            @endif
            @php
                $statusText = ['profile-updated' => 'Profil berhasil disimpan.', 'password-updated' => 'Kata sandi berhasil diganti.', 'verification-link-sent' => 'Tautan verifikasi sudah dikirim ke email Anda.'];
                $status = session('status');
            @endphp
            @if($status)
                <div class="adm-flash adm-flash-success" role="status"><span>{{ $statusText[$status] ?? $status }}</span></div>
            @endif
            @if(session('warnings'))
                <div class="adm-flash adm-flash-warning" role="status"><div><strong>Periksa sebelum ibadah:</strong><ul class="mb-0 ps-3">@foreach((array) session('warnings') as $w)<li>{{ $w }}</li>@endforeach</ul></div></div>
            @endif
            @if(session('error'))
                <div class="adm-flash adm-flash-error" role="alert"><span>{{ session('error') }}</span></div>
            @endif
            @if($errors->any())
                <div class="adm-flash adm-flash-error" role="alert">
                    <div>
                        <strong>Periksa kembali isian Anda:</strong>
                        <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="adm-footer">
            <span>&copy; {{ date('Y') }} Sistem Multimedia {{ $church }}</span>
            <span>Tuhan Yesus memberkati pelayanan kita</span>
        </footer>
    </div>
</div>

@stack('scripts')
</body>
</html>
