@php
    // Ikon Bootstrap Icons (resources/icons.php), gaya ribbon PowerPoint
    $ico = require resource_path('icons.php');
        $svg = fn ($name) => '<svg class="cp-ico" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">' . ($ico[$name] ?? '') . '</svg>';
        $bgNames = ['static' => 'Statis', 'aurora' => 'Aurora', 'flow' => 'Mengalir', 'bokeh' => 'Bokeh', 'waves' => 'Gelombang', 'rays' => 'Sinar', 'grid' => 'Grid', 'video' => 'Video'];
    @endphp
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">
        <link rel="icon" href="{{ \App\Services\Branding::logoUrl() }}">
        <title>Control Panel - {{ $schedule->theme ?: ($schedule->liturgy->name ?? 'Ibadah') }}</title>
        @php
            $presenterConfig = [
            'scheduleId' => $schedule->id,
            'slides' => $payload['slides'],
            'settings' => $payload['settings'],
            'version' => $payload['version'],
            'logoUrl' => \App\Services\DisplaySettings::logoUrl(),
            'videoUrl' => \App\Services\Branding::videoUrl(),
            'sw' => asset('sw.js'),
            'swScope' => rtrim(url('/'), '/') . '/',
            'routes' => [
                'projector' => route('liturgy.presentation', $schedule),
                'slides' => route('presenter.slides', $schedule),
                'settings' => route('presenter.settings', $schedule),
                'state' => route('presenter.state.push', $schedule),
                'insertSong' => route('presenter.insert_song', $schedule),
                'stateGet' => route('presenter.state', $schedule),
                'remote' => route('presenter.remote', $schedule),
                'lagu' => route('api.lagu'),
                'alkitab' => route('api.alkitab'),
            ],
        ];
    @endphp
    <script>
        window.PRESENTER = @json($presenterConfig);
    </script>
    @vite(['resources/css/presenter-stage.css', 'resources/css/control-panel.css', 'resources/js/presenter/control.js'])
</head>
<body class="cp">
<div class="cp-app">

    {{-- ================= BILAH JUDUL ================= --}}
    <header class="cp-titlebar">
        <div class="cp-brand">
            <img src="{{ \App\Services\DisplaySettings::logoUrl() }}" alt="" class="cp-brand-logo" width="30" height="30" onerror="this.style.display='none'">
            <div class="cp-brand-text">
                <strong>{{ str_replace(' (Custom)', '', $schedule->liturgy->name ?? 'Ibadah') }}</strong>
                <span>{{ \Carbon\Carbon::parse($schedule->worship_date)->locale('id')->translatedFormat('l, d F Y') }}@if($schedule->theme) &middot; {{ $schedule->theme }}@endif</span>
            </div>
        </div>
        <div class="cp-titlebar-mid">
            <span class="cp-pill" id="projector-status" title="Status layar live">Layar live: belum dibuka</span>
        </div>
        <div class="cp-titlebar-actions">
            {{-- Mode persiapan (Normal view) --}}
            <button type="button" class="cp-btn cp-btn-primary cp-only-normal" data-cmd="start-begin" title="Mulai tayangan dari slide pertama (F5)">{!! $svg('play') !!} Dari Awal</button>
            <button type="button" class="cp-btn cp-only-normal" data-cmd="start-here" title="Mulai tayangan dari slide yang dipilih (Shift+F5)">{!! $svg('play') !!} Dari Slide Ini</button>
            {{-- Mode tayang (Presenter view) --}}
            <span class="cp-live-dot cp-only-present">● SEDANG TAYANG</span>
            <button type="button" class="cp-btn cp-btn-danger cp-only-present" data-cmd="end-show" title="Akhiri tayangan, layar live kembali ke sampul (Esc 2×)">{!! $svg('stop') !!} Akhiri Tayangan</button>
            <a href="{{ route('liturgy.gallery') }}" class="cp-btn cp-btn-ghost" title="Tutup & kembali ke daftar jadwal">Tutup</a>
        </div>
    </header>

    {{-- ================= RIBBON (mode persiapan) ================= --}}
    <nav class="cp-tabs" role="tablist" aria-label="Menu ribbon">
        <button type="button" role="tab" class="cp-tab is-active" data-tab="home" aria-selected="true">Beranda</button>
        <button type="button" role="tab" class="cp-tab" data-tab="insert" aria-selected="false">Sisipkan</button>
        <button type="button" role="tab" class="cp-tab" data-tab="show" aria-selected="false">Tayangan Slide</button>
        <button type="button" role="tab" class="cp-tab" data-tab="background" aria-selected="false">Latar</button>
        <button type="button" role="tab" class="cp-tab" data-tab="text" aria-selected="false">Teks</button>
        <button type="button" role="tab" class="cp-tab" data-tab="song" aria-selected="false">Lagu</button>
        <button type="button" role="tab" class="cp-tab" data-tab="view" aria-selected="false">Layar</button>
        <button type="button" role="tab" class="cp-tab" data-tab="edit" aria-selected="false">Isi &amp; Cetak</button>
        <button type="button" class="cp-tab cp-tab-help" data-cmd="help" title="Daftar tombol pintas (?)">{!! $svg('help') !!}</button>
    </nav>

    <section class="cp-ribbon">
        {{-- BERANDA: memilih slide dan layar live --}}
        <div class="cp-panel is-active" data-panel="home" role="tabpanel">
            <div class="cp-group">
                <div class="cp-group-body">
                    <button type="button" class="cp-rb" data-cmd="search" title="Cari slide: lagu, bagian, atau kata (Ctrl+F)">{!! $svg('search') !!}<span>Cari Slide</span></button>
                    <button type="button" class="cp-rb" data-cmd="grid" title="Lihat semua slide (G)">{!! $svg('grid') !!}<span>Semua Slide</span></button>
                </div>
                <div class="cp-group-label">Temukan</div>
            </div>
            <div class="cp-group">
                <div class="cp-group-body">
                    <button type="button" class="cp-rb" data-cmd="first" title="Pilih slide pertama (Home)">{!! $svg('first') !!}<span>Awal</span></button>
                    <button type="button" class="cp-rb" data-cmd="prev" title="Pilih slide sebelumnya (↑ ←)">{!! $svg('prev') !!}<span>Sebelumnya</span></button>
                    <button type="button" class="cp-rb" data-cmd="next" title="Pilih slide berikutnya (↓ →)">{!! $svg('next') !!}<span>Berikutnya</span></button>
                    <button type="button" class="cp-rb" data-cmd="last" title="Pilih slide terakhir (End)">{!! $svg('last') !!}<span>Akhir</span></button>
                </div>
                <div class="cp-group-label">Pilih Slide (belum tayang)</div>
            </div>
            <div class="cp-group">
                <div class="cp-group-body">
                    <button type="button" class="cp-rb" data-cmd="edit-current" title="Edit isi slide ini (E)">{!! $svg('edit') !!}<span>Edit Slide</span></button>
                    <button type="button" class="cp-rb" data-cmd="toggle-hidden" aria-pressed="false" title="Sembunyikan slide ini: dilewati saat tayang (H)">{!! $svg('hide-slide') !!}<span>Sembunyikan</span></button>
                </div>
                <div class="cp-group-label">Slide Ini</div>
            </div>
            <div class="cp-group">
                <div class="cp-group-body cp-font-ctl">
                    <button type="button" class="cp-rb cp-rb-sm" data-cmd="font-down" title="Perkecil huruf slide ini (Ctrl -)"><b class="cp-glyph">A−</b><span>Kecil</span></button>
                    <div class="cp-font-value"><strong id="font-value" data-font-value>Otomatis</strong><button type="button" class="cp-link" data-cmd="font-reset" title="Kembali ke ukuran otomatis (Ctrl 0)">Reset</button></div>
                    <button type="button" class="cp-rb cp-rb-sm" data-cmd="font-up" title="Perbesar huruf slide ini (Ctrl +)"><b class="cp-glyph">A+</b><span>Besar</span></button>
                </div>
                <div class="cp-group-label">Ukuran Huruf Slide Ini</div>
            </div>
            <div class="cp-group">
                <div class="cp-group-body">
                    <button type="button" class="cp-rb" data-cmd="black" aria-pressed="false" title="Layar live hitam (B)">{!! $svg('black') !!}<span>Hitam</span></button>
                    <button type="button" class="cp-rb" data-cmd="white" aria-pressed="false" title="Layar live putih (W)">{!! $svg('white') !!}<span>Putih</span></button>
                </div>
                <div class="cp-group-label">Layar Live</div>
            </div>
        </div>

        {{-- SISIPKAN: tambahan mendadak saat ibadah --}}
        <div class="cp-panel" data-panel="insert" role="tabpanel">
            <div class="cp-group">
                <div class="cp-group-body">
                    <button type="button" class="cp-rb" data-cmd="song-insert" title="Sisipkan lagu dari database setelah bagian ini (L)">{!! $svg('insert-song') !!}<span>Lagu Dadakan</span></button>
                </div>
                <div class="cp-group-label">Lagu</div>
            </div>
            <div class="cp-group">
                <div class="cp-group-body">
                    <button type="button" class="cp-rb" data-cmd="ticker" aria-pressed="false" title="Teks pengumuman singkat di bawah layar (K)">{!! $svg('ticker') !!}<span>Teks Cepat</span></button>
                </div>
                <div class="cp-group-label">Pengumuman</div>
            </div>
            <div class="cp-group cp-group-info">
                <div class="cp-group-body">
                    <p class="cp-info">Lagu dadakan masuk setelah bagian yang sedang dipilih.<br>Teks cepat tampil di bawah layar tanpa mengganti slide.</p>
                </div>
                <div class="cp-group-label">Keterangan</div>
            </div>
        </div>

        {{-- TAYANGAN SLIDE (seperti tab "Slide Show" PowerPoint) --}}
        <div class="cp-panel" data-panel="show" role="tabpanel">
            <div class="cp-group">
                <div class="cp-group-body">
                    <button type="button" class="cp-rb cp-rb-lg cp-rb-start" data-cmd="start-begin" title="Mulai tayangan dari awal (F5)">{!! $svg('play') !!}<span>Dari Awal</span></button>
                    <button type="button" class="cp-rb" data-cmd="start-here" title="Mulai dari slide yang dipilih (Shift+F5)">{!! $svg('play') !!}<span>Dari Slide Ini</span></button>
                </div>
                <div class="cp-group-label">Mulai Tayangan</div>
            </div>
            <div class="cp-group">
                <div class="cp-group-body">
                    <button type="button" class="cp-rb" data-cmd="check" title="Periksa semua slide: huruf terlalu kecil, slide kosong">{!! $svg('check') !!}<span>Uji Tayang</span></button>
                    <button type="button" class="cp-rb" data-cmd="toggle-hidden" aria-pressed="false" title="Sembunyikan slide ini: dilewati saat tayang (H)">{!! $svg('hide-slide') !!}<span>Sembunyikan Slide</span></button>
                </div>
                <div class="cp-group-label">Siapkan</div>
            </div>
            <div class="cp-group">
                <div class="cp-group-body">
                    <button type="button" class="cp-rb" data-cmd="open-projector" title="Buka jendela layar live di monitor kedua">{!! $svg('display') !!}<span>Buka Layar Live</span></button>
                    <button type="button" class="cp-rb" data-cmd="copy-remote" title="Salin link layar live untuk komputer lain">{!! $svg('link') !!}<span>Link 2 Komputer</span></button>
                    <button type="button" class="cp-rb" data-cmd="phone-remote" title="Kendalikan slide dari HP (pindai QR)">{!! $svg('phone') !!}<span>Remote HP</span></button>
                </div>
                <div class="cp-group-label">Monitor</div>
            </div>
        </div>

        {{-- LATAR --}}
        <div class="cp-panel" data-panel="background" role="tabpanel">
            <div class="cp-group cp-group-grow">
                <div class="cp-group-body cp-bg-gallery" id="bg-gallery">
                    @foreach($backgrounds as $bg)
                        @continue($bg === 'video' && !\App\Services\Branding::videoUrl())
                        <button type="button" class="cp-bg-tile" data-bg="{{ $bg }}" title="{{ $bgNames[$bg] ?? $bg }}">
                            <span class="cp-bg-preview"></span>
                            <span class="cp-bg-name">{{ $bgNames[$bg] ?? $bg }}</span>
                        </button>
                    @endforeach
                </div>
                <div class="cp-group-label">Latar Belakang</div>
            </div>
            <div class="cp-group">
                <div class="cp-group-body cp-fields cp-fields-col">
                    <div class="cp-fields">
                        <label class="cp-color"><input type="color" data-setting="design.color1"><span>Utama</span></label>
                        <label class="cp-color"><input type="color" data-setting="design.color2"><span>Aksen</span></label>
                        <label class="cp-color"><input type="color" data-setting="design.color3"><span>Gelap</span></label>
                    </div>
                    <select id="season-preset" class="cp-season" title="Pakai warna masa raya gerejawi">
                        <option value="">Warna masa raya…</option>
                        <option value="standar">Standar (biru)</option>
                        <option value="biasa">Minggu Biasa (hijau)</option>
                        <option value="adven">Adven (ungu)</option>
                        <option value="natal">Natal (putih-emas)</option>
                        <option value="sengsara">Pra-Paskah (ungu tua)</option>
                        <option value="paskah">Paskah (putih-emas)</option>
                        <option value="pentakosta">Pentakosta (merah)</option>
                    </select>
                </div>
                <div class="cp-group-label">Warna</div>
            </div>
            <div class="cp-group">
                <div class="cp-group-body cp-fields cp-fields-col">
                    <label class="cp-range"><span>Kecepatan</span><input type="range" min="0.25" max="3" step="0.25" data-setting="design.speed"><output data-out="design.speed"></output></label>
                    <label class="cp-range"><span>Intensitas</span><input type="range" min="0" max="1" step="0.05" data-setting="design.intensity"><output data-out="design.intensity"></output></label>
                    <label class="cp-check"><input type="checkbox" data-setting="design.powerSave"><span>Hemat daya (latar diam)</span></label>
                </div>
                <div class="cp-group-label">Gerak</div>
            </div>
        </div>

        {{-- TEKS --}}
        <div class="cp-panel" data-panel="text" role="tabpanel">
            <div class="cp-group">
                <div class="cp-group-body cp-fields cp-fields-col">
                    <label class="cp-select"><span>Huruf</span>
                        <select data-setting="design.font">
                            @foreach($fonts as $font)<option value="{{ $font }}" style="font-family: '{{ $font }}'">{{ $font }}</option>@endforeach
                        </select>
                    </label>
                </div>
                <div class="cp-group-label">Jenis Huruf</div>
            </div>
            <div class="cp-group">
                <div class="cp-group-body cp-fields cp-fields-col">
                    <div class="cp-fields">
                        <label class="cp-color"><input type="color" data-setting="design.textColor"><span>Teks</span></label>
                        <label class="cp-color"><input type="color" data-setting="design.shadowColor"><span>Bayangan</span></label>
                    </div>
                    <label class="cp-range"><span>Tebal bayangan</span><input type="range" min="0" max="1" step="0.05" data-setting="design.shadowStrength"><output data-out="design.shadowStrength"></output></label>
                </div>
                <div class="cp-group-label">Warna Teks</div>
            </div>
            <div class="cp-group cp-group-info">
                <div class="cp-group-body">
                    <p class="cp-contrast" id="contrast-info" hidden></p>
                </div>
                <div class="cp-group-label">Keterbacaan</div>
            </div>
        </div>

        {{-- LAGU: gaya slide lirik --}}
        <div class="cp-panel" data-panel="song" role="tabpanel">
            <div class="cp-group cp-group-grow">
                <div class="cp-group-body cp-ss-gallery" id="song-style-gallery">
                    @foreach(['nomor' => 'Nomor Besar', 'terang' => 'Terang', 'klasik' => 'Klasik', 'kiri' => 'Rata Kiri'] as $ss => $ssName)
                        <button type="button" class="cp-ss-tile" data-song-style="{{ $ss }}" title="Gaya slide lirik: {{ $ssName }}">
                            <span class="cp-ss-preview"></span>
                            <span class="cp-bg-name">{{ $ssName }}</span>
                        </button>
                    @endforeach
                </div>
                <div class="cp-group-label">Gaya Slide Lirik</div>
            </div>
            <div class="cp-group">
                <div class="cp-group-body cp-fields cp-fields-col">
                    <label class="cp-check"><input type="checkbox" data-setting="design.songMark"><span>Warna lirik berbeda</span></label>
                    <label class="cp-color"><input type="color" data-setting="design.songColor"><span>Warna lirik</span></label>
                </div>
                <div class="cp-group-label">Pembeda Nyanyian</div>
            </div>
        </div>

        {{-- LAYAR: ukuran layar fisik & batas tulisan --}}
        <div class="cp-panel" data-panel="view" role="tabpanel">
            <div class="cp-group">
                <div class="cp-group-body cp-fields cp-fields-col">
                    <label class="cp-select"><span>Layar</span>
                        <select id="screen-preset">
                            <option value="43">TV 43 inci</option><option value="50">TV 50 inci</option><option value="55">TV 55 inci</option>
                            <option value="65">TV 65 inci</option><option value="75">TV 75 inci</option><option value="85">TV 85 inci</option>
                            <option value="100">Layar 100 inci</option><option value="custom">Proyektor / ukuran sendiri</option>
                        </select>
                    </label>
                    <div class="cp-fields">
                        <label class="cp-num"><span>Lebar</span><input type="number" min="20" max="2000" step="0.1" data-setting="screen.widthCm"><em>cm</em></label>
                        <label class="cp-num"><span>Tinggi</span><input type="number" min="10" max="1200" step="0.1" data-setting="screen.heightCm"><em>cm</em></label>
                    </div>
                </div>
                <div class="cp-group-label">Ukuran Layar Fisik</div>
            </div>
            <div class="cp-group">
                <div class="cp-group-body cp-fields">
                    <label class="cp-num cp-num-v"><span>Atas</span><input type="number" min="0" step="0.5" data-setting="screen.marginTopCm"><em>cm</em></label>
                    <label class="cp-num cp-num-v"><span>Kiri &amp; kanan</span><input type="number" min="0" step="0.5" data-setting="screen.marginSideCm"><em>cm</em></label>
                    <label class="cp-num cp-num-v"><span>Bawah</span><input type="number" min="0" step="0.5" data-setting="screen.marginBottomCm"><em>cm</em></label>
                </div>
                <div class="cp-group-label">Batas Kotak Tulisan</div>
            </div>
            <div class="cp-group">
                <div class="cp-group-body cp-fields cp-fields-col">
                    <label class="cp-check"><input type="checkbox" data-setting="header.show"><span>Tampilkan judul slide</span></label>
                    <label class="cp-check"><input type="checkbox" data-setting="header.logo"><span>Tampilkan logo</span></label>
                </div>
                <div class="cp-group-label">Area Atas (per jadwal)</div>
            </div>
            <div class="cp-group">
                <div class="cp-group-body">
                    <button type="button" class="cp-rb" data-cmd="guides" aria-pressed="false" title="Tampilkan garis batas di layar live untuk diukur dengan meteran">{!! $svg('guides') !!}<span>Garis Batas</span></button>
                </div>
                <div class="cp-group-label">Kalibrasi</div>
            </div>
            <div class="cp-group cp-group-info">
                <div class="cp-group-body">
                    <p class="cp-info" id="layout-info"></p>
                </div>
                <div class="cp-group-label">Hasil di Kanvas 1920×1080</div>
            </div>
        </div>

        {{-- ISI & CETAK --}}
        <div class="cp-panel" data-panel="edit" role="tabpanel">
            <div class="cp-group">
                <div class="cp-group-body">
                    <button type="button" class="cp-rb" data-cmd="toggle-editor" aria-pressed="false" title="Buka/tutup panel edit (E)">{!! $svg('edit') !!}<span>Panel Edit</span></button>
                    <button type="button" class="cp-rb" data-cmd="save-now" title="Simpan sekarang (Ctrl S)">{!! $svg('save') !!}<span>Simpan</span></button>
                    <a class="cp-rb" href="{{ route('liturgy.edit', $schedule) }}" title="Halaman edit lengkap">{!! $svg('document') !!}<span>Edit Lengkap</span></a>
                </div>
                <div class="cp-group-label">Isi Slide</div>
            </div>
            <div class="cp-group">
                <div class="cp-group-body">
                    <a class="cp-rb" href="{{ route('liturgy.export_pdf', $schedule) }}" target="_blank" rel="noopener" title="Cetak / PDF slide">{!! $svg('pdf') !!}<span>Slide PDF</span></a>
                    <a class="cp-rb" href="{{ route('liturgy.bulletin', $schedule) }}" target="_blank" rel="noopener" title="Cetak buletin tata ibadah (booklet A4 dilipat)">{!! $svg('printer') !!}<span>Buletin</span></a>
                </div>
                <div class="cp-group-label">Cetak</div>
            </div>
        </div>
    </section>

    {{-- ================= AREA KERJA ================= --}}
    <main class="cp-main">
        <aside class="cp-thumbs" id="thumbs" aria-label="Daftar slide"></aside>

        <section class="cp-center">
            <div class="cp-caption">
                <span id="main-caption">Pratinjau</span>
                <span class="cp-state-badges cp-only-present">
                    <span class="cp-badge cp-badge-black" data-badge="black" hidden>LAYAR HITAM</span>
                    <span class="cp-badge cp-badge-white" data-badge="white" hidden>LAYAR PUTIH</span>
                    <span class="cp-badge" data-badge="hide" hidden>TEKS DISEMBUNYIKAN</span>
                    <span class="cp-badge cp-badge-cam" id="badge-cam" hidden>KAMERA</span>
                    <span class="cp-badge cp-badge-ticker" data-badge="ticker" hidden>TEKS CEPAT TAMPIL</span>
                </span>
                <span class="cp-badge cp-badge-hidden" id="badge-hidden-slide" hidden>SLIDE DISEMBUNYIKAN</span>
            </div>
            <div class="cp-stage-current" id="stage-current"></div>

            {{-- Catatan operator (seperti "Klik untuk menambahkan catatan" di PowerPoint) --}}
            <textarea class="cp-notes-input cp-only-normal" id="notes-input" rows="2" maxlength="1000"
                placeholder="Klik untuk menambahkan catatan operator slide ini (tidak tampil di layar live)…"></textarea>

            {{-- Alat Presenter View --}}
            <div class="cp-ptools cp-only-present">
                <button type="button" class="cp-pt" data-cmd="grid" title="Lihat semua slide (G)">{!! $svg('grid') !!}<span>Semua Slide</span></button>
                <button type="button" class="cp-pt" data-cmd="search" title="Cari slide (Ctrl+F)">{!! $svg('search') !!}<span>Cari</span></button>
                <button type="button" class="cp-pt" data-cmd="song-insert" title="Lagu dadakan (L)">{!! $svg('insert-song') !!}<span>Lagu Dadakan</span></button>
                <button type="button" class="cp-pt" data-cmd="ticker" aria-pressed="false" title="Teks cepat di bawah layar (K)">{!! $svg('ticker') !!}<span>Teks Cepat</span></button>
                <span class="cp-pt-sep"></span>
                <button type="button" class="cp-pt" data-cmd="black" aria-pressed="false" title="Layar hitam (B)">{!! $svg('black') !!}<span>Hitam</span></button>
                <button type="button" class="cp-pt" data-cmd="white" aria-pressed="false" title="Layar putih (W)">{!! $svg('white') !!}<span>Putih</span></button>
                <button type="button" class="cp-pt" data-cmd="hide-text" aria-pressed="false" title="Sembunyikan teks, latar tetap tampil (T)">{!! $svg('hide-text') !!}<span>Tanpa Teks</span></button>
                <span class="cp-pt-sep"></span>
                <button type="button" class="cp-pt" data-cmd="font-down" title="Perkecil huruf slide ini (Ctrl -)"><b class="cp-glyph">A−</b></button>
                <button type="button" class="cp-pt cp-pt-value" data-cmd="font-reset" title="Ukuran huruf. Klik untuk kembali otomatis"><span data-font-value>-</span></button>
                <button type="button" class="cp-pt" data-cmd="font-up" title="Perbesar huruf slide ini (Ctrl +)"><b class="cp-glyph">A+</b></button>
                <span class="cp-pt-sep"></span>
                <button type="button" class="cp-pt" data-cmd="edit-current" title="Perbaiki isi slide ini (E)">{!! $svg('edit') !!}<span>Edit</span></button>
            </div>

            <div class="cp-navbar">
                <div class="cp-nav-group">
                    <button type="button" class="cp-navbtn" data-cmd="prev" title="Sebelumnya (←)">{!! $svg('prev') !!}</button>
                    <label class="cp-counter" title="Ketik nomor slide lalu Enter">
                        <span>Slide</span>
                        <input type="number" id="slide-input" min="1" value="1">
                        <span>dari <b id="slide-total">0</b></span>
                    </label>
                    <button type="button" class="cp-navbtn" data-cmd="next" title="Berikutnya (→)">{!! $svg('next') !!}</button>
                </div>
                <div class="cp-nav-group cp-timer">
                    <span class="cp-timer-value" id="timer">00:00:00</span>
                    <button type="button" class="cp-link" data-cmd="timer-toggle" id="timer-toggle">Jeda</button>
                    <button type="button" class="cp-link" data-cmd="timer-reset" title="Ulang timer">{!! $svg('reset') !!}</button>
                </div>
                <div class="cp-nav-group cp-clock" id="clock">--:--</div>
            </div>
        </section>

        <aside class="cp-side">
            <div class="cp-caption">
                <span id="side-caption">Layar live sekarang</span>
                <span class="cp-state-badges cp-only-normal">
                    <span class="cp-badge cp-badge-black" data-badge="black" hidden>HITAM</span>
                    <span class="cp-badge cp-badge-white" data-badge="white" hidden>PUTIH</span>
                    <span class="cp-badge" data-badge="hide" hidden>TANPA TEKS</span>
                    <span class="cp-badge cp-badge-ticker" data-badge="ticker" hidden>TEKS CEPAT</span>
                </span>
                <span class="cp-muted" id="next-label"></span>
            </div>
            <div class="cp-stage-next" id="stage-next"></div>
            <div class="cp-cue" id="cue-bar" hidden>
                <span id="cue-text"></span>
                <button type="button" class="cp-link" data-cmd="cue-clear">Batal</button>
            </div>
            <div class="cp-caption cp-only-present"><span>Catatan</span></div>
            <div class="cp-notes cp-only-present" id="notes-view" aria-label="Catatan operator slide yang sedang tayang"></div>
            <div class="cp-caption"><span>Urutan Ibadah</span><span class="cp-muted" id="outline-hint"></span></div>
            <ol class="cp-outline" id="outline"></ol>
        </aside>

        <aside class="cp-editor" id="editor" aria-label="Panel edit isi" aria-hidden="true">
            <div class="cp-editor-head">
                <strong>Edit Isi Slide</strong>
                <span class="cp-muted">Tersimpan otomatis</span>
                <button type="button" class="cp-btn cp-btn-ghost cp-btn-sm" data-cmd="undo" title="Urungkan perubahan terakhir (Ctrl+Z)">↶ Urungkan</button>
                <button type="button" class="cp-btn cp-btn-ghost cp-btn-sm" data-cmd="toggle-editor">Tutup</button>
            </div>
            <div class="cp-editor-body">
                @include('liturgy.partials.control_editor', ['schedule' => $schedule])
            </div>
        </aside>
    </main>

    <footer class="cp-statusbar">
        <span class="cp-mode" id="status-mode">Mode persiapan</span>
        <span id="status-slide">Slide 1 dari 1</span>
        <span id="status-section"></span>
        <span class="cp-status-spacer"></span>
        <span id="status-save">Semua perubahan tersimpan</span>
        <span id="status-net" class="is-offline" hidden>● Offline. Tayangan tetap jalan, perubahan isi belum tersimpan</span>
        <span id="status-sync">Sinkron: 1 PC</span>
        <button type="button" class="cp-link" data-cmd="help">Tombol pintas (?)</button>
    </footer>
</div>

{{-- Semua slide (G) --}}
<div class="cp-overlay" id="grid-overlay" hidden>
    <div class="cp-overlay-head">
        <strong>Semua Slide</strong>
        <span class="cp-muted" id="grid-hint">Klik = pilih &middot; Esc untuk menutup</span>
        <button type="button" class="cp-btn cp-btn-ghost cp-btn-sm" data-cmd="grid">Tutup</button>
    </div>
    <div class="cp-grid" id="grid"></div>
</div>

{{-- Cari slide (Ctrl+F) --}}
<dialog class="cp-dialog cp-search" id="search-dlg" aria-label="Cari slide">
    <input type="search" id="search-input" class="cp-input" placeholder="Ketik KJ 85, nama bagian (mis. Pengakuan), atau sepenggal kata…" autocomplete="off">
    <ol class="cp-search-results" id="search-results"></ol>
    <p class="cp-dialog-foot" id="search-hint"></p>
</dialog>

{{-- Lagu dadakan --}}
<dialog class="cp-dialog cp-songdlg" id="song-dlg" aria-label="Lagu dadakan">
    <h2>Lagu Dadakan</h2>
    <form id="song-form" class="cp-songdlg-row" autocomplete="off">
        <select id="sd-book" class="cp-input">
            <option>KJ</option><option>NKB</option><option>PKJ</option><option>NR</option><option>DSL</option><option>GPM</option><option value="BEBAS">Lainnya</option>
        </select>
        <input id="sd-num" class="cp-input" placeholder="Nomor (atau judul untuk Lainnya)">
        <button type="submit" class="cp-btn">Cari</button>
    </form>
    <div id="sd-preview" class="cp-songdlg-preview"></div>
    <p class="cp-dialog-foot" id="sd-where"></p>
    <div class="cp-dialog-actions">
        <button type="button" class="cp-btn cp-btn-ghost" data-sd="close">Batal</button>
        <button type="button" class="cp-btn cp-btn-primary" data-sd="insert" disabled>Sisipkan</button>
    </div>
</dialog>

{{-- Teks cepat --}}
<dialog class="cp-dialog cp-tickerdlg" id="ticker-dlg" aria-label="Teks cepat">
    <h2>Teks Cepat di Bawah Layar</h2>
    <p class="cp-dialog-foot">Muncul di bagian bawah layar live tanpa mengganti slide. Contoh: <i>Kendaraan DS 1234 mohon dipindahkan.</i></p>
    <textarea id="ticker-input" class="cp-input" rows="2" maxlength="200" placeholder="Ketik pengumuman singkat…"></textarea>
    <div class="cp-ticker-recent" id="ticker-recent"></div>
    <div class="cp-dialog-actions">
        <button type="button" class="cp-btn cp-btn-ghost" data-tk="close">Tutup</button>
        <button type="button" class="cp-btn" data-tk="hide">Sembunyikan</button>
        <button type="button" class="cp-btn cp-btn-primary" data-tk="show">Tampilkan</button>
    </div>
</dialog>

{{-- Remote HP --}}
<dialog class="cp-dialog cp-remotedlg" id="remote-dlg" aria-label="Remote HP">
    <h2>Remote dari HP</h2>
    <p class="cp-dialog-foot">Pindai QR dengan kamera HP (HP harus bisa membuka alamat web ini dan login). Tombol <b>Berikutnya</b> di HP langsung menggerakkan layar live.</p>
    <div class="cp-qr" id="remote-qr"></div>
    <input class="cp-input" id="remote-url" readonly>
    <div class="cp-dialog-actions"><button type="button" class="cp-btn" data-rq="copy">Salin link</button><button type="button" class="cp-btn cp-btn-primary" data-rq="close">Tutup</button></div>
</dialog>

{{-- Uji tayang --}}
<dialog class="cp-dialog cp-checkdlg" id="check-dlg" aria-label="Hasil uji tayang">
    <h2>Uji Tayang</h2>
    <p class="cp-dialog-foot" id="check-summary"></p>
    <ol class="cp-search-results" id="check-results"></ol>
    <div class="cp-dialog-actions"><button type="button" class="cp-btn cp-btn-primary" data-ck="close">Tutup</button></div>
</dialog>

{{-- Menu klik kanan --}}
<div class="cp-menu" id="ctx-menu" role="menu" hidden></div>

<div class="cp-jump" id="jump" hidden></div>
<div class="cp-toast" id="toast" role="status" aria-live="polite"></div>

<dialog class="cp-dialog" id="help">
    <h2>Tombol Pintas</h2>
    <p class="cp-help-intro">Sebelum tayangan dimulai, klik slide hanya untuk melihat. Mulai tayang dengan <kbd>F5</kbd>. Saat tayang, klik sekali menyiapkan slide berikutnya, dan slide baru tampil setelah <kbd>→</kbd> atau <kbd>Spasi</kbd> ditekan.</p>
    <table>
        <tr><th colspan="2">Saat tayang</th></tr>
        <tr><td><kbd>→</kbd> <kbd>↓</kbd> <kbd>Spasi</kbd> <kbd>Enter</kbd> <kbd>N</kbd> <kbd>PgDn</kbd></td><td>Tayangkan slide berikutnya (slide tersembunyi dilewati)</td></tr>
        <tr><td><kbd>←</kbd> <kbd>↑</kbd> <kbd>Backspace</kbd> <kbd>P</kbd> <kbd>PgUp</kbd></td><td>Slide sebelumnya</td></tr>
        <tr><td><kbd>Home</kbd> / <kbd>End</kbd></td><td>Slide pertama / terakhir</td></tr>
        <tr><td>Angka lalu <kbd>Enter</kbd></td><td>Langsung ke nomor slide</td></tr>
        <tr><td>Klik / klik dua kali</td><td>Siapkan sebagai berikutnya / tayangkan sekarang</td></tr>
        <tr><td><kbd>B</kbd> atau <kbd>.</kbd> · <kbd>W</kbd> atau <kbd>,</kbd></td><td>Layar hitam · layar putih (tekan lagi untuk kembali)</td></tr>
        <tr><td><kbd>T</kbd></td><td>Sembunyikan teks (latar tetap tampil)</td></tr>
        <tr><td><kbd>Esc</kbd> 2×</td><td>Akhiri tayangan (layar live kembali ke sampul)</td></tr>
        <tr><th colspan="2">Kapan saja</th></tr>
        <tr><td><kbd>F5</kbd> / <kbd>Shift</kbd>+<kbd>F5</kbd></td><td>Mulai tayang dari awal / dari slide yang dipilih</td></tr>
        <tr><td><kbd>G</kbd> · <kbd>E</kbd> · <kbd>H</kbd></td><td>Semua slide · edit isi slide · sembunyikan slide</td></tr>
        <tr><td><kbd>Ctrl</kbd>+<kbd>F</kbd> · <kbd>L</kbd> · <kbd>K</kbd></td><td>Cari slide · lagu dadakan · teks cepat di bawah layar</td></tr>
        <tr><td>Klik kanan</td><td>Menu cepat (slide, thumbnail, semua slide)</td></tr>
        <tr><td><kbd>Ctrl</kbd> <kbd>+</kbd> / <kbd>Ctrl</kbd> <kbd>−</kbd> / <kbd>Ctrl</kbd> <kbd>0</kbd></td><td>Ukuran huruf slide ini: besar / kecil / otomatis</td></tr>
        <tr><td><kbd>Ctrl</kbd> <kbd>S</kbd> · <kbd>Ctrl</kbd> <kbd>Z</kbd></td><td>Simpan isi sekarang · urungkan perubahan isi terakhir</td></tr>
    </table>
    <form method="dialog"><button class="cp-btn cp-btn-primary">Tutup</button></form>
</dialog>
</body>
</html>
