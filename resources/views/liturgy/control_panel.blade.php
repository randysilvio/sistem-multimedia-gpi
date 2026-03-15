<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Kontrol Ibadah - GPI Papua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&family=Montserrat:wght@600;800&family=Playfair+Display:wght@600;800&family=Roboto:wght@500;700&family=Oswald:wght@500;700&family=Lato:wght@700;900&display=swap');
        
        body { background: #121212; color: #e0e0e0; height: 100vh; overflow: hidden; font-family: 'Inter', sans-serif; margin: 0; padding: 0;}
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: #444; border-radius: 3px; }
        
        /* TRANSISI LAYOUT */
        .layout-wrapper { display: flex; width: 100vw; height: 100vh; overflow: hidden; transition: 0.3s ease; }
        
        /* SIDEBAR EDITOR (KIRI) */
        .sidebar { width: 28%; height: 100vh; overflow-y: auto; background: #1a1a1a; border-right: 1px solid #2d2d2d; padding: 15px; position: relative; transition: 0.3s ease; flex-shrink: 0; display: flex; flex-direction: column;}
        .sidebar.collapsed { width: 0; padding: 0; border: none; overflow: hidden; opacity: 0; }
        .sidebar-content { flex-grow: 1; overflow-y: auto; padding-bottom: 20px; }
        
        .card-edit { background: #242424; border: 1px solid #333; border-radius: 6px; padding: 12px; margin-bottom: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);}
        .form-label-header { font-weight: 600; color: #a0aec0; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px; margin-bottom: 8px; display: block; border-bottom: 1px solid #333; padding-bottom: 4px; }
        textarea.form-control, input.form-control, select.form-control { background: #181818 !important; color: #cbd5e0 !important; border: 1px solid #333 !important; font-size: 0.8rem; border-radius: 4px; padding: 6px 10px;}
        textarea.form-control:focus, input.form-control:focus, select.form-control:focus { border-color: #3182ce !important; box-shadow: none; color: #fff !important; }
        .input-group-text { background: #2d2d2d !important; color: #a0aec0 !important; border-color: #333 !important; font-size: 0.75rem; border-radius: 4px 0 0 4px; padding: 4px 8px; min-width: 65px; justify-content: center; font-weight: bold;}
        .custom-slide-box { background: #1e1e1e; border: 1px solid #4a5568; border-radius: 4px; padding: 8px; margin-top: 8px; border-left: 3px solid #4299e1; }
        .btn-add-slide { font-size: 0.7rem; font-weight: 500; border: 1px dashed #4a5568; color: #a0aec0; background: transparent; padding: 5px; width: 100%; border-radius: 4px; transition: 0.2s;}
        .btn-add-slide:hover { border-color: #cbd5e0; color: #fff; background: #2d2d2d; }
        .btn-primary-custom { background-color: #2b6cb0; color: white; border: none; font-weight: 600; font-size: 0.85rem; letter-spacing: 0.5px; transition: 0.2s;}
        .btn-primary-custom:hover { background-color: #2c5282; color: white; }
        .floating-save { position: sticky; bottom: -15px; margin-left: -15px; margin-right: -15px; padding: 15px; background: rgba(26,26,26,0.95); border-top: 1px solid #333; z-index: 100; backdrop-filter: blur(5px); }

        /* AREA PRESENTER */
        .presenter-view { flex-grow: 1; height: 100vh; background: #0f0f0f; display: flex; flex-direction: column; transition: 0.3s ease; min-width: 0; position: relative;}
        
        .ppt-topbar { padding: 10px 24px; background: #1a1a1a; border-bottom: 1px solid #2d2d2d; display: flex; justify-content: space-between; align-items: center; z-index: 1050; position: relative;}
        .btn-ui-toggle { background: transparent; border: 1px solid #4a5568; color: #a0aec0; padding: 4px 12px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; transition: 0.2s; }
        .btn-ui-toggle:hover, .btn-ui-toggle.active { background: #4a5568; color: #fff; }
        .timer-display { font-family: 'Inter', monospace; font-size: 0.9rem; color: #a0aec0; font-weight: 600; display: flex; gap: 15px; align-items: center;}

        /* GRID GAYA POWERPOINT */
        .ppt-layout {
            display: flex;
            gap: 30px;
            padding: 30px;
            flex-grow: 1;
            min-height: 0;
            position: relative;
        }
        .ppt-main-col { flex: 2.5; display: flex; flex-direction: column; justify-content: center; align-items: center; }
        .ppt-side-col { flex: 1; display: flex; flex-direction: column; gap: 20px; }

        .monitor-box { background: #000; position: relative; border-radius: 6px; box-shadow: 0 0 20px rgba(0,0,0,0.5); overflow: hidden; container-type: size; }
        .current-slide-box { width: 100%; aspect-ratio: 16/9; border: 1px solid #444; box-shadow: 0 10px 30px rgba(0,0,0,0.8);}
        .next-slide-box { width: 100%; aspect-ratio: 16/9; border: 1px solid #333; opacity: 0.85; }
        
        /* PRATINJAU KAMERA LOKAL */
        .preview-cam { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1; display: none; }

        /* KONTROL NAVIGASI BAWAH LAYAR UTAMA */
        .ppt-controls { display: flex; justify-content: center; align-items: center; gap: 25px; margin-top: 25px; width: 100%; }
        .ppt-btn-nav { width: 45px; height: 45px; border-radius: 50%; border: 2px solid #a0aec0; background: transparent; color: #a0aec0; font-size: 1.4rem; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; font-weight: bold;}
        .ppt-btn-nav:hover { border-color: #fff; color: #fff; background: rgba(255,255,255,0.1); transform: scale(1.05);}
        .ppt-slide-counter { color: #e2e8f0; font-size: 1rem; font-weight: 600; min-width: 120px; text-align: center; }

        /* OVERLAY GALERI LAYAR PENUH */
        .fullscreen-gallery-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 15, 15, 0.98); z-index: 1000;
            display: none; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px; padding: 30px; align-content: flex-start; overflow-y: auto;
            backdrop-filter: blur(10px);
        }
        .fullscreen-gallery-overlay.expanded { display: grid; }

        /* THUMBNAIL GALERI VISUAL ASLI */
        .mini-slide-thumb {
            width: 100%; aspect-ratio: 16/9; background: #000;
            position: relative; border-radius: 6px; overflow: hidden;
            container-type: size; cursor: pointer; border: 2px solid #333; transition: 0.2s;
        }
        .mini-slide-thumb:hover { border-color: #63b3ed; transform: scale(1.03); z-index: 10; box-shadow: 0 5px 15px rgba(0,0,0,0.5);}
        .mini-slide-thumb.active { border-color: #4299e1; border-width: 3px; box-shadow: 0 0 20px rgba(66,153,225,0.6); }
        .mini-thumb-badge {
            position: absolute; top: 6px; left: 6px; background: rgba(0,0,0,0.85); color: #fff;
            font-size: 0.65rem; padding: 2px 6px; border-radius: 4px; z-index: 50; border: 1px solid #444; font-weight: bold;
        }

        /* VIRTUAL RENDERER CSS */
        :root { --bg-center: #1b2735; --bg-edge: #050505; --text-color: #ffffff; --shadow-color: rgba(0,0,0,0.9); --font-family: 'Inter', sans-serif; }
        .sp-container { width: 100%; height: 100%; background: radial-gradient(circle at center, var(--bg-center) 0%, var(--bg-edge) 100%); color: var(--text-color); font-family: var(--font-family); display: flex; flex-direction: column; justify-content: flex-start; align-items: center; text-align: center; padding: 6cqh 4cqw; box-sizing: border-box; position: absolute; top:0; left:0; border-radius: inherit; z-index: 5;}
        .sp-container.mode-kamera { background: transparent; justify-content: flex-end; padding: 0; }
        .sp-container.mode-kamera .vp-inner-wrapper { width: 100%; background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.6) 65%, transparent 100%); padding: 5cqh 4cqw 6cqh 4cqw; border-top: none; box-sizing: border-box; display: flex; flex-direction: column; align-items: center;}
        .vp-watermark { position: absolute; top: -3cqh; left: 3cqw; font-size: 55cqh; font-weight: 900; line-height: 1; z-index: 1; background: linear-gradient(180deg, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0.02) 80%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .vp-title-header { position: relative; z-index: 2; font-size: 2.2cqw; color: rgba(255, 255, 255, 0.7); text-transform: uppercase; letter-spacing: 0.4cqw; font-weight: 600; margin-top: 2cqh; border-bottom: 2px solid rgba(255, 255, 255, 0.15); padding-bottom: 1.5cqh; width: 85%; flex-shrink:0;}
        .vp-title-header.text-info { color: #63b3ed !important; border-bottom-color: rgba(99, 179, 237, 0.3) !important; }
        .vp-content { position: relative; z-index: 2; margin-top: auto; margin-bottom: auto; font-size: 5cqw; font-weight: 700; line-height: 1.45; text-shadow: 0px 4cqh 15cqw var(--shadow-color); max-width: 95%; }
        .sp-container.mode-kamera .vp-content { margin: 0; font-size: 4cqw; text-shadow: 2px 2px 8px rgba(0,0,0,0.9); }
        .vp-instruksi { position: relative; z-index: 2; margin: auto; font-size: 5.5cqw; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4cqw; text-shadow: 0px 4cqh 20cqw var(--shadow-color); }
        
        .kamera-logo-preview { position: absolute; top: 5cqh; right: 4cqw; height: 9cqh; z-index: 10; opacity: 0.85; filter: drop-shadow(0px 2px 6px rgba(0,0,0,0.7)); }
        .kamera-watermark-title-preview { position: absolute; top: 5cqh; left: 4cqw; font-size: 2cqw; font-weight: 700; color: rgba(255, 255, 255, 0.6); text-transform: uppercase; letter-spacing: 0.3cqw; z-index: 10; text-shadow: 1px 1px 5px rgba(0,0,0,0.9); border-left: 4px solid rgba(99, 179, 237, 0.6); padding-left: 1cqw; text-align: left; line-height: 1.2; }

        .per-slide-font-control { position: absolute; bottom: 10px; right: 10px; background: rgba(20,20,20,0.85); border: 1px solid #4299e1; border-radius: 6px; padding: 4px 10px; display: flex; gap: 8px; align-items: center; z-index: 50; backdrop-filter: blur(5px); }
        .per-slide-font-control button { background: #2d2d2d; color: white; border: 1px solid #444; padding: 2px 10px; border-radius: 4px; font-weight: bold; cursor: pointer; transition: 0.2s;}
        .per-slide-font-control button:hover { background: #4299e1; border-color: #4299e1; }
        #slide-font-indicator { color: #fcd34d; font-size: 0.75rem; font-weight: bold; min-width: 45px; text-align: center; }

        /* Toast Notifikasi AJAX */
        #toastAjax { position: fixed; bottom: 20px; left: 20px; background: #2b6cb0; color: white; padding: 10px 20px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; box-shadow: 0 4px 15px rgba(0,0,0,0.5); opacity: 0; transition: 0.3s; z-index: 9999; pointer-events: none;}
    </style>
</head>
<body onkeydown="handleKeyboard(event)">

@php
    $liturgyItems = $schedule->liturgy ? $schedule->liturgy->items : collect();
    $scheduleDetails = $schedule->details ? $schedule->details->keyBy('liturgy_item_id') : collect();
    $customSlides = $schedule->customSlides ? $schedule->customSlides->groupBy('liturgy_item_id') : collect();

    if (!function_exists('cleanSlideTitle')) {
        function cleanSlideTitle($title) {
            if (!is_string($title)) return '';
            $title = str_ireplace('(opsional)', '', $title);
            $title = preg_replace('/slide bebas\s*[:\-]?\s*/i', '', $title);
            $title = preg_replace('/^nyanyian\s*[:\-]\s*/i', '', $title);
            $title = preg_replace('/^nyanyian\s*$/i', '', trim($title));
            return trim($title, " -:");
        }
    }

    if (!function_exists('smartSplitText')) {
        function smartSplitText($text) {
            if (is_array($text)) { $text = $text['content'] ?? ''; }
            if (!is_string($text)) $text = '';
            
            if (str_contains($text, '===SLIDE_BREAK===')) {
                return array_filter(array_map('trim', explode('===SLIDE_BREAK===', $text)));
            }
            
            $text = preg_replace("/[\r\n]+/", " \n ", trim($text));
            $words = explode(' ', $text);
            
            $slides = [];
            $currentSlideLines = [];
            $currentLine = '';
            $maxCharsPerLine = 24; 
            $tolerance = 8; 

            $totalWords = count($words);
            for ($i = 0; $i < $totalWords; $i++) {
                $word = trim($words[$i]);
                if ($word === '') continue;

                $remainingText = trim(implode(' ', array_slice($words, $i)));
                $remainingLength = strlen($remainingText);

                if ($word === "\n") {
                    if (!empty($currentLine)) {
                        $currentSlideLines[] = trim($currentLine);
                        $currentLine = '';
                    }
                    continue;
                }

                if (strlen($currentLine) + strlen($word) + 1 > $maxCharsPerLine && !empty($currentLine)) {
                    if ($remainingLength <= $tolerance) {
                        $currentLine .= ' ' . $word;
                        continue; 
                    }

                    $currentSlideLines[] = trim($currentLine);
                    $currentLine = $word;
                    
                    $lastWordPushed = $currentSlideLines[count($currentSlideLines)-1];
                    $endsInPunctuation = preg_match('/[.?!,;:]$/', $lastWordPushed);

                    if (count($currentSlideLines) >= 3 || (count($currentSlideLines) >= 2 && $endsInPunctuation)) {
                        $slides[] = implode("\n", $currentSlideLines);
                        $currentSlideLines = [];
                    }
                } else {
                    $currentLine = empty($currentLine) ? $word : $currentLine . ' ' . $word;
                }
            }

            if (!empty($currentLine)) { $currentSlideLines[] = trim($currentLine); }
            if (!empty($currentSlideLines)) { $slides[] = implode("\n", $currentSlideLines); }
            
            return empty($slides) ? [$text] : $slides;
        }
    }

    $allSlides = [];
    
    if(isset($announcements) && $announcements->count() > 0) {
        $slideImages = [];
        foreach($announcements as $ann) {
            $slideImages[] = [
                'image_url' => asset('storage/' . $ann->image_path),
                'caption' => strtoupper($ann->title ?? ''),
                'duration' => $ann->duration ?? 5 
            ];
        }
        $allSlides[] = [
            'type' => 'announcements_slideshow',
            'title' => 'WARTA SINODE',
            'images' => $slideImages,
            'use_camera' => false
        ];
    }
    
    $allSlides[] = [
        'type' => 'cover',
        'title' => str_replace(' (CUSTOM)', '', strtoupper($schedule->liturgy->name ?? 'IBADAH')),
        'date' => \Carbon\Carbon::parse($schedule->worship_date)->translatedFormat('l, d F Y'),
        'theme' => strtoupper($schedule->theme ?? ''),
        'preacher' => strtoupper($schedule->preacher_name ?? ''),
        'use_camera' => false
    ];

    foreach($liturgyItems as $item) {
        $detail = $scheduleDetails->get($item->id);
        $content = $detail ? $detail->dynamic_content : $item->static_content;
        $isEmptyArray = is_array($content) && empty($content['judul']) && empty($content['bait']) && empty($content['content']);
        $isEmptyString = !is_array($content) && trim($content) === '';
        
        $cleanBaseTitle = cleanSlideTitle($item->title);
        $useCamera = is_array($content) && isset($content['use_camera']) && filter_var($content['use_camera'], FILTER_VALIDATE_BOOLEAN) == true;

        if($content && !$isEmptyArray && !$isEmptyString) {
            
            if(is_array($content) && array_key_exists('custom_title', $content) && empty(trim($content['custom_title']))) {
                $textInstruksi = $content['content'] ?? '';
                $allSlides[] = ['type' => 'instruksi', 'title' => '', 'content' => $textInstruksi, 'use_camera' => $useCamera];
            } 
            elseif(is_array($content)) {
                if(isset($content['custom_title']) && !empty(trim($content['custom_title']))) {
                    $cTitle = cleanSlideTitle($content['custom_title']);
                    $slidesText = smartSplitText($content['content'] ?? '');
                    foreach($slidesText as $st) {
                        $allSlides[] = ['type' => 'text', 'title' => $cTitle, 'content' => $st, 'use_camera' => $useCamera];
                    }
                } 
                elseif(!empty($content['bait'])) {
                    $songCoverTitle = $cleanBaseTitle;
                    $songCoverContent = $content['judul'] ?? '';
                    if (!empty($songCoverContent) && stripos($songCoverContent, $songCoverTitle) !== false) {
                        $songCoverTitle = ''; 
                    }

                    $allSlides[] = [
                        'type' => 'song_cover', 
                        'title' => $songCoverTitle,
                        'content' => $songCoverContent,
                        'use_camera' => $useCamera
                    ];
                    
                    foreach($content['bait'] as $key => $baitText) {
                        $baitTextRaw = trim($baitText);
                        if(empty($baitTextRaw)) continue;

                        $isReff = false;
                        if ((is_string($key) && stripos($key, 'ref') !== false) || preg_match('/^\[?reff?\]?[\s\:\.\-]?/i', $baitTextRaw)) {
                            $isReff = true;
                        }

                        $cleanBaitText = preg_replace('/^\[?REFF\]?\s*/i', '', $baitTextRaw);
                        
                        $displayIndex = '';
                        if (!$isReff) {
                            if (preg_match('/\d+/', $key, $matches)) { $displayIndex = $matches[0]; } 
                            else { $displayIndex = $key; }
                        }
                        
                        $laguTitle = $content['judul'] ?? '';
                        if (!empty($laguTitle)) {
                            if ($cleanBaseTitle !== '' && stripos($laguTitle, $cleanBaseTitle) !== false) { $slideTitle = $laguTitle; } 
                            else { $slideTitle = ($cleanBaseTitle !== '' ? $cleanBaseTitle . ' - ' : '') . $laguTitle; }
                        } else { $slideTitle = $cleanBaseTitle; }
                        
                        if($isReff) $slideTitle .= ' (Reff)';

                        $baitSlides = smartSplitText($cleanBaitText);
                        foreach($baitSlides as $bSlide) {
                            $allSlides[] = [
                                'type' => 'song_lyric',
                                'watermark' => $displayIndex,
                                'title' => $slideTitle,
                                'content' => $bSlide,
                                'use_camera' => $useCamera
                            ];
                        }
                    }
                } 
                elseif(isset($content['content'])) {
                     $slidesText = smartSplitText($content['content']);
                     foreach($slidesText as $st) {
                         $allSlides[] = ['type' => 'text', 'title' => $cleanBaseTitle, 'content' => $st, 'use_camera' => $useCamera];
                     }
                }
            } else {
                $slidesText = smartSplitText($content);
                foreach($slidesText as $st) {
                    $allSlides[] = ['type' => 'text', 'title' => $cleanBaseTitle, 'content' => $st, 'use_camera' => $useCamera];
                }
            }
        }
        
        if(isset($customSlides[$item->id])) {
            foreach($customSlides[$item->id] as $cSlide) {
                $cTitle = cleanSlideTitle($cSlide->title);
                $cUseCamera = false;
                $cleanContent = $cSlide->content;

                if (str_contains($cleanContent, '[KAMERA_AKTIF]')) {
                    $cUseCamera = true;
                    $cleanContent = str_replace('[KAMERA_AKTIF]', '', $cleanContent);
                }

                if (empty($cTitle)) {
                    $allSlides[] = ['type' => 'instruksi', 'title' => '', 'content' => $cleanContent, 'use_camera' => $cUseCamera];
                } else {
                    $cSlidesText = smartSplitText($cleanContent);
                    foreach($cSlidesText as $ct) {
                        $allSlides[] = ['type' => 'custom', 'title' => $cTitle, 'content' => $ct, 'use_camera' => $cUseCamera];
                    }
                }
            }
        }
    }
    $allSlides[] = ['type' => 'closing', 'title' => 'PENUTUP', 'content' => "TUHAN YESUS\nMEMBERKATI", 'use_camera' => false];
@endphp

<div id="toastAjax">Tersimpan Sinkron ⚡</div>

<div class="layout-wrapper">
    <div class="sidebar collapsed" id="sidebarPanel">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary">
            <h6 class="fw-bold m-0 text-white text-uppercase" style="letter-spacing: 1px; font-size: 0.8rem;">Kontrol Data</h6>
        </div>
        
        <div class="sidebar-content">
            <form id="liveForm" action="{{ route('liturgy.update', $schedule->id) }}" method="POST">
                @csrf

                <div class="card-edit" style="border-left: 3px solid #3182ce;">
                    <label class="form-label-header text-info">Desain Latar & Huruf</label>
                    <div class="mb-2">
                        <select id="bg_type" name="bg_type" onchange="toggleBgSettings(); updateDesignLive()" class="form-select form-select-sm bg-dark text-white border-secondary fw-medium">
                            <option value="gradient">Gradien Statis (Standar)</option>
                            <option value="anim-linear">Animasi Gradien Garis</option>
                            <option value="anim-radial">Animasi Gradien Bulat</option>
                            <option value="anim-sweep">Animasi Gradien Sapuan</option>
                            <option value="pattern-grid">Pola Grid Statis</option>
                            <option value="pattern-grid-anim">Animasi Pola Grid</option>
                            <option value="pattern-dots">Pola Titik</option>
                            <option value="pattern-stripes">Pola Garis Miring</option>
                            <option value="pattern-stripes-anim">Animasi Garis Miring</option>
                        </select>
                    </div>

                    <div id="anim_speed_wrapper" class="mb-2 px-1" style="display: none;">
                        <div class="d-flex justify-content-between text-secondary" style="font-size:0.65rem;">
                            <span>Laju Animasi:</span>
                            <span class="text-info fw-bold" id="speed_indicator">15s</span>
                        </div>
                        <input type="range" id="anim_speed" oninput="updateSpeedIndicator(); updateDesignLive()" class="form-range mt-1" min="3" max="40" step="1" value="15" dir="rtl" style="height: 0.5rem;"> 
                    </div>

                    <div class="row g-1 mb-2">
                        <div class="col-6">
                            <small class="text-secondary d-block" style="font-size:0.65rem;">Warna Tepi</small>
                            <input type="color" id="bg_edge_color" onchange="updateDesignLive()" class="form-control form-control-color w-100 p-0" value="#050505" style="height: 24px; border:none;">
                        </div>
                        <div class="col-6">
                            <small class="text-secondary d-block" style="font-size:0.65rem;">Warna Utama</small>
                            <input type="color" name="theme_color" id="bg_center_color" onchange="updateDesignLive()" class="form-control form-control-color w-100 p-0" value="{{ $schedule->theme_color ?? '#1b2735' }}" style="height: 24px; border:none;">
                        </div>
                    </div>

                    <div id="anim_colors_wrapper" class="row g-1 mb-2" style="display: none;">
                        <div class="col-6">
                            <small class="text-secondary d-block" style="font-size:0.65rem;">Transisi 1</small>
                            <input type="color" id="anim_color_1" onchange="updateDesignLive()" class="form-control form-control-color w-100 p-0" value="#2b6cb0" style="height: 24px; border:none;">
                        </div>
                        <div class="col-6">
                            <small class="text-secondary d-block" style="font-size:0.65rem;">Transisi 2</small>
                            <input type="color" id="anim_color_2" onchange="updateDesignLive()" class="form-control form-control-color w-100 p-0" value="#2c5282" style="height: 24px; border:none;">
                        </div>
                    </div>

                    <div class="row g-1 mb-1">
                        <div class="col-12 mb-1">
                            <select id="font_family" onchange="updateDesignLive()" class="form-select form-select-sm bg-dark text-white border-secondary" style="font-size:0.75rem;">
                                <option value="'Inter', Tahoma, sans-serif">Inter</option>
                                <option value="'Montserrat', sans-serif">Montserrat</option>
                                <option value="'Roboto', sans-serif">Roboto</option>
                                <option value="'Lato', sans-serif">Lato</option>
                                <option value="'Oswald', sans-serif">Oswald</option>
                                <option value="'Playfair Display', serif">Playfair Display</option>
                                <option value="Arial, sans-serif">Arial</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <small class="text-secondary d-block" style="font-size:0.65rem;">Warna Teks</small>
                            <input type="color" id="text_color" onchange="updateDesignLive()" class="form-control form-control-color w-100 p-0" value="#ffffff" style="height: 24px; border:none;">
                        </div>
                        <div class="col-6">
                            <small class="text-secondary d-block" style="font-size:0.65rem;">Bayangan</small>
                            <input type="color" id="shadow_color" onchange="updateDesignLive()" class="form-control form-control-color w-100 p-0" value="#000000" style="height: 24px; border:none;">
                        </div>
                    </div>
                </div>

                <div class="card-edit">
                    <label class="form-label-header">Keterangan Ibadah</label>
                    <div class="mb-1"><input type="text" name="theme" class="form-control" value="{{ $schedule->theme }}" placeholder="Tema Ibadah"></div>
                    <div><input type="text" name="preacher_name" class="form-control" value="{{ $schedule->preacher_name }}" placeholder="Nama Pelayan Firman"></div>
                </div>
                
                @foreach($schedule->liturgy->items as $item)
                    @php 
                        $detail = $schedule->details->where('liturgy_item_id', $item->id)->first();
                        $val = $detail ? $detail->dynamic_content : ($item->static_content ?? '');
                        
                        $cleanTitle = cleanSlideTitle($item->title);
                        $editTitleLabel = $cleanTitle ?: 'SLIDE INSTRUKSI';
                        
                        $titleLower = strtolower($item->title);
                        $type = 'teks_bebas';
                        if (str_contains($titleLower, 'nyanyian') || str_contains($titleLower, 'pujian')) $type = 'nyanyian'; 
                        elseif (str_contains($titleLower, 'alkitab') || str_contains($titleLower, 'bacaan')) $type = 'alkitab'; 
                        
                        $useCamera = is_array($val) && isset($val['use_camera']) && filter_var($val['use_camera'], FILTER_VALIDATE_BOOLEAN) == true;
                    @endphp
                    
                    <div class="card-edit">
                        <label class="form-label-header" style="color:#e2e8f0;">{{ $editTitleLabel }}</label>
                        
                        @if($item->is_dynamic)
                            @if($type === 'nyanyian')
                                <div class="input-group mb-1">
                                    <select id="buku-lagu-{{ $item->id }}" class="form-select bg-dark text-white" style="max-width: 80px; padding:2px;">
                                        <option value="KJ">KJ</option><option value="NKB">NKB</option><option value="PKJ">PKJ</option><option value="NR">NR</option><option value="BEBAS">Lain</option>
                                    </select>
                                    <input type="text" id="nomor-lagu-{{ $item->id }}" class="form-control" placeholder="Nomor">
                                    <button type="button" class="btn btn-secondary btn-sm px-2" onclick="tarikLagu({{ $item->id }}, event)">Tarik</button>
                                </div>
                                <input type="text" name="dynamic_content[{{ $item->id }}][judul]" class="form-control mb-1 fw-medium" placeholder="Judul Nyanyian" value="{{ is_array($val) ? ($val['judul'] ?? '') : '' }}">
                                
                                <div id="bait-container-{{ $item->id }}">
                                    @if(is_array($val) && isset($val['bait']) && is_array($val['bait']))
                                        @foreach($val['bait'] as $key => $baitText)
                                            @php 
                                                $isReffKey = (is_string($key) && stripos($key, 'ref') !== false) || preg_match('/^\[?reff?\]?[\s\:\.\-]?/i', $baitText);
                                                $displayKey = $isReffKey ? 'Reff' : preg_replace('/[^0-9]/', '', $key);
                                                if(empty($displayKey) && !$isReffKey) $displayKey = $key;
                                                $cleanTextForEdit = preg_replace('/^\[?REFF\]?\s*/i', '', $baitText);
                                            @endphp
                                            <div class="input-group mb-1 position-relative bait-item">
                                                <div class="input-group-text {{ $isReffKey ? 'bg-warning text-dark' : 'bg-dark text-secondary' }} p-0 overflow-hidden">
                                                    <input type="text" class="bait-label-input {{ $isReffKey ? 'bg-warning text-dark' : 'bg-dark text-secondary' }}" 
                                                        onchange="updateBaitName(this, '{{ $item->id }}')" value="{{ $displayKey }}">
                                                </div>
                                                <input type="hidden" class="bait-hidden-key" name="dynamic_content[{{ $item->id }}][bait][{{ $key }}]" value="{{ $isReffKey ? '[REFF] ' . trim($cleanTextForEdit) : trim($cleanTextForEdit) }}">
                                                <textarea class="form-control" rows="2" oninput="this.previousElementSibling.value = this.value">{{ $isReffKey ? '[REFF] ' . trim($cleanTextForEdit) : trim($cleanTextForEdit) }}</textarea>
                                                <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3" onclick="this.closest('.bait-item').remove(); silentSyncLive(false);" style="font-size: 14px;">&times;</button>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="input-group mb-1 position-relative bait-item">
                                            <div class="input-group-text bg-dark text-secondary p-0 overflow-hidden">
                                                <input type="text" class="bait-label-input bg-dark text-secondary" onchange="updateBaitName(this, '{{ $item->id }}')" value="1">
                                            </div>
                                            <input type="hidden" class="bait-hidden-key" name="dynamic_content[{{ $item->id }}][bait][1]" value="">
                                            <textarea class="form-control" rows="2" oninput="this.previousElementSibling.value = this.value"></textarea>
                                            <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3" onclick="this.closest('.bait-item').remove(); silentSyncLive(false);" style="font-size: 14px;">&times;</button>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn-add-slide mt-1" onclick="tambahBaitLagu('{{ $item->id }}')">Tambah Lirik Baris Baru</button>

                            @elseif($type === 'alkitab')
                                <div class="input-group mb-1">
                                    <input type="text" id="input-alkitab-{{ $item->id }}" class="form-control" placeholder="Cari Kitab/Ayat">
                                    <button type="button" class="btn btn-secondary btn-sm px-2" onclick="tarikAlkitab({{ $item->id }}, event)">Tarik</button>
                                </div>
                                <textarea id="textarea-{{ $item->id }}" name="dynamic_content[{{ $item->id }}][content]" class="form-control" rows="2">{{ is_array($val) ? ($val['content'] ?? '') : $val }}</textarea>
                            
                            @else
                                @php
                                    $customTitle = is_array($val) ? ($val['custom_title'] ?? '') : $cleanTitle;
                                    $textContent = is_array($val) ? ($val['content'] ?? '') : $val;
                                @endphp
                                <input type="text" name="dynamic_content[{{ $item->id }}][custom_title]" class="form-control mb-1 fw-medium text-info" value="{{ $customTitle }}" placeholder="Judul (Kosongkan utk Teks Tengah)">
                                <textarea id="textarea-{{ $item->id }}" name="dynamic_content[{{ $item->id }}][content]" class="form-control" rows="2">{{ $textContent }}</textarea>
                            @endif

                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="dynamic_content[{{ $item->id }}][use_camera]" value="0">
                                <input class="form-check-input" type="checkbox" name="dynamic_content[{{ $item->id }}][use_camera]" value="1" id="cam-cp-{{ $item->id }}" {{ $useCamera ? 'checked' : '' }}>
                                <label class="form-check-label text-secondary" style="font-size: 0.7rem;" for="cam-cp-{{ $item->id }}">Aktifkan Latar Kamera</label>
                            </div>

                            <div class="mt-2 pt-2 border-top" style="border-color: #333 !important;">
                                <div id="custom-slide-container-{{ $item->id }}">
                                    @if(isset($schedule->customSlides) && $schedule->customSlides->where('liturgy_item_id', $item->id)->count() > 0)
                                        @foreach($schedule->customSlides->where('liturgy_item_id', $item->id) as $index => $cSlide)
                                            @php 
                                                $isCameraCustom = is_string($cSlide->content) && str_contains($cSlide->content, '[KAMERA_AKTIF]'); 
                                                $cleanCustomContent = str_replace('[KAMERA_AKTIF]', '', $cSlide->content);
                                            @endphp
                                            <div class="custom-slide-box">
                                                <input type="text" name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][title]" class="form-control form-control-sm mb-1 fw-medium text-info" value="{{ cleanSlideTitle($cSlide->title) }}" placeholder="Judul">
                                                <textarea name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][content]" class="form-control form-control-sm" rows="1">{{ $cleanCustomContent }}</textarea>
                                                
                                                <div class="d-flex justify-content-between align-items-center mt-1">
                                                    <div class="form-check form-switch m-0">
                                                        <input type="hidden" name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][use_camera]" value="0">
                                                        <input class="form-check-input" type="checkbox" name="custom_slides[{{ $item->id }}][{{ $cSlide->id }}][use_camera]" value="1" id="cam-cus-{{ $cSlide->id }}" {{ $isCameraCustom ? 'checked' : '' }}>
                                                        <label class="form-check-label text-secondary" style="font-size: 0.65rem;" for="cam-cus-{{ $cSlide->id }}">Kamera</label>
                                                    </div>
                                                    <button type="button" class="btn btn-sm text-danger p-0" style="font-size:0.7rem;" onclick="this.parentElement.parentElement.remove(); silentSyncLive(false);">Hapus</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <button type="button" class="btn-add-slide mt-1" onclick="tambahSlideKhusus({{ $item->id }})">Sisipkan Slide Ekstra</button>
                            </div>

                        @else
                            <textarea name="dynamic_content[{{ $item->id }}]" class="form-control text-secondary" style="background:#1a1a1a!important;" rows="2" readonly>{{ $item->static_content }}</textarea>
                        @endif
                    </div>
                @endforeach
            </form>
            
            <div class="floating-save">
                <button type="button" onclick="silentSyncLive(true)" id="btnSaveLive" class="btn btn-primary-custom w-100 py-2">Terapkan Perubahan</button>
            </div>
        </div>
    </div>

    <div class="presenter-view" id="mainPanel">
        <div class="ppt-topbar">
            <div class="d-flex align-items-center gap-3">
                <button type="button" id="btnToggleSidebar" class="btn-ui-toggle active" onclick="toggleSidebar()">Tampilkan Editor</button>
                <button type="button" id="btnToggleGallery" class="btn-ui-toggle" onclick="toggleGallery()">Buka Galeri</button>
                <div class="timer-display" style="margin-left: 10px;">
                    <span id="liveTimer">00:00:00</span>
                    <span class="text-info" style="font-size:0.7rem;">| {{ strtoupper($schedule->liturgy->name ?? 'IBADAH') }}</span>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <button onclick="openProjectorMultiMonitor()" class="btn btn-primary-custom btn-sm px-4">Tampilkan Proyektor</button>
                <a href="{{ route('liturgy.gallery') }}" class="btn btn-danger ms-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 50%; text-decoration: none; font-size: 1.2rem; font-weight: bold; padding:0; line-height: 1;" title="Tutup & Kembali ke Daftar">&times;</a>
            </div>
        </div>

        <div class="ppt-layout">
            
            <div class="ppt-main-col">
                <div class="current-slide-box monitor-box" id="monitor-current">
                    <video id="preview-cam-current" class="preview-cam" autoplay playsinline muted></video>
                    <span class="label-badge">TAYANGAN SAAT INI</span>
                    <div id="virtual-render-current" style="width: 100%; height: 100%; position:relative; z-index:5;"></div>
                    
                    <div class="per-slide-font-control">
                        <button type="button" onclick="changeSlideFont(-0.5)">-</button>
                        <span id="slide-font-indicator">Otomatis</span>
                        <button type="button" onclick="changeSlideFont(0.5)">+</button>
                        <button type="button" onclick="resetSlideFont()" style="background:transparent; border-color:#666; font-size:0.65rem; font-weight:normal;">Reset</button>
                    </div>
                </div>
                
                <div class="ppt-controls">
                    <button class="ppt-btn-nav" onclick="controlProjector('prev')" title="Slide Sebelumnya">&#8593;</button>
                    <div class="ppt-slide-counter" id="slide-num">Slide 0 dari 0</div>
                    <button class="ppt-btn-nav" onclick="controlProjector('next')" title="Slide Selanjutnya">&#8595;</button>
                </div>
            </div>

            <div class="ppt-side-col">
                <div>
                    <div class="text-secondary mb-2" style="font-size: 0.85rem; font-weight: 600;">Tayangan Berikutnya</div>
                    <div class="next-slide-box monitor-box" id="monitor-next">
                        <video id="preview-cam-next" class="preview-cam" autoplay playsinline muted></video>
                        <div id="virtual-render-next" style="width: 100%; height: 100%; position:relative; z-index:5;"></div>
                    </div>
                </div>
            </div>

            <div class="fullscreen-gallery-overlay" id="slideGallery">
                </div>

        </div>
    </div>
</div>

<script>
    window.cpWartaInterval = null;
    window.cpWartaNextInterval = null;
    let isCameraInitialized = false;

    // Timer Logika
    let startTime = Date.now();
    setInterval(() => {
        let diff = Math.floor((Date.now() - startTime) / 1000);
        let h = Math.floor(diff / 3600).toString().padStart(2, '0');
        let m = Math.floor((diff % 3600) / 60).toString().padStart(2, '0');
        let s = (diff % 60).toString().padStart(2, '0');
        document.getElementById('liveTimer').innerText = `${h}:${m}:${s}`;
    }, 1000);

    const scheduleId = {{ $schedule->id ?? 0 }};
    let allSlidesData = @json($allSlides); 
    let currentSlide = parseInt(localStorage.getItem('last_slide_index')) || 0;
    let customFonts = JSON.parse(localStorage.getItem('custom_fonts_' + scheduleId)) || {};

    // =========================================================================
    // FITUR: AUTO-SAVE & REALTIME SYNC (TANPA RELOAD)
    // =========================================================================
    let liveTypingTimer;
    
    document.getElementById('liveForm').addEventListener('input', function() {
        clearTimeout(liveTypingTimer);
        liveTypingTimer = setTimeout(() => { silentSyncLive(false); }, 800); 
    });
    
    document.getElementById('liveForm').addEventListener('change', function() {
        silentSyncLive(false);
    });

    function silentSyncLive(showButtonEffect = false) {
        const form = document.getElementById('liveForm');
        const btn = document.getElementById('btnSaveLive');
        const formData = new FormData(form);

        if(showButtonEffect) {
            btn.innerText = 'Menyimpan & Sync...';
            btn.disabled = true;
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(() => {
            localStorage.setItem('liturgy_update', Date.now()); 
            
            fetch(window.location.href)
                .then(res => res.text())
                .then(html => {
                    const match = html.match(/let allSlidesData = (\[.*?\]);/s);
                    if(match && match[1]) {
                        allSlidesData = JSON.parse(match[1]);
                        buildGallery();
                        updateConsoleView();
                    }
                });
        })
        .catch(err => {
            console.warn("Internet terputus! Data tidak tersimpan di Cloud.");
        })
        .finally(() => {
            if(showButtonEffect) {
                btn.innerText = 'Terapkan Perubahan';
                btn.disabled = false;
            }
            const toast = document.getElementById('toastAjax');
            toast.style.opacity = 1;
            setTimeout(() => { toast.style.opacity = 0; }, 3000);
        });
    }

    // =========================================================================
    // FITUR: LAYAR 2 OTOMATIS
    // =========================================================================
    async function openProjectorMultiMonitor() {
        const url = "{{ route('liturgy.presentation', $schedule->id) }}";
        let features = `width=${window.screen.availWidth},height=${window.screen.availHeight},left=0,top=0,menubar=no,toolbar=no,location=no,status=no`;
        
        try {
            if ('getScreenDetails' in window) {
                const screenDetails = await window.getScreenDetails();
                const secondaryScreen = screenDetails.screens.find(s => s !== screenDetails.currentScreen);
                
                if (secondaryScreen) {
                    features = `left=${secondaryScreen.availLeft},top=${secondaryScreen.availTop},width=${secondaryScreen.availWidth},height=${secondaryScreen.availHeight},menubar=no,toolbar=no,location=no,status=no`;
                } else {
                    const primary = screenDetails.screens[0];
                    features = `left=${primary.availLeft},top=${primary.availTop},width=${primary.availWidth},height=${primary.availHeight},menubar=no,toolbar=no,location=no,status=no`;
                }
            }
        } catch (err) {}
        window.open(url, "ProjectorWindow", features);
    }

    // =========================================================================
    // FITUR: PRATINJAU KAMERA LIVE
    // =========================================================================
    async function initPreviewCamera() {
        if (isCameraInitialized) return;
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            document.getElementById('preview-cam-current').srcObject = stream;
            document.getElementById('preview-cam-next').srcObject = stream;
            isCameraInitialized = true;
        } catch(e) { console.log("Gagal akses kamera untuk preview", e); }
    }


    // =========================================================================
    // FUNGSI UI & RENDER VIRTUAL
    // =========================================================================
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebarPanel');
        const btn = document.getElementById('btnToggleSidebar');
        sidebar.classList.toggle('collapsed');
        if (sidebar.classList.contains('collapsed')) {
            btn.innerText = "Tampilkan Editor"; btn.classList.add('active');
        } else {
            btn.innerText = "Sembunyikan Editor"; btn.classList.remove('active');
        }
    }

    function toggleGallery() {
        const gallery = document.getElementById('slideGallery');
        const btn = document.getElementById('btnToggleGallery');
        
        gallery.classList.toggle('expanded');
        
        if (gallery.classList.contains('expanded')) {
            btn.innerText = "Tutup Galeri";
            btn.classList.add('active');
            const activeThumb = gallery.querySelector('.mini-slide-thumb.active');
            if(activeThumb) {
                activeThumb.scrollIntoView({ behavior: 'auto', block: 'center' }); 
            }
        } else {
            btn.innerText = "Buka Galeri";
            btn.classList.remove('active');
        }
    }

    function renderVirtualSlide(slide, index) {
        if(!slide) return '<div class="sp-container"><div class="vp-content" style="color:#666;">SELESAI</div></div>';

        let customSize = customFonts[index];
        let fontSizeStyle = customSize ? `font-size: ${customSize}cqw !important;` : '';
        let innerHTML = '';
        
        let isCamActive = (slide.use_camera === true || slide.use_camera === 'true' || slide.use_camera == 1);
        let containerClass = isCamActive ? 'sp-container mode-kamera' : 'sp-container';

        if(slide.type === 'cover') {
            innerHTML = `
                <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" style="height: 18cqh; margin-bottom: 3cqh; filter: drop-shadow(0px 4px 10px rgba(0,0,0,0.5));">
                <div class="vp-title-header" style="font-size: 6.5cqw; font-weight: 900; margin-bottom: 1.5cqh; border:none;">${slide.title}</div>
                <div style="font-size: 2.5cqw; color: #cbd5e0;">${slide.date}</div>
                ${slide.theme ? `<div style="font-size: 2.5cqw; color: #cbd5e0; margin-top: 2cqh;">TEMA: ${slide.theme}</div>` : ''}
            `;
        }
        else if (slide.type === 'announcements_slideshow') {
            let imagesHtml = slide.images.map((img, idx) => `
                <div class="warta-item-cp warta-item-cp-${index}" data-duration="${img.duration || 5}" style="position:absolute; top:0; left:0; width:100%; height:100%; opacity: ${idx===0 ? 1 : 0}; transition: opacity 1.5s ease; display:flex; flex-direction:column; justify-content:flex-end; align-items:center;">
                    <img src="${img.image_url}" style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:contain; z-index:1;">
                    ${img.caption ? `<div style="position:relative; z-index:2; margin-bottom: 5cqh; background:rgba(0,0,0,0.8); color:#fff; padding:1.5cqh 3cqw; border-radius:50px; font-size:2.5cqw; font-weight:bold;">${img.caption}</div>` : ''}
                </div>
            `).join('');
            return `<div style="position: absolute; top:0; left:0; width:100%; height:100%; background:#000; overflow:hidden;">${imagesHtml}</div>`;
        }
        else if (slide.type === 'instruksi') {
            if(!fontSizeStyle) fontSizeStyle = slide.content.length < 30 ? 'font-size: 7cqw;' : 'font-size: 5cqw;';
            innerHTML = `<div class="vp-instruksi" style="${fontSizeStyle}">${slide.content.replace(/\n/g, '<br>')}</div>`;
        }
        else if (slide.type === 'song_cover') {
            innerHTML = `
                <div style="margin: auto; text-align: center;">
                    ${slide.title ? `<div style="font-size: 2.5cqw; margin-bottom: 1.5cqh; text-transform: uppercase; color: #cbd5e0; letter-spacing: 0.2cqw;">${slide.title}</div>` : ''}
                    <div style="font-size: 5.5cqw; font-weight: 900; text-shadow: 0px 4cqh 15cqw var(--shadow-color);">${slide.content}</div>
                </div>
            `;
        }
        else if (slide.type === 'song_lyric') {
            innerHTML = `
                ${slide.watermark !== '' && !isCamActive ? `<div class="vp-watermark">${slide.watermark}</div>` : ''}
                ${slide.title && !isCamActive ? `<div class="vp-title-header">${slide.title}</div>` : ''}
                <div class="vp-content" style="${fontSizeStyle}">${slide.content.replace(/\n/g, '<br>')}</div>
            `;
        }
        else if (slide.type === 'closing') {
            innerHTML = `<div style="font-size: 6.5cqw; font-weight: 900; text-transform: uppercase; margin: auto;">TUHAN YESUS<br><span style="color:#fcd34d;">MEMBERKATI</span></div>`;
        }
        else { 
            innerHTML = `
                ${slide.title && !isCamActive ? `<div class="vp-title-header ${slide.type === 'custom' ? 'text-info' : ''}">${slide.title}</div>` : ''}
                <div class="vp-content" style="${fontSizeStyle}">${slide.content.replace(/\n/g, '<br>')}</div>
            `;
        }

        // Tampilan khusus "TV Broadcast" bila Mode Kamera aktif
        if (isCamActive && slide.type !== 'cover' && slide.type !== 'closing' && slide.type !== 'announcements_slideshow') {
            innerHTML = `
                <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" class="kamera-logo-preview" alt="Logo">
                ${slide.title ? `<div class="kamera-watermark-title-preview">${slide.title}</div>` : ''}
                <div class="vp-inner-wrapper">${innerHTML}</div>
            `;
        }

        return `<div class="${containerClass}">${innerHTML}</div>`;
    }

    function toggleBgSettings() {
        const type = document.getElementById('bg_type').value;
        const isAnim = type.includes('anim-');
        document.getElementById('anim_colors_wrapper').style.display = isAnim ? 'flex' : 'none';
        if(type.includes('anim') || type.includes('pattern')) { document.getElementById('anim_speed_wrapper').style.display = 'block'; } 
        else { document.getElementById('anim_speed_wrapper').style.display = 'none'; }
    }
    
    function updateSpeedIndicator() { document.getElementById('speed_indicator').innerText = document.getElementById('anim_speed').value + 's'; }

    function applyVirtualDesign(settings) {
        if(!settings) return;
        document.documentElement.style.setProperty('--bg-center', settings.bgCenterColor || '#1b2735');
        document.documentElement.style.setProperty('--bg-edge', settings.bgEdgeColor || '#050505');
        document.documentElement.style.setProperty('--text-color', settings.textColor || '#ffffff');
        document.documentElement.style.setProperty('--font-family', settings.fontFamily || "'Inter', Tahoma, sans-serif");
        
        let shadowHex = settings.shadowColor || '#000000';
        let r = parseInt(shadowHex.slice(1, 3), 16), g = parseInt(shadowHex.slice(3, 5), 16), b = parseInt(shadowHex.slice(5, 7), 16);
        document.documentElement.style.setProperty('--shadow-color', `rgba(${r}, ${g}, ${b}, 0.9)`);

        document.querySelectorAll('.sp-container:not(.mode-kamera)').forEach(el => {
            el.style.background = ''; el.style.backgroundColor = ''; el.style.backgroundImage = ''; el.style.backgroundSize = ''; el.style.animation = '';
            
            const spd = settings.animSpeed || 15;
            const ac1 = settings.animColor1 || '#2b6cb0'; const ac2 = settings.animColor2 || '#2c5282';

            if (settings.bgType === 'gradient' || !settings.bgType) { el.style.background = `radial-gradient(circle at center, ${settings.bgCenterColor} 0%, ${settings.bgEdgeColor} 100%)`; } 
            else if (settings.bgType === 'anim-linear') { el.style.background = `linear-gradient(-45deg, ${settings.bgCenterColor}, ${settings.bgEdgeColor}, ${ac1}, ${ac2})`; el.style.backgroundSize = '400% 400%'; el.style.animation = `gradientBG ${spd}s ease infinite`; } 
            else if (settings.bgType === 'anim-radial') { el.style.background = `radial-gradient(circle, ${settings.bgCenterColor}, ${ac1}, ${settings.bgEdgeColor}, ${ac2})`; el.style.backgroundSize = '400% 400%'; el.style.animation = `gradientBG ${spd}s ease infinite`; } 
            else if (settings.bgType === 'anim-sweep') { el.style.background = `linear-gradient(90deg, ${settings.bgCenterColor}, ${ac1}, ${settings.bgEdgeColor}, ${ac2}, ${settings.bgCenterColor})`; el.style.backgroundSize = '400% 100%'; el.style.animation = `gradientBG ${spd}s linear infinite`; }
            else if (settings.bgType === 'pattern-grid') { el.style.backgroundColor = settings.bgEdgeColor; el.style.backgroundImage = `linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px)`; el.style.backgroundSize = '4cqw 4cqw'; }
            else if (settings.bgType === 'pattern-grid-anim') { el.style.backgroundColor = settings.bgEdgeColor; el.style.backgroundImage = `linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px)`; el.style.backgroundSize = '4cqw 4cqw'; el.style.animation = `moveGridCP ${spd}s linear infinite`; } 
            else if (settings.bgType === 'pattern-dots') { el.style.backgroundColor = settings.bgEdgeColor; el.style.backgroundImage = `radial-gradient(${settings.bgCenterColor} 3px, transparent 3px)`; el.style.backgroundSize = '4cqw 4cqw'; } 
            else if (settings.bgType === 'pattern-stripes') { el.style.backgroundColor = settings.bgEdgeColor; el.style.backgroundImage = `repeating-linear-gradient(45deg, ${settings.bgCenterColor} 0, ${settings.bgCenterColor} 2px, transparent 2px, transparent 50%)`; el.style.backgroundSize = '4cqw 4cqw'; }
            else if (settings.bgType === 'pattern-stripes-anim') { el.style.backgroundColor = settings.bgEdgeColor; el.style.backgroundImage = `repeating-linear-gradient(45deg, ${settings.bgCenterColor} 0, ${settings.bgCenterColor} 2px, transparent 2px, transparent 50%)`; el.style.backgroundSize = '4cqw 4cqw'; el.style.animation = `moveStripesCP ${spd}s linear infinite`; }
        });
    }

    function updateDesignLive() {
        const bgType = document.getElementById('bg_type').value; 
        const animSpeed = document.getElementById('anim_speed').value; const bgCenterColor = document.getElementById('bg_center_color').value;
        const bgEdgeColor = document.getElementById('bg_edge_color').value; const animColor1 = document.getElementById('anim_color_1').value;
        const animColor2 = document.getElementById('anim_color_2').value; const textColor = document.getElementById('text_color').value;
        const fontFamily = document.getElementById('font_family').value; const shadowColor = document.getElementById('shadow_color').value;

        const designSettings = { bgType, animSpeed, fontFamily, bgCenterColor, bgEdgeColor, animColor1, animColor2, textColor, shadowColor };
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
        }
    }

    function changeSlideFont(step) {
        let currentSize = customFonts[currentSlide] ? parseFloat(customFonts[currentSlide]) : 5.0; 
        let newSize = (currentSize + step).toFixed(1);
        if(newSize < 2.0) newSize = 2.0; if(newSize > 15.0) newSize = 15.0;
        customFonts[currentSlide] = newSize; saveAndSyncFont();
    }
    function resetSlideFont() { delete customFonts[currentSlide]; saveAndSyncFont(); }
    function saveAndSyncFont() { localStorage.setItem('custom_fonts_' + scheduleId, JSON.stringify(customFonts)); localStorage.setItem('font_sync_trigger', Date.now()); updateConsoleView(); }

    function controlProjector(action, specificIndex = null) {
        if(action === 'next') currentSlide++; else if(action === 'prev') currentSlide--; else if(action === 'jump') currentSlide = specificIndex;
        if (currentSlide < 0) currentSlide = 0; if (currentSlide >= allSlidesData.length) currentSlide = allSlidesData.length - 1;
        
        localStorage.setItem('projector_command', JSON.stringify({ action: 'jump', index: currentSlide, time: Date.now() }));
        localStorage.setItem('last_slide_index', currentSlide); 
        
        if(document.getElementById('slideGallery').classList.contains('expanded') && action === 'jump') {
            toggleGallery(); 
        }
        
        updateConsoleView();
    }

    // Penyesuaian Keyboard: Arrow Down & Right = Next | Arrow Up & Left = Prev
    function handleKeyboard(e) {
        if (['TEXTAREA', 'INPUT', 'SELECT'].includes(document.activeElement.tagName)) return;
        if (['ArrowRight', 'ArrowDown', ' ', 'PageDown', 'Enter'].includes(e.key)) { e.preventDefault(); controlProjector('next'); }
        if (['ArrowLeft', 'ArrowUp', 'PageUp'].includes(e.key)) { e.preventDefault(); controlProjector('prev'); }
    }

    function updateConsoleView() {
        clearTimeout(window.cpWartaInterval); 
        clearTimeout(window.cpWartaNextInterval); 
        
        document.getElementById('virtual-render-current').innerHTML = renderVirtualSlide(allSlidesData[currentSlide], currentSlide);
        document.getElementById('virtual-render-next').innerHTML = renderVirtualSlide(allSlidesData[currentSlide + 1], currentSlide + 1);
        document.getElementById('slide-num').innerText = `Slide ${currentSlide + 1} dari ${allSlidesData.length}`;
        
        let isCurrentCam = allSlidesData[currentSlide] && (allSlidesData[currentSlide].use_camera == 1 || allSlidesData[currentSlide].use_camera === true || allSlidesData[currentSlide].use_camera === 'true');
        let isNextCam = allSlidesData[currentSlide + 1] && (allSlidesData[currentSlide + 1].use_camera == 1 || allSlidesData[currentSlide + 1].use_camera === true || allSlidesData[currentSlide + 1].use_camera === 'true');
        
        document.getElementById('preview-cam-current').style.display = isCurrentCam ? 'block' : 'none';
        document.getElementById('preview-cam-next').style.display = isNextCam ? 'block' : 'none';
        
        if (isCurrentCam || isNextCam) { initPreviewCamera(); }

        const gallery = document.getElementById('slideGallery');
        document.querySelectorAll('.mini-slide-thumb').forEach((thumb, i) => { 
            thumb.classList.toggle('active', i === currentSlide); 
        });

        const fontIndicator = document.getElementById('slide-font-indicator');
        if (customFonts[currentSlide]) { fontIndicator.innerText = customFonts[currentSlide] + 'vw'; fontIndicator.style.color = '#fcd34d'; } 
        else { fontIndicator.innerText = 'Otomatis'; fontIndicator.style.color = '#cbd5e0'; }

        let currentItems = document.getElementById('monitor-current').querySelectorAll(`.warta-item-cp-${currentSlide}`);
        if(currentItems.length > 1) {
            let cwIdx = 0;
            const runNextCurrent = () => {
                currentItems[cwIdx].style.opacity = 0; cwIdx = (cwIdx + 1) % currentItems.length; currentItems[cwIdx].style.opacity = 1;
                window.cpWartaInterval = setTimeout(runNextCurrent, (parseInt(currentItems[cwIdx].dataset.duration) || 5) * 1000);
            };
            window.cpWartaInterval = setTimeout(runNextCurrent, (parseInt(currentItems[0].dataset.duration) || 5) * 1000);
        }

        let nextIndex = currentSlide + 1;
        let nextItems = document.getElementById('monitor-next').querySelectorAll(`.warta-item-cp-${nextIndex}`);
        if(nextItems.length > 1) {
            let nwIdx = 0;
            const runNextNext = () => {
                nextItems[nwIdx].style.opacity = 0; nwIdx = (nwIdx + 1) % nextItems.length; nextItems[nwIdx].style.opacity = 1;
                window.cpWartaNextInterval = setTimeout(runNextNext, (parseInt(nextItems[nwIdx].dataset.duration) || 5) * 1000);
            };
            window.cpWartaNextInterval = setTimeout(runNextNext, (parseInt(nextItems[0].dataset.duration) || 5) * 1000);
        }

        applyVirtualDesign(JSON.parse(localStorage.getItem('live_design_settings')));
    }

    function buildGallery() {
        const gallery = document.getElementById('slideGallery'); 
        gallery.innerHTML = '';
        allSlidesData.forEach((slide, i) => {
            const thumb = document.createElement('div'); 
            thumb.className = 'mini-slide-thumb';
            thumb.title = "Klik ganda (2x) untuk menampilkan slide ini"; 
            
            const badge = document.createElement('div');
            badge.className = 'mini-thumb-badge';
            badge.innerText = i + 1;
            thumb.appendChild(badge);

            const slideContent = document.createElement('div');
            slideContent.style.width = '100%';
            slideContent.style.height = '100%';
            slideContent.innerHTML = renderVirtualSlide(slide, i);
            
            thumb.appendChild(slideContent);
            thumb.ondblclick = () => { 
                controlProjector('jump', i); 
            }; 
            gallery.appendChild(thumb);
        });
        
        applyVirtualDesign(JSON.parse(localStorage.getItem('live_design_settings')));
    }

    function updateBaitName(inputElement, blockId) {
        let newName = inputElement.value.trim(); if(newName === '') newName = 'Bait';
        let hiddenInput = inputElement.closest('.bait-item').querySelector('.bait-hidden-key');
        let formKey = newName;
        
        if(newName.toLowerCase() === 'reff') {
            inputElement.classList.replace('bg-dark', 'bg-warning'); inputElement.classList.replace('text-secondary', 'text-dark');
            let existingKey = hiddenInput ? hiddenInput.name.match(/\[bait\]\[(.*?)\]/) : null;
            if (existingKey && existingKey[1].toLowerCase().includes('ref')) { formKey = existingKey[1]; } 
            else { formKey = 'ref_' + Math.random().toString(36).substr(2, 5); }
        } else {
            inputElement.classList.replace('bg-warning', 'bg-dark'); inputElement.classList.replace('text-dark', 'text-secondary');
        }
        if(hiddenInput) { hiddenInput.name = `dynamic_content[${blockId}][bait][${formKey}]`; }
        silentSyncLive(false);
    }

    function tambahBaitLagu(blockId) {
        const container = document.getElementById(`bait-container-${blockId}`);
        const baitNum = Date.now().toString().slice(-4);
        const html = `
            <div class="input-group mb-1 position-relative bait-item">
                <div class="input-group-text bg-dark text-secondary p-0 overflow-hidden">
                    <input type="text" class="bait-label-input bg-dark text-secondary" onchange="updateBaitName(this, '${blockId}')" value="${baitNum}">
                </div>
                <input type="hidden" class="bait-hidden-key" name="dynamic_content[${blockId}][bait][${baitNum}]" value="">
                <textarea class="form-control" rows="2" oninput="this.previousElementSibling.value = this.value"></textarea>
                <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3" onclick="this.closest('.bait-item').remove(); silentSyncLive(false);" style="font-size: 14px;">&times;</button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    function tambahSlideKhusus(itemId) {
        const container = document.getElementById('custom-slide-container-' + itemId); const slideId = Math.random().toString(36).substr(2, 9);
        const html = `
            <div class="custom-slide-box">
                <input type="text" name="custom_slides[${itemId}][${slideId}][title]" class="form-control form-control-sm mb-1 fw-medium text-info" placeholder="Judul Sisipan">
                <textarea name="custom_slides[${itemId}][${slideId}][content]" class="form-control form-control-sm" rows="1" placeholder="Isi teks..."></textarea>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <div class="form-check form-switch m-0">
                        <input type="hidden" name="custom_slides[${itemId}][${slideId}][use_camera]" value="0">
                        <input class="form-check-input" type="checkbox" name="custom_slides[${itemId}][${slideId}][use_camera]" value="1" id="cam-cus-${slideId}">
                        <label class="form-check-label text-secondary" style="font-size: 0.65rem;" for="cam-cus-${slideId}">Kamera</label>
                    </div>
                    <button type="button" class="btn btn-sm text-danger p-0" style="font-size:0.7rem;" onclick="this.parentElement.parentElement.remove(); silentSyncLive(false);">Hapus</button>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html); 
    }

    function tarikLagu(itemId, event) {
        const buku = document.getElementById(`buku-lagu-${itemId}`).value; const nomor = document.getElementById(`nomor-lagu-${itemId}`).value.trim();
        const judulInput = document.querySelector(`input[name="dynamic_content[${itemId}][judul]"]`); const container = document.getElementById(`bait-container-${itemId}`);
        const btn = event.currentTarget; if(!nomor) return alert('Ketik nomor nyanyian.');
        const originalText = btn.innerHTML; btn.innerHTML = '...'; btn.disabled = true;
        
        fetch(`/api/fetch-lagu?buku=${buku}&nomor=${nomor}`).then(res => res.json()).then(data => {
            if(data.success) {
                judulInput.value = data.judul; container.innerHTML = ''; const baits = data.text.split('===SLIDE_BREAK==='); 
                let verseCounter = 1;
                baits.forEach((bait, idx) => {
                    let baitRaw = bait.trim();
                    if(baitRaw !== '') {
                        let isReff = baitRaw.toUpperCase().startsWith('[REFF]') || baitRaw.toLowerCase().startsWith('reff') || baitRaw.toLowerCase().startsWith('ref');
                        let cleanBait = baitRaw.replace(/^\[?REFF\]?\s*/i, '');
                        let labelText = isReff ? 'Reff' : verseCounter;
                        let uniqueKey = isReff ? 'ref_' + idx : verseCounter;
                        let bgClass = isReff ? 'bg-warning text-dark' : 'bg-dark text-secondary';
                        const html = `
                            <div class="input-group mb-1 position-relative bait-item">
                                <div class="input-group-text ${bgClass} p-0 overflow-hidden">
                                    <input type="text" class="bait-label-input ${bgClass}" onchange="updateBaitName(this, '${itemId}')" value="${labelText}">
                                </div>
                                <input type="hidden" class="bait-hidden-key" name="dynamic_content[${itemId}][bait][${uniqueKey}]" value="${isReff ? '[REFF]\\n' + cleanBait : cleanBait}">
                                <textarea class="form-control" rows="3" oninput="this.previousElementSibling.value = this.value">${isReff ? '[REFF]\\n' + cleanBait : cleanBait}</textarea>
                                <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-1 z-3" onclick="this.closest('.bait-item').remove(); silentSyncLive(false);" style="font-size: 14px;">&times;</button>
                            </div>
                        `;
                        container.insertAdjacentHTML('beforeend', html); 
                        if (!isReff) verseCounter++; 
                    }
                });
                silentSyncLive(false); 
            } else { alert(data.message); }
        }).catch(err => alert('Data tidak ditemukan.')).finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
    }

    function tarikAlkitab(itemId, event) {
        const inputField = document.getElementById('input-alkitab-' + itemId); const textarea = document.getElementById('textarea-' + itemId);
        const btn = event.currentTarget; const query = inputField.value.trim();
        if(!query) return alert('Ketik referensi ayat.'); 
        const originalText = btn.innerHTML; btn.innerHTML = '...'; btn.disabled = true;
        fetch(`/api/fetch-alkitab?q=${encodeURIComponent(query)}`).then(res => res.json()).then(data => {
            if(data.success) { 
                textarea.value = query.toUpperCase() + "\n===SLIDE_BREAK===\n" + data.text; 
                silentSyncLive(false); 
            } else alert(data.message);
        }).catch(() => alert('Terjadi gangguan jaringan.')).finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
    }

    window.addEventListener('storage', (e) => { 
        if (e.key === 'last_slide_index') { currentSlide = parseInt(e.newValue); updateConsoleView(); }
    });
    
    window.onload = () => { loadSavedDesign(); buildGallery(); updateConsoleView(); };
</script>
</body>
</html>