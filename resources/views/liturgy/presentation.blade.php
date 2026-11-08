<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ \App\Services\Branding::logoUrl() }}">
    <title>Layar Live - {{ $schedule->theme ?: ($schedule->liturgy->name ?? 'Ibadah') }}</title>
    <style>
        html, body { margin: 0; height: 100%; background: #000; overflow: hidden; }
        body.hide-cursor, body.hide-cursor * { cursor: none !important; }
        #projector { position: fixed; inset: 0; }
        #start-overlay {
            position: fixed; inset: 0; z-index: 10; cursor: pointer;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px;
            background: rgba(0, 0, 0, .88); color: #e2e8f0; font-family: Inter, Tahoma, sans-serif; text-align: center;
        }
        #start-overlay[hidden] { display: none; }
        #start-overlay img { height: 96px; width: auto; }
        #start-overlay strong { font-size: clamp(22px, 2.6vw, 44px); letter-spacing: 2px; text-transform: uppercase; color: #63b3ed; }
        #start-overlay span { font-size: clamp(14px, 1.2vw, 20px); color: #a0aec0; }
        #start-overlay kbd { background: #2d3748; border-radius: 4px; padding: 2px 8px; color: #fff; }
        #sync-dot { position: fixed; right: 10px; bottom: 10px; width: 10px; height: 10px; border-radius: 50%; z-index: 5; opacity: .5; }
        #sync-dot.is-ok { background: #38a169; }
        #sync-dot.is-error { background: #e53e3e; opacity: 1; }
    </style>
    @php
        $presenterConfig = [
            'scheduleId' => $schedule->id,
            'slides' => $payload['slides'],
            'settings' => $payload['settings'],
            'version' => $payload['version'],
            'remote' => $remote,
            'logoUrl' => \App\Services\DisplaySettings::logoUrl(),
            'videoUrl' => \App\Services\Branding::videoUrl(),
            'sw' => asset('sw.js'),
            'swScope' => rtrim(url('/'), '/') . '/',
            'routes' => [
                'slides' => route('presenter.slides', $schedule),
                'state' => route('presenter.state', $schedule),
            ],
        ];
    @endphp
    <script>
        window.PRESENTER = @json($presenterConfig);
    </script>
    @vite(['resources/css/presenter-stage.css', 'resources/js/presenter/projector.js'])
</head>
<body data-live>
    <div id="projector"></div>
    <div id="sync-dot" @if(!$remote) hidden @endif></div>

    <div id="start-overlay">
        <img src="{{ \App\Services\DisplaySettings::logoUrl() }}" alt="" width="120" height="120" onerror="this.style.display='none'">
        <strong>Layar Live Siap</strong>
        <span>Klik di mana saja untuk masuk layar penuh &middot; tekan <kbd>F</kbd> untuk keluar/masuk layar penuh</span>
        @if($remote)
            <span>Mode 2 komputer: layar ini mengikuti control panel lewat jaringan</span>
        @endif
    </div>
</body>
</html>
