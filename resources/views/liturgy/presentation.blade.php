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
            color: #ffffff; font-family: 'Inter', Tahoma, sans-serif; overflow: hidden; background: #000;
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
        
        .slide { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; text-align: center; padding: 6vh 4vw; box-sizing: border-box; opacity: 0; z-index: 0; pointer-events: none; }
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
    @endphp

    <div id="presentation">
        <div class="slide active">
            <div class="welcome-wrapper">
                <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" alt="Logo GPI Papua" style="height: 18vh; margin: 0 auto 3vh auto; filter: drop-shadow(0px 4px 10px rgba(0,0,0,0.5));">
                <div class="welcome-title">{{ strtoupper($schedule->liturgy->name) }}</div>
                <div class="welcome-sub" style="color: {{ $schedule->theme_color == '#ffffff' ? '#ecc94b' : $schedule->theme_color }};">
                    {{ \Carbon\Carbon::parse($schedule->worship_date)->translatedFormat('l, d F Y') }}
                </div>
                @if($schedule->theme) <div class="welcome-sub mt-4" style="margin-top: 20px;">TEMA: {{ strtoupper($schedule->theme) }}</div> @endif
                @if($schedule->preacher_name) <div class="welcome-sub mt-2">PELAYAN FIRMAN: {{ strtoupper($schedule->preacher_name) }}</div> @endif
            </div>
        </div>

        @foreach($liturgyItems as $item)
            @php
                $detail = $scheduleDetails->get($item->id);
                $content = $detail ? $detail->dynamic_content : $item->static_content;
                $isEmptyArray = is_array($content) && empty($content['judul']) && empty($content['bait']) && empty($content['content']);
                $isEmptyString = !is_array($content) && trim($content) === '';
                $isInstruksi = str_contains(strtolower($item->title), 'sikap') || str_contains(strtolower($item->title), 'aksi');
            @endphp

            @if($content && !$isEmptyArray && !$isEmptyString)
                @if($isInstruksi)
                    @php $textInstruksi = is_array($content) ? ($content['content'] ?? $content[0] ?? '') : $content; @endphp
                    <div class="slide"><div class="instruksi-jemaat">{{ $textInstruksi }}</div></div>
                @elseif(is_array($content))
                    @if(isset($content['custom_title']))
                        @php $slidesText = autoSplitText($content['content'] ?? ''); @endphp
                        @foreach($slidesText as $slideTeks)
                            <div class="slide"><div class="judul-sesi">{{ $content['custom_title'] }}</div><div class="isi-teks">{!! nl2br(e(is_string($slideTeks) ? trim($slideTeks) : '')) !!}</div></div>
                        @endforeach
                    @elseif(!empty($content['bait']))
                        <div class="slide">
                            <div style="margin: auto; text-align: center; position:relative; z-index:2;">
                                <div class="welcome-sub" style="font-size: 2.5vw; margin-bottom: 15px; text-transform: uppercase; color: #cbd5e0; letter-spacing: 2px;">{{ str_replace(' (Opsional)', '', $item->title) }}</div>
                                @if(!empty($content['judul']))<div class="welcome-title" style="font-size: 5.5vw;">{{ $content['judul'] }}</div>@endif
                            </div>
                        </div>
                        
                        @php $verseCounter = 1; @endphp
                        @foreach($content['bait'] as $key => $bait)
                            @php 
                                $baitTextRaw = trim($bait);
                                if(empty($baitTextRaw)) continue;

                                // Deteksi Reff (dari key atau dari teks)
                                $isReff = false;
                                if ((is_string($key) && stripos($key, 'ref') !== false) || preg_match('/^\[?reff?\]?[\s\:\.\-]?/i', $baitTextRaw)) {
                                    $isReff = true;
                                }

                                // Bersihkan tag [REFF] agar lirik bersih
                                $cleanBaitText = preg_replace('/^\[?REFF\]?\s*/i', '', $baitTextRaw);

                                $displayIndex = $isReff ? '' : $verseCounter; 
                                if (!$isReff) $verseCounter++; // Penomoran Bait tidak naik jika itu Reff
                                
                                $slideTitle = str_replace(' (Opsional)', '', $item->title) . (!empty($content['judul']) ? ' - ' . $content['judul'] : '');
                                if($isReff) $slideTitle .= ' (Reff)';

                                $baitSlides = autoSplitText($cleanBaitText);
                            @endphp

                            @foreach($baitSlides as $bSlide)
                                <div class="slide">
                                    @if($displayIndex !== '')
                                        <div class="bait-watermark">{{ $displayIndex }}</div>
                                    @endif
                                    <div class="judul-sesi">{{ $slideTitle }}</div>
                                    <div class="isi-teks">{!! nl2br(e(is_string($bSlide) ? trim($bSlide) : '')) !!}</div>
                                </div>
                            @endforeach
                        @endforeach
                    @endif
                @else
                    @php $slidesText = autoSplitText($content); @endphp
                    @foreach($slidesText as $slideTeks)
                        <div class="slide"><div class="judul-sesi">{{ str_replace(' (Opsional)', '', $item->title) }}</div><div class="isi-teks">{!! nl2br(e(is_string($slideTeks) ? trim($slideTeks) : '')) !!}</div></div>
                    @endforeach
                @endif
            @endif

            @if(isset($customSlides[$item->id]))
                @foreach($customSlides[$item->id] as $cSlide)
                    @php $cSlidesText = autoSplitText($cSlide->content); @endphp
                    @foreach($cSlidesText as $cText)
                        <div class="slide"><div class="judul-sesi judul-custom">{{ $cSlide->title }}</div><div class="isi-teks">{!! nl2br(e(is_string($cText) ? trim($cText) : '')) !!}</div></div>
                    @endforeach
                @endforeach
            @endif
        @endforeach

        <div class="slide"><div class="welcome-title text-center" style="margin: auto;">TUHAN YESUS<br><span class="teks-kuning">MEMBERKATI</span></div></div>
    </div>

    <div class="nav-hint">Navigasi: Panah Kiri/Kanan, Atas/Bawah | F: Layar Penuh</div>

    <script>
        const scheduleId = {{ $schedule->id ?? 0 }};

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

        // =======================================================
        // ENGINE PENGUBAH BACKGROUND DAN DESAIN
        // =======================================================
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

            // Terapkan Latar Sesuai Tipe
            if (bgType === 'gradient') {
                document.body.style.background = `radial-gradient(circle at center, ${bgCenter} 0%, ${bgEdge} 100%)`;
            } 
            else if (bgType === 'anim-linear') {
                document.body.style.background = `linear-gradient(-45deg, ${bgCenter}, ${bgEdge}, ${ac1}, ${ac2})`;
                document.body.style.backgroundSize = '400% 400%';
                document.body.style.animation = `gradientBG ${speed}s ease infinite`;
            } 
            else if (bgType === 'anim-radial') {
                document.body.style.background = `radial-gradient(circle, ${bgCenter}, ${ac1}, ${bgEdge}, ${ac2})`;
                document.body.style.backgroundSize = '400% 400%';
                document.body.style.animation = `gradientBG ${speed}s ease infinite`;
            } 
            else if (bgType === 'anim-sweep') {
                document.body.style.background = `linear-gradient(90deg, ${bgCenter}, ${ac1}, ${bgEdge}, ${ac2}, ${bgCenter})`;
                document.body.style.backgroundSize = '400% 100%';
                document.body.style.animation = `gradientBG ${speed}s linear infinite`;
            }
            else if (bgType === 'pattern-grid') {
                document.body.style.backgroundColor = bgEdge;
                document.body.style.backgroundImage = `linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px)`;
                document.body.style.backgroundSize = '4vw 4vw';
            }
            else if (bgType === 'pattern-grid-anim') {
                document.body.style.backgroundColor = bgEdge;
                document.body.style.backgroundImage = `linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px)`;
                document.body.style.backgroundSize = '4vw 4vw';
                document.body.style.animation = `moveGrid ${speed}s linear infinite`;
            } 
            else if (bgType === 'pattern-dots') {
                document.body.style.backgroundColor = bgEdge;
                document.body.style.backgroundImage = `radial-gradient(${bgCenter} 3px, transparent 3px)`;
                document.body.style.backgroundSize = '4vw 4vw';
            } 
            else if (bgType === 'pattern-stripes') {
                document.body.style.backgroundColor = bgEdge;
                document.body.style.backgroundImage = `repeating-linear-gradient(45deg, ${bgCenter} 0, ${bgCenter} 2px, transparent 2px, transparent 50%)`;
                document.body.style.backgroundSize = '4vw 4vw';
            }
            else if (bgType === 'pattern-stripes-anim') {
                document.body.style.backgroundColor = bgEdge;
                document.body.style.backgroundImage = `repeating-linear-gradient(45deg, ${bgCenter} 0, ${bgCenter} 2px, transparent 2px, transparent 50%)`;
                document.body.style.backgroundSize = '4vw 4vw';
                document.body.style.animation = `moveStripes ${speed}s linear infinite`;
            }
            else if (bgType === 'video' && settings.bgVideoUrl) {
                document.body.style.backgroundColor = '#000';
                const vid = document.createElement('video');
                vid.id = 'bg-video-element';
                vid.src = settings.bgVideoUrl;
                vid.autoplay = true; vid.loop = true; vid.muted = true;
                vid.style.position = 'fixed'; vid.style.top = '0'; vid.style.left = '0'; vid.style.width = '100vw'; vid.style.height = '100vh'; vid.style.objectFit = 'cover'; vid.style.zIndex = '-1'; vid.style.opacity = '0.6';
                document.body.insertBefore(vid, document.body.firstChild);
            }

            // Settingan Shadow Text
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
            
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (i === index) {
                    slide.classList.add('active');
                    adjustAdaptiveText(slide, i); 
                }
            });
            currentSlide = index;
            
            localStorage.setItem('last_slide_index', index);
            localStorage.setItem('total_slides', slides.length);
            localStorage.setItem('last_slide_text', slides[index].innerText.substring(0, 150));
            localStorage.setItem('next_slide_text', slides[index+1] ? slides[index+1].innerText.substring(0, 80) : 'Selesai');
            const allContent = Array.from(slides).map(s => s.innerText.trim());
            localStorage.setItem('all_slides_content', JSON.stringify(allContent));
            localStorage.setItem('slide_changed', Date.now());
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