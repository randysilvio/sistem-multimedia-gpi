<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layar Proyektor - GPI Papua</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&family=Montserrat:wght@600;800&family=Playfair+Display:wght@600;800&family=Roboto:wght@500;700&family=Oswald:wght@500;700&family=Lato:wght@700;900&display=swap');
        
        body, html { 
            margin: 0; padding: 0; width: 100%; height: 100%; 
            background: radial-gradient(circle at center, {{ $schedule->theme_color ?? '#1b2735' }} 0%, #050505 100%);
            color: #ffffff; 
            font-family: 'Inter', Tahoma, sans-serif; 
            overflow: hidden; 
            transition: background 0.5s ease, color 0.5s ease;
        }

        /* KEYFRAME ANIMASI BACKGROUND CSS */
        @keyframes gradientBG { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        @keyframes moveGrid { 0% { background-position: 0 0; } 100% { background-position: 4vw 4vw; } }
        @keyframes moveStripes { 0% { background-position: 0 0; } 100% { background-position: 4vw 0; } }

        #start-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; background: rgba(0,0,0,0.95);
            display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; border: 2px solid #2b6cb0;
        }
        .start-title { color: #3182ce; font-size: 3vw; margin-bottom: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; }
        .start-subtitle { color: #a0aec0; font-size: 1.5vw; text-align: center; font-weight: 400; }
        
        .slide { 
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
            display: flex; flex-direction: column; justify-content: flex-start; align-items: center; 
            text-align: center; padding: 7vh 6vw; box-sizing: border-box; 
            opacity: 0; 
            z-index: 0; pointer-events: none; 
        }
        .slide.active { opacity: 1; z-index: 10; pointer-events: auto; }
        
        .bait-watermark { position: absolute; top: -3vh; left: 3vw; font-size: 55vh; font-weight: 900; line-height: 1; z-index: 1; background: linear-gradient(180deg, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0.02) 80%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; user-select: none; pointer-events: none;}
        .instruksi-jemaat { position: relative; z-index: 2; margin: auto; font-size: 5.5vw; color: inherit; font-weight: 700; text-transform: uppercase; letter-spacing: 4px; text-shadow: 0px 4px 20px rgba(0,0,0,0.8); }
        .judul-sesi { position: relative; z-index: 2; flex-shrink: 0; font-size: 2.2vw; color: rgba(255, 255, 255, 0.7); text-transform: uppercase; letter-spacing: 4px; font-weight: 600; margin-top: 2vh; margin-bottom: 0; border-bottom: 2px solid rgba(255, 255, 255, 0.15); padding-bottom: 12px; width: 85%; }
        .judul-custom { color: #63b3ed; border-bottom-color: rgba(99, 179, 237, 0.3); }
        .isi-teks { position: relative; z-index: 2; margin-top: auto; margin-bottom: auto; font-size: 5vw; font-weight: 700; line-height: 1.45; text-shadow: 0px 4px 15px rgba(0,0,0,0.9); max-width: 95%; }
        .teks-kuning { color: #ecc94b; }
        
        .welcome-wrapper { position: relative; z-index: 2; margin: auto; }
        .welcome-title { font-size: 6.5vw; font-weight: 900; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 3px; text-shadow: 0px 5px 20px rgba(0,0,0,0.8); line-height: 1.1; }
        .welcome-sub { font-size: 2.5vw; color: #cbd5e0; font-weight: 400; letter-spacing: 1px; }
        .nav-hint { position: absolute; bottom: 15px; right: 20px; font-size: 0.8vw; color: rgba(255,255,255,0.15); z-index: 100; font-weight: 400; }
    </style>
</head>
<body>

    <div id="start-overlay" onclick="launchFullscreen()">
        <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" alt="GPI Papua" style="height: 100px; margin-bottom: 20px;">
        <div class="start-title">Sistem Siap Ditayangkan</div>
        <div class="start-subtitle">Klik area ini untuk mengaktifkan Fullscreen Proyektor</div>
    </div>

    @php
    // FUNGSI PEMBERSIH JUDUL (Mencegah teks dobel dan menghapus awalan yang tidak perlu)
    if (!function_exists('cleanSlideTitle')) {
        function cleanSlideTitle($title) {
            if (!is_string($title)) return '';
            // Hapus kata (Opsional)
            $title = str_ireplace('(opsional)', '', $title);
            // Hapus teks "Slide Bebas" beserta tanda titik dua/strip setelahnya
            $title = preg_replace('/slide bebas\s*[:\-]?\s*/i', '', $title);
            // Hapus awalan "Nyanyian:" atau "Nyanyian -" 
            $title = preg_replace('/^nyanyian\s*[:\-]\s*/i', '', $title);
            // Hapus jika isinya hanya kata "Nyanyian"
            $title = preg_replace('/^nyanyian\s*$/i', '', trim($title));
            // Rapikan spasi atau tanda baca yang tersisa di ujung
            return trim($title, " -:");
        }
    }

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
                        $currentSlide[] = trim($currentLine); $currentLine = $word;
                        if (count($currentSlide) >= 3) { $slides[] = implode("\n", $currentSlide); $currentSlide = []; }
                    } else { $currentLine = empty($currentLine) ? $word : $currentLine . ' ' . $word; }
                }
                if (!empty($currentLine)) {
                    $currentSlide[] = trim($currentLine);
                    if (count($currentSlide) >= 3) { $slides[] = implode("\n", $currentSlide); $currentSlide = []; }
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
    
    // 1. SISIPAN OTOMATIS WARTA SINODE
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
            'images' => $slideImages
        ];
    }
    
    // 2. COVER (LOGO & TEMA)
    $allSlides[] = [
        'type' => 'cover',
        'title' => str_replace(' (CUSTOM)', '', strtoupper($schedule->liturgy->name ?? 'IBADAH')),
        'date' => \Carbon\Carbon::parse($schedule->worship_date)->translatedFormat('l, d F Y'),
        'theme' => strtoupper($schedule->theme ?? ''),
        'preacher' => strtoupper($schedule->preacher_name ?? '')
    ];

    // 3. TATA IBADAH
    foreach($liturgyItems as $item) {
        $detail = $scheduleDetails->get($item->id);
        $content = $detail ? $detail->dynamic_content : $item->static_content;
        $isEmptyArray = is_array($content) && empty($content['judul']) && empty($content['bait']) && empty($content['content']);
        $isEmptyString = !is_array($content) && trim($content) === '';
        $isInstruksi = str_contains(strtolower($item->title), 'sikap') || str_contains(strtolower($item->title), 'aksi');
        
        // Eksekusi fungsi pembersih utama pada judul item liturgi
        $cleanBaseTitle = cleanSlideTitle($item->title);

        if($content && !$isEmptyArray && !$isEmptyString) {
            if($isInstruksi) {
                $textInstruksi = is_array($content) ? ($content['content'] ?? $content[0] ?? '') : $content;
                $allSlides[] = ['type' => 'instruksi', 'title' => $cleanBaseTitle, 'content' => $textInstruksi];
            } elseif(is_array($content)) {
                
                // Cek isi jika custom_title disematkan di array
                if(isset($content['custom_title'])) {
                    $cTitle = cleanSlideTitle($content['custom_title']);
                    $slidesText = autoSplitText($content['content'] ?? '');
                    foreach($slidesText as $st) {
                        $allSlides[] = ['type' => 'text', 'title' => $cTitle, 'content' => $st];
                    }
                } elseif(!empty($content['bait'])) {
                    
                    $songCoverTitle = $cleanBaseTitle;
                    $songCoverContent = $content['judul'] ?? '';
                    
                    // Filter judul cover lagu yang duplikat
                    if (!empty($songCoverContent) && stripos($songCoverContent, $songCoverTitle) !== false) {
                        $songCoverTitle = ''; 
                    }

                    $allSlides[] = [
                        'type' => 'song_cover', 
                        'title' => $songCoverTitle,
                        'content' => $songCoverContent
                    ];
                    
                    foreach($content['bait'] as $key => $bait) {
                        $baitTextRaw = trim($bait);
                        if(empty($baitTextRaw)) continue;

                        $isReff = false;
                        if ((is_string($key) && stripos($key, 'ref') !== false) || preg_match('/^\[?reff?\]?[\s\:\.\-]?/i', $baitTextRaw)) {
                            $isReff = true;
                        }
                        
                        $cleanBaitText = preg_replace('/^\[?REFF\]?\s*/i', '', $baitTextRaw);
                        
                        $displayIndex = '';
                        if (!$isReff) {
                            if (preg_match('/\d+/', $key, $matches)) {
                                $displayIndex = $matches[0];
                            } else {
                                $displayIndex = $key; 
                            }
                        }
                        
                        $laguTitle = $content['judul'] ?? '';
                        if (!empty($laguTitle)) {
                            // Cek jika laguTitle sudah mengandung cleanBaseTitle (mencegah duplikasi kata)
                            if ($cleanBaseTitle !== '' && stripos($laguTitle, $cleanBaseTitle) !== false) {
                                $slideTitle = $laguTitle;
                            } else {
                                $slideTitle = ($cleanBaseTitle !== '' ? $cleanBaseTitle . ' - ' : '') . $laguTitle;
                            }
                        } else {
                            $slideTitle = $cleanBaseTitle;
                        }
                        
                        if($isReff) $slideTitle .= ' (Reff)';

                        $baitSlides = autoSplitText($cleanBaitText);
                        foreach($baitSlides as $bSlide) {
                            $allSlides[] = [
                                'type' => 'song_lyric',
                                'watermark' => $displayIndex,
                                'title' => $slideTitle,
                                'content' => $bSlide
                            ];
                        }
                    }
                }
            } else {
                $slidesText = autoSplitText($content);
                foreach($slidesText as $st) {
                    $allSlides[] = ['type' => 'text', 'title' => $cleanBaseTitle, 'content' => $st];
                }
            }
        }
        
        // Loop ini untuk Custom Slides murni yang ditambahkan jemaat/admin
        if(isset($customSlides[$item->id])) {
            foreach($customSlides[$item->id] as $cSlide) {
                // Eksekusi fungsi pembersih pada custom slide title
                $cTitle = cleanSlideTitle($cSlide->title);

                $cSlidesText = autoSplitText($cSlide->content);
                foreach($cSlidesText as $ct) {
                    $allSlides[] = ['type' => 'custom', 'title' => $cTitle, 'content' => $ct];
                }
            }
        }
    }
    $allSlides[] = ['type' => 'closing', 'title' => 'PENUTUP', 'content' => "TUHAN YESUS\nMEMBERKATI"];
    @endphp

    <div id="presentation">
        @foreach($allSlides as $index => $slide)
            <div class="slide" id="slide-{{ $index }}" style="{{ $slide['type'] === 'announcements_slideshow' ? 'padding: 0;' : '' }}">
                @if($slide['type'] === 'cover')
                    <div class="welcome-wrapper">
                        <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" alt="Logo GPI Papua" style="height: 18vh; margin: 0 auto 3vh auto; filter: drop-shadow(0px 4px 10px rgba(0,0,0,0.5));">
                        <div class="welcome-title">{{ $slide['title'] }}</div>
                        <div class="welcome-sub" style="color: {{ $schedule->theme_color == '#ffffff' ? '#ecc94b' : $schedule->theme_color }};">
                            {{ $slide['date'] }}
                        </div>
                        @if($slide['theme']) <div class="welcome-sub mt-4" style="margin-top: 20px;">TEMA: {{ $slide['theme'] }}</div> @endif
                        @if($slide['preacher']) <div class="welcome-sub mt-2">PELAYAN FIRMAN: {{ $slide['preacher'] }}</div> @endif
                    </div>
                
                @elseif($slide['type'] === 'announcements_slideshow')
                    <div style="position: absolute; top:0; left:0; width:100%; height:100%; background:#000; z-index:0;">
                        @foreach($slide['images'] as $idx => $img)
                            <div class="warta-item warta-item-{{ $index }}" data-duration="{{ $img['duration'] }}" style="position:absolute; top:0; left:0; width:100%; height:100%; opacity: {{ $idx==0 ? 1 : 0 }}; transition: opacity 1.5s ease-in-out; display:flex; flex-direction:column; justify-content:flex-end; align-items:center;">
                                <img src="{{ $img['image_url'] }}" style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:contain; z-index:1;">
                                @if($img['caption'])
                                    <div style="position:relative; z-index:2; margin-bottom: 5vh; background:rgba(0,0,0,0.8); color:#fff; padding:1vh 3vw; border-radius:50px; font-size:2vw; font-weight:bold; text-align:center;">{{ $img['caption'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                @elseif($slide['type'] === 'instruksi')
                    <div class="instruksi-jemaat">{{ $slide['content'] }}</div>
                
                @elseif($slide['type'] === 'song_cover')
                    <div style="margin: auto; text-align: center; position:relative; z-index:2;">
                        @if(!empty($slide['title']))
                            <div class="welcome-sub" style="font-size: 2.5vw; margin-bottom: 15px; text-transform: uppercase; color: #cbd5e0; letter-spacing: 2px;">{{ $slide['title'] }}</div>
                        @endif
                        <div class="welcome-title" style="font-size: 5.5vw;">{{ $slide['content'] }}</div>
                    </div>
                
                @elseif($slide['type'] === 'song_lyric')
                    @if($slide['watermark'] !== '')
                        <div class="bait-watermark">{{ $slide['watermark'] }}</div>
                    @endif
                    
                    @if(!empty($slide['title']))
                        <div class="judul-sesi">{{ $slide['title'] }}</div>
                    @endif
                    <div class="isi-teks">{!! nl2br(e(is_string($slide['content']) ? trim($slide['content']) : '')) !!}</div>
                
                @elseif($slide['type'] === 'closing')
                    <div class="welcome-title text-center" style="margin: auto;">{!! nl2br(e($slide['content'])) !!}</div>
                
                @else
                    @if(!empty($slide['title']))
                        <div class="judul-sesi {{ $slide['type'] === 'custom' ? 'judul-custom' : '' }}">{{ $slide['title'] }}</div>
                    @endif
                    <div class="isi-teks">{!! nl2br(e(is_string($slide['content']) ? trim($slide['content']) : '')) !!}</div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="nav-hint">Navigasi: Panah Kiri/Kanan, Atas/Bawah | F: Layar Penuh</div>

    <script>
        const scheduleId = {{ $schedule->id ?? 0 }};
        let wartaInterval = null; 

        function launchFullscreen() {
            if (!document.fullscreenElement) {
                if (document.documentElement.requestFullscreen) document.documentElement.requestFullscreen();
                else if (document.documentElement.webkitRequestFullscreen) document.documentElement.webkitRequestFullscreen();
            }
            const overlay = document.getElementById('start-overlay');
            if (overlay) overlay.style.display = 'none';
        }

        function adjustAdaptiveText(slide, slideIndex) {
            const textEl = slide.querySelector('.isi-teks');
            const instruksiEl = slide.querySelector('.instruksi-jemaat');
            let customFonts = JSON.parse(localStorage.getItem('custom_fonts_' + scheduleId)) || {};
            let customSize = customFonts[slideIndex]; 

            if (textEl) {
                if(customSize) {
                    textEl.style.fontSize = customSize + 'vw';
                } else {
                    const htmlContent = textEl.innerHTML.toLowerCase();
                    const lines = (htmlContent.match(/<br\s*\/?>/g) || []).length + 1;
                    let size = '5vw'; 
                    if (lines === 1) { size = textEl.innerText.trim().length < 25 ? '7.5vw' : '6.5vw'; } 
                    else if (lines === 2) { size = '5.8vw'; }
                    textEl.style.fontSize = size;
                }
            }

            if (instruksiEl) {
                if(customSize) {
                    instruksiEl.style.fontSize = customSize + 'vw';
                } else {
                    const length = instruksiEl.innerText.trim().length;
                    instruksiEl.style.fontSize = length < 25 ? '7vw' : '5vw';
                }
            }
        }

        function applyLiveDesign(settings) {
            if(!settings) return;
            if(settings.fontFamily) document.body.style.fontFamily = settings.fontFamily;

            const bgCenter = settings.bgCenterColor || '#1b2735';
            const bgEdge = settings.bgEdgeColor || '#050505';
            const bgType = settings.bgType || 'gradient';
            const speed = settings.animSpeed || 15;
            const ac1 = settings.animColor1 || '#2b6cb0';
            const ac2 = settings.animColor2 || '#2c5282';
            
            let videoEl = document.getElementById('bg-video-element');
            if (videoEl) videoEl.remove();

            document.body.style.background = '';
            document.body.style.backgroundColor = '';
            document.body.style.backgroundImage = '';
            document.body.style.backgroundSize = '';
            document.body.style.animation = '';

            if (bgType === 'gradient') { document.body.style.background = `radial-gradient(circle at center, ${bgCenter} 0%, ${bgEdge} 100%)`; } 
            else if (bgType === 'anim-linear') { document.body.style.background = `linear-gradient(-45deg, ${bgCenter}, ${bgEdge}, ${ac1}, ${ac2})`; document.body.style.backgroundSize = '400% 400%'; document.body.style.animation = `gradientBG ${speed}s ease infinite`; } 
            else if (bgType === 'anim-radial') { document.body.style.background = `radial-gradient(circle, ${bgCenter}, ${ac1}, ${bgEdge}, ${ac2})`; document.body.style.backgroundSize = '400% 400%'; document.body.style.animation = `gradientBG ${speed}s ease infinite`; } 
            else if (bgType === 'anim-sweep') { document.body.style.background = `linear-gradient(90deg, ${bgCenter}, ${ac1}, ${bgEdge}, ${ac2}, ${bgCenter})`; document.body.style.backgroundSize = '400% 100%'; document.body.style.animation = `gradientBG ${speed}s linear infinite`; }
            else if (bgType === 'pattern-grid') { document.body.style.backgroundColor = bgEdge; document.body.style.backgroundImage = `linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px)`; document.body.style.backgroundSize = '4vw 4vw'; }
            else if (bgType === 'pattern-grid-anim') { document.body.style.backgroundColor = bgEdge; document.body.style.backgroundImage = `linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px)`; document.body.style.backgroundSize = '4vw 4vw'; document.body.style.animation = `moveGrid ${speed}s linear infinite`; } 
            else if (bgType === 'pattern-dots') { document.body.style.backgroundColor = bgEdge; document.body.style.backgroundImage = `radial-gradient(${bgCenter} 3px, transparent 3px)`; document.body.style.backgroundSize = '4vw 4vw'; } 
            else if (bgType === 'pattern-stripes') { document.body.style.backgroundColor = bgEdge; document.body.style.backgroundImage = `repeating-linear-gradient(45deg, ${bgCenter} 0, ${bgCenter} 2px, transparent 2px, transparent 50%)`; document.body.style.backgroundSize = '4vw 4vw'; }
            else if (bgType === 'pattern-stripes-anim') { document.body.style.backgroundColor = bgEdge; document.body.style.backgroundImage = `repeating-linear-gradient(45deg, ${bgCenter} 0, ${bgCenter} 2px, transparent 2px, transparent 50%)`; document.body.style.backgroundSize = '4vw 4vw'; document.body.style.animation = `moveStripes ${speed}s linear infinite`; }
            else if (bgType === 'video' && settings.bgVideoUrl) {
                document.body.style.backgroundColor = '#000';
                const vid = document.createElement('video');
                vid.id = 'bg-video-element'; vid.src = settings.bgVideoUrl;
                vid.autoplay = true; vid.loop = true; vid.muted = true;
                vid.style.position = 'fixed'; vid.style.top = '0'; vid.style.left = '0'; vid.style.width = '100vw'; vid.style.height = '100vh'; vid.style.objectFit = 'cover'; vid.style.zIndex = '-1'; vid.style.opacity = '0.6';
                document.body.insertBefore(vid, document.body.firstChild);
            }

            const shadowIntensity = settings.textShadow || '0.9';
            const sColor = settings.shadowColor || '#000000';
            let r = 0, g = 0, b = 0;
            if (sColor.length === 7) { r = parseInt(sColor.substring(1,3), 16); g = parseInt(sColor.substring(3,5), 16); b = parseInt(sColor.substring(5,7), 16); }

            document.querySelectorAll('.isi-teks, .instruksi-jemaat').forEach(el => {
                el.style.color = settings.textColor; el.style.textShadow = `0px 4px 15px rgba(${r}, ${g}, ${b}, ${shadowIntensity})`;
            });
            document.querySelectorAll('.welcome-title').forEach(el => {
                el.style.color = settings.textColor; el.style.textShadow = `0px 5px 20px rgba(${r}, ${g}, ${b}, ${shadowIntensity})`;
            });
        }
        
        const savedDesign = JSON.parse(localStorage.getItem('live_design_settings'));
        if(savedDesign) applyLiveDesign(savedDesign);

        const slides = document.querySelectorAll('.slide');
        let currentSlide = parseInt(localStorage.getItem('last_slide_index')) || 0;

        function showSlide(index) {
            if (index >= slides.length) index = slides.length - 1;
            if (index < 0) index = 0;
            
            clearTimeout(wartaInterval);

            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (i === index) {
                    slide.classList.add('active');
                    adjustAdaptiveText(slide, i); 
                }
            });
            currentSlide = index;

            let currentSlideEl = slides[index];
            let wartaItems = currentSlideEl.querySelectorAll('.warta-item-' + index);
            if(wartaItems.length > 1) {
                let wIndex = 0;
                
                const runNextWarta = () => {
                    wartaItems[wIndex].style.opacity = 0;
                    wIndex = (wIndex + 1) % wartaItems.length;
                    wartaItems[wIndex].style.opacity = 1;
                    let nextDuration = (parseInt(wartaItems[wIndex].dataset.duration) || 5) * 1000;
                    wartaInterval = setTimeout(runNextWarta, nextDuration);
                };

                let firstDuration = (parseInt(wartaItems[0].dataset.duration) || 5) * 1000;
                wartaInterval = setTimeout(runNextWarta, firstDuration);
            }
            
            localStorage.setItem('last_slide_index', index);
        }

        showSlide(currentSlide);

        document.addEventListener('keydown', (e) => {
            if (['ArrowRight', 'ArrowUp', ' ', 'PageDown', 'Enter'].includes(e.key)) showSlide(currentSlide + 1);
            else if (['ArrowLeft', 'ArrowDown', 'PageUp'].includes(e.key)) showSlide(currentSlide - 1);
            else if (e.key.toLowerCase() === 'f') {
                const overlay = document.getElementById('start-overlay');
                if (overlay) overlay.style.display = 'none';
                if (!document.fullscreenElement) document.documentElement.requestFullscreen();
                else document.exitFullscreen();
            }
        });

        window.addEventListener('storage', (e) => {
            if (e.key === 'projector_command') {
                const cmd = JSON.parse(e.newValue);
                if (cmd.action === 'next') showSlide(currentSlide + 1);
                if (cmd.action === 'prev') showSlide(currentSlide - 1);
                if (cmd.action === 'jump') showSlide(cmd.index);
            }
            if (e.key === 'font_sync_trigger') showSlide(currentSlide); 
            if (e.key === 'liturgy_update') location.reload();
            if (e.key === 'live_design_settings') applyLiveDesign(JSON.parse(e.newValue));
        });
    </script>
</body>
</html>