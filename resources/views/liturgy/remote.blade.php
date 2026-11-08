<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0f1013">
    <link rel="icon" href="{{ \App\Services\Branding::logoUrl() }}">
    <title>Remote: {{ str_replace(' (Custom)', '', $schedule->liturgy->name ?? 'Ibadah') }}</title>
    @php $remoteConfig = [
            'scheduleId' => $schedule->id,
            'slides' => $payload['slides'],
            'settings' => $payload['settings'],
            'version' => $payload['version'],
            'routes' => [
                'slides' => route('presenter.slides', $schedule),
                'state' => route('presenter.state', $schedule),
                'push' => route('presenter.state.push', $schedule),
            ],
        ]; @endphp
    <script>
        window.PRESENTER = @json($remoteConfig);
    </script>
    @vite(['resources/css/remote.css', 'resources/js/presenter/remote.js'])
</head>
<body class="rm">
    <header class="rm-head">
        <div>
            <strong>{{ str_replace(' (Custom)', '', $schedule->liturgy->name ?? 'Ibadah') }}</strong>
            <span>{{ \Carbon\Carbon::parse($schedule->worship_date)->locale('id')->translatedFormat('l, d F Y') }}</span>
        </div>
        <span class="rm-dot" id="rm-dot" title="Koneksi ke server"></span>
    </header>

    <main class="rm-main">
        <section class="rm-card rm-now">
            <div class="rm-label"><span>● TAYANG</span><span id="rm-now-num"></span></div>
            <div class="rm-section" id="rm-now-sec"></div>
            <div class="rm-text" id="rm-now-text"></div>
        </section>
        <section class="rm-card rm-next">
            <div class="rm-label"><span>BERIKUTNYA</span><span id="rm-next-num"></span></div>
            <div class="rm-text" id="rm-next-text"></div>
        </section>

        <div class="rm-toggles">
            <button type="button" data-rm="black" aria-pressed="false">Hitam</button>
            <button type="button" data-rm="white" aria-pressed="false">Putih</button>
            <button type="button" data-rm="hide" aria-pressed="false">Tanpa Teks</button>
            <button type="button" data-rm="sections">Bagian…</button>
        </div>

        <ol class="rm-sections" id="rm-sections" hidden></ol>
    </main>

    <footer class="rm-nav">
        <button type="button" data-rm="prev" aria-label="Sebelumnya">◀</button>
        <button type="button" data-rm="next" aria-label="Berikutnya">Berikutnya ▶</button>
    </footer>
</body>
</html>
