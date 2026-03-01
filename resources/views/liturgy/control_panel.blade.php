<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Live Control Panel - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&family=Montserrat:wght@600;800&family=Playfair+Display:wght@600;800&family=Roboto:wght@500;700&family=Oswald:wght@500;700&family=Lato:wght@700;900&display=swap');
        
        body { background: #121212; color: #e0e0e0; height: 100vh; overflow: hidden; font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: #444; border-radius: 3px; }
        
        /* SIDEBAR COMPACT */
        .sidebar { height: 100vh; overflow-y: auto; background: #1a1a1a; border-right: 1px solid #2d2d2d; padding: 15px; padding-bottom: 80px;}
        .card-edit { background: #242424; border: 1px solid #333; border-radius: 6px; padding: 12px; margin-bottom: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);}
        .form-label-header { font-weight: 600; color: #a0aec0; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px; margin-bottom: 8px; display: block; border-bottom: 1px solid #333; padding-bottom: 4px; }
        textarea.form-control, input.form-control, select.form-control { background: #181818 !important; color: #cbd5e0 !important; border: 1px solid #333 !important; font-size: 0.8rem; border-radius: 4px; padding: 6px 10px;}
        textarea.form-control:focus, input.form-control:focus, select.form-control:focus { border-color: #3182ce !important; box-shadow: none; color: #fff !important; }
        .input-group-text { background: #2d2d2d !important; color: #a0aec0 !important; border-color: #333 !important; font-size: 0.75rem; border-radius: 4px 0 0 4px; padding: 4px 8px;}
        .custom-slide-box { background: #1e1e1e; border: 1px solid #4a5568; border-radius: 4px; padding: 8px; margin-top: 8px; border-left: 3px solid #4299e1; }
        .btn-add-slide { font-size: 0.7rem; font-weight: 500; border: 1px dashed #4a5568; color: #a0aec0; background: transparent; padding: 5px; width: 100%; border-radius: 4px; transition: 0.2s;}
        .btn-add-slide:hover { border-color: #cbd5e0; color: #fff; }
        .btn-primary-custom { background-color: #2b6cb0; color: white; border: none; font-weight: 600; font-size: 0.85rem; letter-spacing: 0.5px; }
        .btn-primary-custom:hover { background-color: #2c5282; color: white; }

        /* PRESENTER VIEW KANAN */
        .presenter-view { height: 100vh; background: #0d0d0d; display: flex; flex-direction: column; }
        .preview-header { padding: 10px 24px; background: #141414; border-bottom: 1px solid #2d2d2d; display: flex; justify-content: space-between; align-items: center; }
        .slide-monitor { flex-grow: 1; display: flex; padding: 20px; gap: 20px; justify-content: center; align-items: center; }
        
        .monitor-box { background: #000; position: relative; border-radius: 6px; box-shadow: 0 0 20px rgba(0,0,0,0.5); overflow: hidden; }
        .current-slide-box { width: 55%; aspect-ratio: 16/9; border: 2px solid #4299e1; box-shadow: 0 0 20px rgba(66, 153, 225, 0.15); }
        .next-slide-box { width: 25%; aspect-ratio: 16/9; border: 1px solid #333; opacity: 0.8; }
        .label-badge { position: absolute; top: 10px; left: 10px; font-size: 0.65rem; font-weight: 600; letter-spacing: 0.5px; padding: 3px 8px; border-radius: 3px; background: #2b6cb0; color: white; z-index: 50; box-shadow: 0 2px 5px rgba(0,0,0,0.5);}

        /* CSS CONTAINER QUERIES (Mesin Virtual Proyektor) */
        :root {
            --bg-center: #1b2735; --bg-edge: #050505; --text-color: #ffffff;
            --shadow-color: rgba(0,0,0,0.9); --font-family: 'Inter', sans-serif;
        }
        @keyframes gradientBG { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        @keyframes moveGridCP { 0% { background-position: 0 0; } 100% { background-position: 4cqw 4cqw; } }
        @keyframes moveStripesCP { 0% { background-position: 0 0; } 100% { background-position: 4cqw 0; } }

        .sp-container {
            container-type: inline-size; width: 100%; height: 100%;
            background: radial-gradient(circle at center, var(--bg-center) 0%, var(--bg-edge) 100%);
            color: var(--text-color); font-family: var(--font-family);
            display: flex; flex-direction: column; justify-content: flex-start; align-items: center; text-align: center;
            padding: 6cqh 4cqw; box-sizing: border-box; position: absolute; top:0; left:0; border-radius: inherit;
        }
        
        .vp-watermark { position: absolute; top: -3cqh; left: 3cqw; font-size: 55cqh; font-weight: 900; line-height: 1; z-index: 1; background: linear-gradient(180deg, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0.02) 80%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .vp-title-header { position: relative; z-index: 2; font-size: 2.2cqw; color: rgba(255, 255, 255, 0.7); text-transform: uppercase; letter-spacing: 0.4cqw; font-weight: 600; margin-top: 2cqh; border-bottom: 2px solid rgba(255, 255, 255, 0.15); padding-bottom: 1.5cqh; width: 85%; flex-shrink:0;}
        .vp-title-header.text-info { color: #63b3ed !important; border-bottom-color: rgba(99, 179, 237, 0.3) !important; }
        .vp-content { position: relative; z-index: 2; margin-top: auto; margin-bottom: auto; font-size: 5cqw; font-weight: 700; line-height: 1.45; text-shadow: 0px 4cqh 15cqw var(--shadow-color); max-width: 95%; }
        .vp-instruksi { position: relative; z-index: 2; margin: auto; font-size: 5.5cqw; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4cqw; text-shadow: 0px 4cqh 20cqw var(--shadow-color); }

        .per-slide-font-control { position: absolute; bottom: 10px; right: 10px; background: rgba(20,20,20,0.85); border: 1px solid #4299e1; border-radius: 6px; padding: 4px 10px; display: flex; gap: 8px; align-items: center; z-index: 50; backdrop-filter: blur(5px); }
        .per-slide-font-control button { background: #2d2d2d; color: white; border: 1px solid #444; padding: 2px 10px; border-radius: 4px; font-weight: bold; cursor: pointer; transition: 0.2s;}
        .per-slide-font-control button:hover { background: #4299e1; border-color: #4299e1; }
        #slide-font-indicator { color: #fcd34d; font-size: 0.75rem; font-weight: bold; min-width: 45px; text-align: center; }

        /* GALERI BAWAH - SIMPLE TEXT LIST (HITAM, BESAR, JELAS) */
        .slide-gallery-container { height: 160px; background: #141414; border-top: 1px solid #2d2d2d; overflow-x: auto; overflow-y: hidden; white-space: nowrap; padding: 15px; scroll-behavior: smooth; }
        
        .slide-thumb-simple {
            display: inline-flex; flex-direction: column; width: 220px; height: 100%;
            background: #111; border: 2px solid #333; margin-right: 15px; padding: 12px 15px;
            cursor: pointer; position: relative; transition: 0.2s; border-radius: 6px;
            vertical-align: top; overflow: hidden; white-space: normal;
        }
        .slide-thumb-simple:hover { border-color: #718096; background: #1a1a1a; transform: translateY(-2px); }
        .slide-thumb-simple.active { border-color: #4299e1; background: #0f172a; box-shadow: 0 0 15px rgba(66, 153, 225, 0.4); }
        
        .thumb-num-badge { position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.8); color: #fff; font-size: 0.65rem; font-weight: bold; padding: 3px 6px; border-radius: 4px; z-index: 10; border: 1px solid #444;}
        .thumb-simple-title { font-size: 0.65rem; color: #63b3ed; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; border-bottom: 1px solid #333; padding-bottom: 5px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; padding-right: 25px;}
        .thumb-simple-content { font-size: 0.9rem; color: #e2e8f0; font-weight: 600; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;}

        .nav-controls { background: #141414; border-top: 1px solid #2d2d2d; display: flex; justify-content: center; align-items: center; gap: 50px; padding: 10px; }
        .nav-btn { background: #2d2d2d; border: 1px solid #4a5568; color: #e2e8f0; padding: 8px 30px; border-radius: 4px; font-weight: 500; font-size: 0.85rem; transition: 0.2s; letter-spacing: 0.5px;}
        .nav-btn:hover { background: #4a5568; color: #fff; }
    </style>
</head>
<body onkeydown="handleKeyboard(event)">

@php
    $liturgyItems = $schedule->liturgy ? $schedule->liturgy->items : collect();
    $scheduleDetails = $schedule->details ? $schedule->details->keyBy('liturgy_item_id') : collect();
    $customSlides = $schedule->customSlides ? $schedule->customSlides->groupBy('liturgy_item_id') : collect();

    if (!function_exists('autoSplitText')) {
        function autoSplitText($text) {
            if (is_array($text)) { $text = $text['content'] ?? ''; }
            if (!is_string($text)) $text = '';
            if (str_contains($text, '===SLIDE_BREAK===')) return array_filter(array_map('trim', explode('===SLIDE_BREAK===', $text)));
            
            $text = preg_replace("/[\r\n]+/", "\n", trim($text));
            $paragraphs = explode("\n", $text);
            $slides = []; $currentSlide = [];
            $maxCharsPerLine = 35; 

            foreach ($paragraphs as $para) {
                $para = trim($para); if (empty($para)) continue;
                $words = explode(' ', $para); $currentLine = '';
                foreach ($words as $word) {
                    if (strlen($currentLine) + strlen($word) + 1 > $maxCharsPerLine && !empty($currentLine)) {
                        $currentSlide[] = trim($currentLine);
                        $currentLine = $word;
                        if (count($currentSlide) >= 3) {
                            $slides[] = implode("\n", $currentSlide);
                            $currentSlide = [];
                        }
                    } else { $currentLine = empty($currentLine) ? $word : $currentLine . ' ' . $word; }
                }
                if (!empty($currentLine)) {
                    $currentSlide[] = trim($currentLine);
                    if (count($currentSlide) >= 3) {
                        $slides[] = implode("\n", $currentSlide);
                        $currentSlide = [];
                    }
                }
            }
            if (!empty($currentSlide)) { $slides[] = implode("\n", $currentSlide); }
            
            $finalSlides = []; $total = count($slides);
            for ($i = 0; $i < $total; $i++) {
                if ($i === $total - 1 && $total > 1) {
                    $wordCount = str_word_count(strip_tags(str_replace("\n", " ", $slides[$i])));
                    if ($wordCount <= 4) { 
                        $lastIndex = count($finalSlides) - 1;
                        $finalSlides[$lastIndex] .= ' ' . str_replace("\n", " ", trim($slides[$i]));
                        continue; 
                    }
                }
                $finalSlides[] = $slides[$i];
            }
            return empty($finalSlides) ? [$text] : $finalSlides;
        }
    }

    $allSlides = [];
    $allSlides[] = [
        'type' => 'cover',
        'title' => strtoupper($schedule->liturgy->name ?? 'IBADAH'),
        'date' => \Carbon\Carbon::parse($schedule->worship_date)->translatedFormat('l, d F Y'),
        'theme' => strtoupper($schedule->theme ?? ''),
        'preacher' => strtoupper($schedule->preacher_name ?? '')
    ];

    foreach($liturgyItems as $item) {
        $detail = $scheduleDetails->get($item->id);
        $content = $detail ? $detail->dynamic_content : $item->static_content;
        $isEmptyArray = is_array($content) && empty($content['judul']) && empty($content['bait']) && empty($content['content']);
        $isEmptyString = !is_array($content) && trim($content) === '';
        $isInstruksi = str_contains(strtolower($item->title), 'sikap') || str_contains(strtolower($item->title), 'aksi');

        if($content && !$isEmptyArray && !$isEmptyString) {
            if($isInstruksi) {
                $textInstruksi = is_array($content) ? ($content['content'] ?? $content[0] ?? '') : $content;
                $allSlides[] = ['type' => 'instruksi', 'title' => $item->title, 'content' => $textInstruksi];
            } elseif(is_array($content)) {
                if(isset($content['custom_title'])) {
                    $slidesText = autoSplitText($content['content'] ?? '');
                    foreach($slidesText as $st) {
                        $allSlides[] = ['type' => 'text', 'title' => $content['custom_title'], 'content' => $st];
                    }
                } elseif(!empty($content['bait'])) {
                    $allSlides[] = [
                        'type' => 'song_cover', 
                        'title' => str_replace(' (Opsional)', '', $item->title),
                        'content' => ($content['judul'] ?? '')
                    ];
                    foreach($content['bait'] as $key => $bait) {
                        $baitSlides = autoSplitText($bait);
                        foreach($baitSlides as $bSlide) {
                            $allSlides[] = [
                                'type' => 'song_lyric',
                                'watermark' => $key,
                                'title' => str_replace(' (Opsional)', '', $item->title) . (!empty($content['judul']) ? ' - ' . $content['judul'] : ''),
                                'content' => $bSlide
                            ];
                        }
                    }
                }
            } else {
                $slidesText = autoSplitText($content);
                foreach($slidesText as $st) {
                    $allSlides[] = ['type' => 'text', 'title' => str_replace(' (Opsional)', '', $item->title), 'content' => $st];
                }
            }
        }
        if(isset($customSlides[$item->id])) {
            foreach($customSlides[$item->id] as $cSlide) {
                $cSlidesText = autoSplitText($cSlide->content);
                foreach($cSlidesText as $ct) {
                    $allSlides[] = ['type' => 'custom', 'title' => $cSlide->title, 'content' => $ct];
                }
            }
        }
    }
    $allSlides[] = ['type' => 'closing', 'title' => 'PENUTUP', 'content' => "TUHAN YESUS\nMEMBERKATI"];
@endphp

<div class="container-fluid p-0">
    <div class="row g-0">
        
        <div class="col-md-3 sidebar">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary">
                <h6 class="fw-bold m-0 text-white text-uppercase" style="letter-spacing: 1px; font-size: 0.8rem;">Control Panel</h6>
                <a href="{{ route('liturgy.gallery') }}" class="btn btn-outline-secondary btn-sm py-0" style="font-size: 0.65rem;">Kembali</a>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success py-2 px-3 mb-3 text-center" style="font-size: 0.75rem;">
                    <strong>Berhasil Tersimpan!</strong>
                </div>
            @endif

            <form id="liveForm" action="{{ route('liturgy.update', $schedule->id) }}" method="POST">
                @csrf
                @method('PUT') 

                <div class="card-edit" style="border-left: 3px solid #3182ce;">
                    <label class="form-label-header text-info">Desain Latar & Font</label>
                    
                    <div class="mb-2">
                        <select id="bg_type" onchange="toggleBgSettings(); updateDesignLive()" class="form-select form-select-sm bg-dark text-white border-secondary fw-medium">
                            <option value="gradient">Gradien Statis (Klasik)</option>
                            <option value="anim-linear">Animasi Gradien Linear (Garis)</option>
                            <option value="anim-radial">Animasi Gradien Radial (Bulat)</option>
                            <option value="anim-sweep">Animasi Gradien Sapuan (Sweep)</option>
                            <option value="pattern-grid">Pola Grid (Statis)</option>
                            <option value="pattern-grid-anim">Animasi Pola Grid Berjalan</option>
                            <option value="pattern-dots">Pola Titik (Polka Dots)</option>
                            <option value="pattern-stripes">Pola Garis Miring (Statis)</option>
                            <option value="pattern-stripes-anim">Animasi Garis Miring Berjalan</option>
                            <option value="video">Video Interaktif (MP4 Offline)</option>
                        </select>
                    </div>

                    <div id="video_settings" class="mb-2" style="display: none; background: #1a1a1a; padding: 8px; border-radius: 4px; border: 1px dashed #444;">
                        <input type="text" id="bg_video_url" oninput="updateDesignLive()" class="form-control form-control-sm bg-dark text-white border-secondary mb-1" placeholder="URL Video LOKAL (Cth: /bg.mp4)">
                        <small class="text-muted d-block" style="font-size:0.6rem; line-height: 1.1;">Taruh file mp4 di dalam folder public/ lalu ketik namanya di atas. Pasti jalan walau offline.</small>
                    </div>

                    <div id="anim_speed_wrapper" class="mb-2 px-1" style="display: none;">
                        <div class="d-flex justify-content-between text-secondary" style="font-size:0.65rem;">
                            <span>Kecepatan Animasi:</span>
                            <span class="text-info fw-bold" id="speed_indicator">15s</span>
                        </div>
                        <input type="range" id="anim_speed" oninput="updateSpeedIndicator(); updateDesignLive()" class="form-range mt-1" min="3" max="40" step="1" value="15" dir="rtl" style="height: 0.5rem;"> 
                    </div>

                    <div class="row g-1 mb-2">
                        <div class="col-6">
                            <small class="text-secondary d-block" style="font-size:0.65rem;">Edge / Tepi</small>
                            <input type="color" id="bg_edge_color" onchange="updateDesignLive()" class="form-control form-control-color w-100 p-0" value="#050505" style="height: 24px; border:none;">
                        </div>
                        <div class="col-6">
                            <small class="text-secondary d-block" style="font-size:0.65rem;">Center / Utama</small>
                            <input type="color" name="theme_color" id="bg_center_color" onchange="updateDesignLive()" class="form-control form-control-color w-100 p-0" value="{{ $schedule->theme_color ?? '#1b2735' }}" style="height: 24px; border:none;">
                        </div>
                    </div>

                    <div id="anim_colors_wrapper" class="row g-1 mb-2" style="display: none;">
                        <div class="col-6">
                            <small class="text-secondary d-block" style="font-size:0.65rem;">Warna Animasi 1</small>
                            <input type="color" id="anim_color_1" onchange="updateDesignLive()" class="form-control form-control-color w-100 p-0" value="#2b6cb0" style="height: 24px; border:none;">
                        </div>
                        <div class="col-6">
                            <small class="text-secondary d-block" style="font-size:0.65rem;">Warna Animasi 2</small>
                            <input type="color" id="anim_color_2" onchange="updateDesignLive()" class="form-control form-control-color w-100 p-0" value="#2c5282" style="height: 24px; border:none;">
                        </div>
                    </div>

                    <div class="row g-1 mb-1">
                        <div class="col-12 mb-1">
                            <select id="font_family" onchange="updateDesignLive()" class="form-select form-select-sm bg-dark text-white border-secondary" style="font-size:0.75rem;">
                                <option value="'Inter', Tahoma, sans-serif">Inter (Modern Default)</option>
                                <option value="'Montserrat', sans-serif">Montserrat (Tegas)</option>
                                <option value="'Roboto', sans-serif">Roboto (Bersih)</option>
                                <option value="'Lato', sans-serif">Lato (Elegan)</option>
                                <option value="'Oswald', sans-serif">Oswald (Tinggi/Padat)</option>
                                <option value="'Playfair Display', serif">Playfair Display (Klasik)</option>
                                <option value="Arial, sans-serif">Arial</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <small class="text-secondary d-block" style="font-size:0.65rem;">Warna Teks</small>
                            <input type="color" id="text_color" onchange="updateDesignLive()" class="form-control form-control-color w-100 p-0" value="#ffffff" style="height: 24px; border:none;">
                        </div>
                        <div class="col-6">
                            <small class="text-secondary d-block" style="font-size:0.65rem;">Bayangan Teks</small>
                            <input type="color" id="shadow_color" onchange="updateDesignLive()" class="form-control form-control-color w-100 p-0" value="#000000" style="height: 24px; border:none;">
                        </div>
                    </div>
                </div>

                <div class="card-edit">
                    <label class="form-label-header">Info Ibadah</label>
                    <div class="mb-1"><input type="text" name="theme" class="form-control" value="{{ $schedule->theme }}" placeholder="Tema Ibadah"></div>
                    <div><input type="text" name="preacher_name" class="form-control" value="{{ $schedule->preacher_name }}" placeholder="Pelayan Firman"></div>
                </div>
                
                @foreach($schedule->liturgy->items as $item)
                    @php 
                        $detail = $schedule->details->where('liturgy_item_id', $item->id)->first();
                        $val = $detail ? $detail->dynamic_content : ($item->static_content ?? '');
                    @endphp
                    
                    <div class="card-edit">
                        <label class="form-label-header" style="color:#e2e8f0;">{{ $item->title }}</label>
                        
                        @if($item->is_dynamic)
                            @if(str_contains(strtolower($item->title), 'sikap') || str_contains(strtolower($item->title), 'aksi'))
                                <select name="dynamic_content[{{ $item->id }}]" class="form-select bg-dark text-white fw-medium">
                                    <option value="(Jemaat Berdiri)" {{ str_contains($val, 'Berdiri') ? 'selected' : '' }}>(Jemaat Berdiri)</option>
                                    <option value="(Jemaat Duduk)" {{ str_contains($val, 'Duduk') && !str_contains($val, 'Teduh') ? 'selected' : '' }}>(Jemaat Duduk)</option>
                                    <option value="(Saat Teduh)" {{ str_contains($val, 'Teduh') ? 'selected' : '' }}>(Saat Teduh / Lilin Dipadamkan)</option>
                                </select>
                            @elseif(str_contains(strtolower($item->title), 'pra-ibadah') || str_contains(strtolower($item->title), 'prosesi'))
                                <input type="text" name="dynamic_content[{{ $item->id }}][custom_title]" class="form-control mb-1 fw-medium text-info" value="{{ is_array($val) ? ($val['custom_title'] ?? '') : str_replace(' (Opsional)', '', $item->title) }}">
                                <textarea name="dynamic_content[{{ $item->id }}][content]" class="form-control" rows="2">{{ is_array($val) ? ($val['content'] ?? '') : (is_string($val) ? $val : '') }}</textarea>
                            @elseif(str_contains(strtolower($item->title), 'nyanyian') || str_contains(strtolower($item->title), 'pujian'))
                                <div class="input-group mb-1">
                                    <select id="buku-lagu-{{ $item->id }}" class="form-select bg-dark text-white" style="max-width: 80px; padding:2px;">
                                        <option value="KJ">KJ</option><option value="NKB">NKB</option><option value="PKJ">PKJ</option><option value="NR">NR</option><option value="BEBAS">Lainnya</option>
                                    </select>
                                    <input type="text" id="nomor-lagu-{{ $item->id }}" class="form-control" placeholder="No">
                                    <button type="button" class="btn btn-secondary btn-sm px-2" onclick="tarikLagu({{ $item->id }}, event)">Tarik</button>
                                </div>
                                <input type="text" name="dynamic_content[{{ $item->id }}][judul]" class="form-control mb-1 fw-medium" placeholder="Judul Lagu" value="{{ is_array($val) ? ($val['judul'] ?? '') : '' }}">
                                <div id="bait-container-{{ $item->id }}">
                                    @if(is_array($val) && isset($val['bait']) && is_array($val['bait']))
                                        @foreach($val['bait'] as $key => $baitText)
                                            <div class="input-group mb-1 position-relative">
                                                <span class="input-group-text">Bait {{ $key }}</span>
                                                <textarea name="dynamic_content[{{ $item->id }}][bait][{{ $key }}]" class="form-control" rows="2">{{ $baitText }}</textarea>
                                                <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3" onclick="this.parentElement.remove()" style="font-size: 14px;">&times;</button>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="input-group mb-1 position-relative">
                                            <span class="input-group-text">Bait 1</span>
                                            <textarea name="dynamic_content[{{ $item->id }}][bait][1]" class="form-control" rows="2"></textarea>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn-add-slide mt-1" onclick="tambahBait({{ $item->id }})">&plus; Lirik</button>
                            @else
                                @if(str_contains(strtolower($item->title), 'alkitab') || str_contains(strtolower($item->title), 'bacaan'))
                                    <div class="input-group mb-1"><input type="text" id="input-alkitab-{{ $item->id }}" class="form-control" placeholder="Cari Kitab"><button type="button" class="btn btn-secondary btn-sm px-2" onclick="tarikAlkitab({{ $item->id }}, event)">Tarik</button></div>
                                @endif
                                <textarea id="textarea-{{ $item->id }}" name="dynamic_content[{{ $item->id }}]" class="form-control" rows="2">{{ is_array($val) ? ($val['content'] ?? '') : $val }}</textarea>
                            @endif
                        @else
                            <textarea name="dynamic_content[{{ $item->id }}]" class="form-control text-secondary" style="background:#1a1a1a!important;" rows="2" readonly>{{ $item->static_content }}</textarea>
                        @endif

                        <div class="mt-2 pt-2 border-top" style="border-color: #333 !important;">
                            <div id="custom-slide-container-{{ $item->id }}">
                                @if(isset($schedule->customSlides) && $schedule->customSlides->where('liturgy_item_id', $item->id)->count() > 0)
                                    @foreach($schedule->customSlides->where('liturgy_item_id', $item->id) as $index => $cSlide)
                                        <div class="custom-slide-box">
                                            <input type="text" name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][title]" class="form-control form-control-sm mb-1 fw-medium text-info" value="{{ $cSlide->title }}">
                                            <textarea name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][content]" class="form-control form-control-sm" rows="1">{{ $cSlide->content }}</textarea>
                                            <button type="button" class="btn btn-sm text-danger mt-1 p-0" style="font-size:0.7rem;" onclick="this.parentElement.remove()">Hapus</button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn-add-slide mt-1" onclick="tambahSlideKhusus({{ $item->id }})">&plus; Slide Tambahan</button>
                        </div>
                    </div>
                @endforeach
                
                <button type="submit" onclick="triggerUpdateSync()" class="btn btn-primary-custom w-100 py-3 mt-3 shadow sticky-bottom" style="bottom: 15px;">SIMPAN PERUBAHAN</button>
            </form>
        </div>

        <div class="col-md-9 presenter-view">
            <div class="preview-header">
                <span class="fw-medium text-info" style="font-size: 0.85rem; letter-spacing: 0.5px;">LIVE STATUS: {{ strtoupper($schedule->liturgy->name ?? 'JADWAL IBADAH') }}</span>
                <button onclick="openProjector()" class="btn btn-primary-custom btn-sm px-4">BUKA PROYEKTOR</button>
            </div>

            <div class="slide-monitor">
                
                <div class="current-slide-box monitor-box" id="monitor-current">
                    <span class="label-badge">TAMPILAN SAAT INI</span>
                    <div id="virtual-render-current" style="width: 100%; height: 100%;"></div>
                    
                    <div class="per-slide-font-control">
                        <span class="font-control-label">Ukuran Teks:</span>
                        <button type="button" onclick="changeSlideFont(-0.5)" title="Perkecil Font">-</button>
                        <span id="slide-font-indicator">Auto</span>
                        <button type="button" onclick="changeSlideFont(0.5)" title="Perbesar Font">+</button>
                        <button type="button" onclick="resetSlideFont()" style="background:transparent; border-color:#666; font-size:0.7rem; font-weight:normal;">Reset</button>
                    </div>
                </div>

                <div class="next-slide-box monitor-box" id="monitor-next">
                    <span class="label-badge" style="background: #4a5568;">BERIKUTNYA</span>
                    <div id="virtual-render-next" style="width: 100%; height: 100%;"></div>
                </div>

            </div>

            <div class="slide-gallery-container" id="slideGallery"></div>

            <div class="nav-controls">
                <button class="nav-btn" onclick="controlProjector('prev')">SEBELUMNYA</button>
                <div class="text-center">
                    <div id="slide-num" class="fw-bold fs-5 text-white">0 / 0</div>
                    <div style="font-size: 0.65rem; color: #718096; margin-top: 2px;">Gunakan Panah Kiri/Kanan atau Atas/Bawah</div>
                </div>
                <button class="nav-btn" onclick="controlProjector('next')">SELANJUTNYA</button>
            </div>
        </div>
    </div>
</div>

<script>
    // PERINTAH REFRESH PROYEKTOR JIKA BERHASIL SAVE
    @if(session('success'))
        localStorage.setItem('liturgy_update', Date.now());
    @endif

    function triggerUpdateSync() {
        // Form akan tersubmit native, ini hanya untuk memastikan proyektor menangkap sinyal reload
        localStorage.setItem('liturgy_update', Date.now());
    }

    const scheduleId = {{ $schedule->id ?? 0 }};
    const allSlidesData = @json($allSlides);
    let currentSlide = parseInt(localStorage.getItem('last_slide_index')) || 0;
    let customFonts = JSON.parse(localStorage.getItem('custom_fonts_' + scheduleId)) || {};

    // 1. ENGINE VIRTUAL RENDER (HANYA UNTUK 2 MONITOR ATAS)
    function renderVirtualSlide(slide, index) {
        if(!slide) return '<div class="sp-container"><div class="vp-content">SELESAI</div></div>';

        let customSize = customFonts[index];
        let fontSizeStyle = customSize ? `font-size: ${customSize}cqw !important;` : '';
        let innerHTML = '';

        if(slide.type === 'cover') {
            innerHTML = `
                <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" class="welcome-img" style="height: 18cqh; margin-bottom: 3cqh; filter: drop-shadow(0px 4px 10px rgba(0,0,0,0.5));">
                <div class="welcome-title vp-cover-title" style="font-size: 6.5cqw; font-weight: 900; margin-bottom: 1.5cqh; text-transform: uppercase; letter-spacing: 0.3cqw; line-height: 1.1; text-shadow: 0px 4cqh 15cqw var(--shadow-color);">${slide.title}</div>
                <div class="welcome-sub vp-cover-date" style="font-size: 2.5cqw; color: #cbd5e0; font-weight: 400;">${slide.date}</div>
                ${slide.theme ? `<div class="welcome-sub vp-cover-theme" style="font-size: 2.5cqw; color: #cbd5e0; margin-top: 2cqh;">TEMA: ${slide.theme}</div>` : ''}
                ${slide.preacher ? `<div class="welcome-sub vp-cover-preacher" style="font-size: 2.5cqw; color: #cbd5e0; margin-top: 1cqh;">PELAYAN FIRMAN: ${slide.preacher}</div>` : ''}
            `;
        }
        else if (slide.type === 'instruksi') {
            if(!fontSizeStyle) fontSizeStyle = slide.content.length < 25 ? 'font-size: 7cqw;' : 'font-size: 5cqw;';
            innerHTML = `<div class="vp-instruksi sp-instruksi" style="${fontSizeStyle}">${slide.content}</div>`;
        }
        else if (slide.type === 'song_cover') {
            innerHTML = `
                <div style="margin: auto; text-align: center;">
                    <div class="welcome-sub vp-song-cover-title" style="font-size: 2.5cqw; margin-bottom: 1.5cqh; text-transform: uppercase; color: #cbd5e0; letter-spacing: 0.2cqw;">${slide.title}</div>
                    <div class="welcome-title vp-song-cover-content" style="font-size: 5.5cqw; font-weight: 900; text-shadow: 0px 4cqh 15cqw var(--shadow-color);">${slide.content}</div>
                </div>
            `;
        }
        else if (slide.type === 'song_lyric') {
            innerHTML = `
                <div class="vp-watermark sp-watermark">${slide.watermark}</div>
                <div class="vp-title-header sp-title-header">${slide.title}</div>
                <div class="vp-content sp-content" style="${fontSizeStyle}">${slide.content.replace(/\n/g, '<br>')}</div>
            `;
        }
        else if (slide.type === 'closing') {
            innerHTML = `<div class="vp-closing welcome-title" style="font-size: 6.5cqw; font-weight: 900; text-transform: uppercase; margin: auto; text-shadow: 0px 4cqh 15cqw var(--shadow-color);">TUHAN YESUS<br><span class="sp-text-kuning">MEMBERKATI</span></div>`;
        }
        else { 
            innerHTML = `
                <div class="vp-title-header sp-title-header ${slide.type === 'custom' ? 'text-info' : ''}">${slide.title}</div>
                <div class="vp-content sp-content" style="${fontSizeStyle}">${slide.content.replace(/\n/g, '<br>')}</div>
            `;
        }

        return `<div class="sp-container">${innerHTML}</div>`;
    }

    // 2. LOGIKA BACKGROUND DINAMIS (FULL COLOR & PATTERNS)
    function toggleBgSettings() {
        const type = document.getElementById('bg_type').value;
        document.getElementById('video_settings').style.display = (type === 'video') ? 'block' : 'none';
        
        const isAnim = type.includes('anim-');
        document.getElementById('anim_colors_wrapper').style.display = isAnim ? 'flex' : 'none';
        
        if(type.includes('anim') || type === 'pattern-grid-anim' || type === 'pattern-stripes-anim') {
            document.getElementById('anim_speed_wrapper').style.display = 'block';
        } else {
            document.getElementById('anim_speed_wrapper').style.display = 'none';
        }
    }

    function updateSpeedIndicator() {
        document.getElementById('speed_indicator').innerText = document.getElementById('anim_speed').value + 's';
    }

    function hexToRgb(hex) {
        let r = parseInt(hex.slice(1, 3), 16), g = parseInt(hex.slice(3, 5), 16), b = parseInt(hex.slice(5, 7), 16);
        return `${r}, ${g}, ${b}`;
    }

    function applyVirtualDesign(settings) {
        document.documentElement.style.setProperty('--bg-center', settings.bgCenterColor);
        document.documentElement.style.setProperty('--bg-edge', settings.bgEdgeColor);
        document.documentElement.style.setProperty('--text-color', settings.textColor);
        document.documentElement.style.setProperty('--font-family', settings.fontFamily);
        document.documentElement.style.setProperty('--shadow-color', `rgba(${hexToRgb(settings.shadowColor)}, 0.9)`);

        const speed = settings.animSpeed || 15;
        const ac1 = settings.animColor1 || '#2b6cb0';
        const ac2 = settings.animColor2 || '#2c5282';

        document.querySelectorAll('.sp-container').forEach(el => {
            el.style.background = ''; el.style.backgroundColor = ''; el.style.backgroundImage = ''; el.style.backgroundSize = ''; el.style.animation = '';
            
            let vid = el.querySelector('.virtual-bg-video');
            if(vid) vid.remove();

            if (settings.bgType === 'gradient' || !settings.bgType) {
                el.style.background = `radial-gradient(circle at center, ${settings.bgCenterColor} 0%, ${settings.bgEdgeColor} 100%)`;
            } 
            else if (settings.bgType === 'anim-linear') {
                el.style.background = `linear-gradient(-45deg, ${settings.bgCenterColor}, ${settings.bgEdgeColor}, ${ac1}, ${ac2})`;
                el.style.backgroundSize = '400% 400%'; el.style.animation = `gradientBG ${speed}s ease infinite`;
            } 
            else if (settings.bgType === 'anim-radial') {
                el.style.background = `radial-gradient(circle, ${settings.bgCenterColor}, ${ac1}, ${settings.bgEdgeColor}, ${ac2})`;
                el.style.backgroundSize = '400% 400%'; el.style.animation = `gradientBG ${speed}s ease infinite`;
            } 
            else if (settings.bgType === 'anim-sweep') {
                el.style.background = `linear-gradient(90deg, ${settings.bgCenterColor}, ${ac1}, ${settings.bgEdgeColor}, ${ac2}, ${settings.bgCenterColor})`;
                el.style.backgroundSize = '400% 100%'; el.style.animation = `gradientBG ${speed}s linear infinite`;
            }
            else if (settings.bgType === 'pattern-grid') {
                el.style.backgroundColor = settings.bgEdgeColor;
                el.style.backgroundImage = `linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px)`;
                el.style.backgroundSize = '4cqw 4cqw';
            }
            else if (settings.bgType === 'pattern-grid-anim') {
                el.style.backgroundColor = settings.bgEdgeColor;
                el.style.backgroundImage = `linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px)`;
                el.style.backgroundSize = '4cqw 4cqw'; el.style.animation = `moveGridCP ${speed}s linear infinite`;
            } 
            else if (settings.bgType === 'pattern-dots') {
                el.style.backgroundColor = settings.bgEdgeColor;
                el.style.backgroundImage = `radial-gradient(${settings.bgCenterColor} 2px, transparent 2px)`;
                el.style.backgroundSize = '4cqw 4cqw';
            } 
            else if (settings.bgType === 'pattern-stripes') {
                el.style.backgroundColor = settings.bgEdgeColor;
                el.style.backgroundImage = `repeating-linear-gradient(45deg, ${settings.bgCenterColor} 0, ${settings.bgCenterColor} 2px, transparent 2px, transparent 50%)`;
                el.style.backgroundSize = '4cqw 4cqw';
            }
            else if (settings.bgType === 'pattern-stripes-anim') {
                el.style.backgroundColor = settings.bgEdgeColor;
                el.style.backgroundImage = `repeating-linear-gradient(45deg, ${settings.bgCenterColor} 0, ${settings.bgCenterColor} 2px, transparent 2px, transparent 50%)`;
                el.style.backgroundSize = '4cqw 4cqw'; el.style.animation = `moveStripesCP ${speed}s linear infinite`;
            }
            else if (settings.bgType === 'video' && settings.bgVideoUrl) {
                el.style.backgroundColor = '#000';
                const v = document.createElement('video');
                v.className = 'virtual-bg-video'; v.src = settings.bgVideoUrl; v.autoplay = true; v.loop = true; v.muted = true;
                v.style.position = 'absolute'; v.style.top = '0'; v.style.left = '0'; v.style.width = '100%'; v.style.height = '100%'; v.style.objectFit = 'cover'; v.style.zIndex = '0'; v.style.opacity = '0.6';
                el.insertBefore(v, el.firstChild);
            }
        });
    }

    function updateDesignLive() {
        const bgType = document.getElementById('bg_type').value;
        const bgVideoUrl = document.getElementById('bg_video_url').value;
        const animSpeed = document.getElementById('anim_speed').value;
        const bgCenterColor = document.getElementById('bg_center_color').value;
        const bgEdgeColor = document.getElementById('bg_edge_color').value;
        const animColor1 = document.getElementById('anim_color_1').value;
        const animColor2 = document.getElementById('anim_color_2').value;
        const textColor = document.getElementById('text_color').value;
        const fontFamily = document.getElementById('font_family').value;
        const shadowColor = document.getElementById('shadow_color').value;

        const designSettings = { 
            bgType, bgVideoUrl, animSpeed, fontFamily, bgCenterColor, bgEdgeColor, animColor1, animColor2,
            textColor, shadowColor, textShadow: '0.9'
        };
        localStorage.setItem('live_design_settings', JSON.stringify(designSettings));
        
        applyVirtualDesign(designSettings);
    }

    function loadSavedDesign() {
        const saved = JSON.parse(localStorage.getItem('live_design_settings'));
        if(saved) {
            if(saved.fontFamily) document.getElementById('font_family').value = saved.fontFamily;
            if(saved.bgCenterColor) document.getElementById('bg_center_color').value = saved.bgCenterColor;
            if(saved.bgEdgeColor) document.getElementById('bg_edge_color').value = saved.bgEdgeColor;
            if(saved.animColor1) document.getElementById('anim_color_1').value = saved.animColor1;
            if(saved.animColor2) document.getElementById('anim_color_2').value = saved.animColor2;
            if(saved.textColor) document.getElementById('text_color').value = saved.textColor;
            if(saved.shadowColor) document.getElementById('shadow_color').value = saved.shadowColor;
            if(saved.animSpeed) { document.getElementById('anim_speed').value = saved.animSpeed; updateSpeedIndicator(); }
            if(saved.bgType) { document.getElementById('bg_type').value = saved.bgType; toggleBgSettings(); }
            if(saved.bgVideoUrl) document.getElementById('bg_video_url').value = saved.bgVideoUrl;
        }
    }

    // 3. LOGIKA FONT KHUSUS PER SLIDE
    function changeSlideFont(step) {
        let currentSize = customFonts[currentSlide] ? parseFloat(customFonts[currentSlide]) : 5.0; 
        let newSize = (currentSize + step).toFixed(1);
        if(newSize < 2.0) newSize = 2.0; if(newSize > 15.0) newSize = 15.0;
        customFonts[currentSlide] = newSize;
        saveAndSyncFont();
    }

    function resetSlideFont() {
        delete customFonts[currentSlide];
        saveAndSyncFont();
    }

    function saveAndSyncFont() {
        localStorage.setItem('custom_fonts_' + scheduleId, JSON.stringify(customFonts));
        localStorage.setItem('font_sync_trigger', Date.now()); 
        updateConsoleView(); 
    }

    // 4. NAVIGASI PROYEKTOR
    function controlProjector(action, specificIndex = null) {
        if(action === 'next') currentSlide++;
        else if(action === 'prev') currentSlide--;
        else if(action === 'jump') currentSlide = specificIndex;

        if (currentSlide < 0) currentSlide = 0;
        if (currentSlide >= allSlidesData.length) currentSlide = allSlidesData.length - 1;

        localStorage.setItem('projector_command', JSON.stringify({ action: 'jump', index: currentSlide, time: Date.now() }));
        localStorage.setItem('last_slide_index', currentSlide);
        updateConsoleView();
    }

    function openProjector() {
        window.open("{{ route('liturgy.presentation', $schedule->id) }}", "ProjectorWindow", `width=${window.screen.width},height=${window.screen.height},left=${window.screen.width},top=0,menubar=no,toolbar=no,location=no,status=no`);
    }

    function handleKeyboard(e) {
        if (['TEXTAREA', 'INPUT', 'SELECT'].includes(document.activeElement.tagName)) return;
        if (['ArrowRight', 'ArrowUp', ' '].includes(e.key)) { e.preventDefault(); controlProjector('next'); }
        if (['ArrowLeft', 'ArrowDown'].includes(e.key)) { e.preventDefault(); controlProjector('prev'); }
    }

    // 5. UPDATE TAMPILAN MONITOR CONTROL PANEL
    function updateConsoleView() {
        document.getElementById('virtual-render-current').innerHTML = renderVirtualSlide(allSlidesData[currentSlide], currentSlide);
        document.getElementById('virtual-render-next').innerHTML = renderVirtualSlide(allSlidesData[currentSlide + 1], currentSlide + 1);
        document.getElementById('slide-num').innerText = `${currentSlide + 1} / ${allSlidesData.length}`;
        
        // Update Thumbnails Simple Bawah
        document.querySelectorAll('.slide-thumb-simple').forEach((thumb, i) => { 
            thumb.classList.toggle('active', i === currentSlide); 
            if(i === currentSlide) thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' }); 
        });

        const fontIndicator = document.getElementById('slide-font-indicator');
        if (customFonts[currentSlide]) {
            fontIndicator.innerText = customFonts[currentSlide] + 'vw';
            fontIndicator.style.color = '#fcd34d'; 
        } else {
            fontIndicator.innerText = 'Auto';
            fontIndicator.style.color = '#cbd5e0'; 
        }

        applyVirtualDesign(JSON.parse(localStorage.getItem('live_design_settings')));
    }

    // 6. BUILD GALLERY (MODE TEKS SEDERHANA, LEBIH RINGAN DAN TERBACA JELAS)
    function buildGallery() {
        const gallery = document.getElementById('slideGallery'); gallery.innerHTML = '';
        allSlidesData.forEach((slide, i) => {
            const thumb = document.createElement('div'); 
            thumb.className = 'slide-thumb-simple';
            
            let displayTitle = slide.title || '';
            let displayContent = slide.content || '';
            
            if(slide.type === 'cover') { displayTitle = 'SAMPUL DEPAN'; displayContent = slide.title + ' - ' + slide.date; }
            if(slide.type === 'song_lyric') { displayTitle = slide.title + ' (Bait ' + slide.watermark + ')'; }

            // Potong teks agar tidak kepanjangan di thumbnail
            let shortContent = displayContent.replace(/<[^>]*>?/gm, '').substring(0, 90);
            if(displayContent.length > 90) shortContent += '...';

            thumb.innerHTML = `
                <div class="thumb-num-badge">${i+1}</div>
                <div class="thumb-simple-title">${displayTitle}</div>
                <div class="thumb-simple-content">${shortContent}</div>
            `;
            thumb.ondblclick = () => controlProjector('jump', i); 
            gallery.appendChild(thumb);
        });
    }

    // 7. FUNGSI FORM LOGIC
    function getNextVerseNumber(containerId) {
        const container = document.getElementById(containerId); let maxNum = 0;
        container.querySelectorAll('textarea').forEach(ta => {
            if(ta.name.includes('[bait]')) { const match = ta.name.match(/\[bait\]\[(\d+)\]/); if(match && parseInt(match[1]) > maxNum) maxNum = parseInt(match[1]); }
        });
        return maxNum === 0 ? 1 : maxNum + 1;
    }
    function tambahBait(itemId) {
        const containerId = 'bait-container-' + itemId; const nextNum = getNextVerseNumber(containerId);
        const html = `<div class="input-group mb-1 position-relative"><span class="input-group-text">Bait ${nextNum}</span><textarea name="dynamic_content[${itemId}][bait][${nextNum}]" class="form-control" rows="2"></textarea><button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3" onclick="this.parentElement.remove()" style="font-size: 14px;">&times;</button></div>`;
        document.getElementById(containerId).insertAdjacentHTML('beforeend', html);
    }
    function tambahSlideKhusus(itemId) {
        const container = document.getElementById('custom-slide-container-' + itemId); const slideId = Math.random().toString(36).substr(2, 9);
        const html = `<div class="custom-slide-box"><input type="text" name="custom_slides[${itemId}][${slideId}][title]" class="form-control form-control-sm mb-1 fw-medium text-info" placeholder="Judul Sisipan"><textarea name="custom_slides[${itemId}][${slideId}][content]" class="form-control form-control-sm" rows="1" placeholder="Isi teks..."></textarea><button type="button" class="btn btn-sm text-danger mt-1 p-0" style="font-size:0.7rem;" onclick="this.parentElement.remove()">Hapus</button></div>`;
        container.insertAdjacentHTML('beforeend', html); container.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function tarikLagu(itemId, event) {
        const buku = document.getElementById(`buku-lagu-${itemId}`).value; const nomor = document.getElementById(`nomor-lagu-${itemId}`).value.trim();
        const judulInput = document.querySelector(`input[name="dynamic_content[${itemId}][judul]"]`); const container = document.getElementById(`bait-container-${itemId}`);
        const btn = event.currentTarget;
        if(!nomor) return alert('Masukkan nomor lagu!');
        const originalText = btn.innerHTML; btn.innerHTML = '...'; btn.disabled = true;
        fetch(`/api/fetch-lagu?buku=${buku}&nomor=${nomor}`).then(res => res.json()).then(data => {
            if(data.success) {
                judulInput.value = data.judul; container.innerHTML = ''; 
                const baits = data.text.split('===SLIDE_BREAK==='); let verseNum = 1;
                baits.forEach(bait => {
                    if(bait.trim() !== '') {
                        const html = `<div class="input-group mb-1 position-relative"><span class="input-group-text">Bait ${verseNum}</span><textarea name="dynamic_content[${itemId}][bait][${verseNum}]" class="form-control" rows="2">${bait.trim()}</textarea><button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3" onclick="this.parentElement.remove()" style="font-size: 14px;">&times;</button></div>`;
                        container.insertAdjacentHTML('beforeend', html); verseNum++;
                    }
                });
            } else { alert(data.message); }
        }).catch(err => alert('Gagal menarik data lagu.')).finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
    }
    function tarikAlkitab(itemId, event) {
        const inputField = document.getElementById('input-alkitab-' + itemId); const textarea = document.getElementById('textarea-' + itemId);
        const btn = event.currentTarget; const query = inputField.value.trim();
        if(!query) return alert('Masukkan kitab dan pasal.'); 
        const originalText = btn.innerHTML; btn.innerHTML = '...'; btn.disabled = true;
        fetch(`/api/fetch-alkitab?q=${encodeURIComponent(query)}`).then(res => res.json()).then(data => {
            if(data.success) { textarea.value = query.toUpperCase() + "\n===SLIDE_BREAK===\n" + data.text; } else alert(data.message);
        }).catch(() => alert('Terjadi kesalahan koneksi.')).finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
    }

    window.addEventListener('storage', (e) => { 
        if (e.key === 'last_slide_index') { currentSlide = parseInt(e.newValue); updateConsoleView(); }
    });
    
    window.onload = () => { loadSavedDesign(); buildGallery(); updateConsoleView(); };
</script>
</body>
</html>