<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layar Proyektor - GPI Papua</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap');
        
        body, html { 
            margin: 0; padding: 0; width: 100%; height: 100%; 
            background: radial-gradient(circle at center, {{ $schedule->theme_color ?? '#1b2735' }} 0%, #050505 100%);
            color: #ffffff; 
            font-family: 'Inter', Tahoma, sans-serif; 
            overflow: hidden; 
            transition: background 0.5s ease, color 0.5s ease;
        }

        #start-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 9999; background: rgba(0,0,0,0.95);
            display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer;
            border: 2px solid #2b6cb0;
        }
        .start-title { color: #3182ce; font-size: 3vw; margin-bottom: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; }
        .start-subtitle { color: #a0aec0; font-size: 1.5vw; text-align: center; font-weight: 400; }
        
        .slide { 
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
            display: flex; flex-direction: column; justify-content: flex-start; align-items: center; 
            text-align: center; padding: 7vh 6vw; box-sizing: border-box; 
            opacity: 0; transition: opacity 0.4s ease-in-out; 
            z-index: 0; pointer-events: none; 
        }
        .slide.active { opacity: 1; z-index: 10; pointer-events: auto; }
        
        .instruksi-jemaat { margin: auto; font-size: 5vw; color: inherit; font-weight: 700; text-transform: uppercase; letter-spacing: 4px; text-shadow: 0px 4px 20px rgba(0,0,0,0.8); transition: 0.3s; }

        .judul-sesi { flex-shrink: 0; font-size: 2.2vw; color: rgba(255, 255, 255, 0.7); text-transform: uppercase; letter-spacing: 4px; font-weight: 600; margin-top: 2vh; margin-bottom: 0; border-bottom: 2px solid rgba(255, 255, 255, 0.15); padding-bottom: 12px; width: 85%; }
        .judul-custom { color: #63b3ed; border-bottom-color: rgba(99, 179, 237, 0.3); }
        
        .isi-teks { margin-top: auto; margin-bottom: auto; font-size: 4.6vw; font-weight: 700; line-height: 1.5; text-shadow: 0px 4px 15px rgba(0,0,0,0.9); max-width: 90%; transition: 0.3s; }
        .teks-kuning { color: #ecc94b; }
        
        .welcome-wrapper { margin: auto; }
        .welcome-title { font-size: 6vw; font-weight: 900; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 3px; text-shadow: 0px 5px 20px rgba(0,0,0,0.8); }
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
        function autoSplitText($text, $maxLines = 4, $charsPerLine = 38) {
            if (is_array($text)) { $text = $text['content'] ?? ''; }
            if (!is_string($text)) $text = '';

            if (str_contains($text, '===SLIDE_BREAK===')) return array_filter(array_map('trim', explode('===SLIDE_BREAK===', $text)));
            $slides = []; $currentSlideText = ''; $currentLines = 0;
            $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $text));

            foreach ($lines as $line) {
                $line = trim($line); if (empty($line)) continue;
                $estLinesForLine = max(1, ceil(strlen($line) / $charsPerLine));
                if ($estLinesForLine > 1) {
                    $sentences = preg_split('/(?<=[.?!])\s+/', $line);
                    foreach ($sentences as $sentence) {
                        $sentence = trim($sentence); if (empty($sentence)) continue;
                        $estLines = max(1, ceil(strlen($sentence) / $charsPerLine));
                        if ($estLines > $maxLines) {
                            $words = explode(' ', $sentence); $tempLine = '';
                            foreach ($words as $word) {
                                if (strlen($tempLine . ' ' . $word) > $charsPerLine) {
                                    $currentSlideText .= trim($tempLine) . "\n"; $currentLines++; $tempLine = $word;
                                    if ($currentLines >= $maxLines) { $slides[] = trim($currentSlideText); $currentSlideText = ''; $currentLines = 0; }
                                } else { $tempLine .= (empty($tempLine) ? '' : ' ') . $word; }
                            }
                            if (!empty($tempLine)) { $currentSlideText .= trim($tempLine) . " "; $currentLines++; }
                            continue;
                        }
                        if ($currentLines + $estLines > $maxLines && !empty(trim($currentSlideText))) {
                            $slides[] = trim($currentSlideText); $currentSlideText = $sentence . " "; $currentLines = $estLines;
                        } else { $currentSlideText .= $sentence . " "; $currentLines += $estLines; }
                    }
                    $currentSlideText = rtrim($currentSlideText) . "\n";
                } else {
                    if ($currentLines + 1 > $maxLines && !empty(trim($currentSlideText))) {
                        $slides[] = trim($currentSlideText); $currentSlideText = $line . "\n"; $currentLines = 1;
                    } else { $currentSlideText .= $line . "\n"; $currentLines += 1; }
                }
            }
            if (!empty(trim($currentSlideText))) $slides[] = trim($currentSlideText);
            return empty($slides) ? [$text] : $slides;
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
                            <div class="slide">
                                <div class="judul-sesi">{{ $content['custom_title'] }}</div>
                                <div class="isi-teks">{!! nl2br(e(is_string($slideTeks) ? trim($slideTeks) : '')) !!}</div>
                            </div>
                        @endforeach
                    
                    @elseif(!empty($content['bait']))
                        
                        <div class="slide">
                            <div style="margin: auto; text-align: center;">
                                <div class="welcome-sub" style="font-size: 2.5vw; margin-bottom: 15px; text-transform: uppercase; color: #cbd5e0; letter-spacing: 2px;">
                                    {{ str_replace(' (Opsional)', '', $item->title) }}
                                </div>
                                @if(!empty($content['judul']))
                                    <div class="welcome-title" style="font-size: 5.5vw;">
                                        {{ $content['judul'] }}
                                    </div>
                                @endif
                                <div class="isi-teks teks-kuning" style="font-size: 2.2vw; margin-top: 25px; font-weight: 600;">
                                    Menyanyikan Bait: {{ implode(', ', range(1, count($content['bait']))) }}
                                </div>
                            </div>
                        </div>

                        @foreach($content['bait'] as $index => $bait)
                            @php $baitSlides = autoSplitText($bait); @endphp
                            @foreach($baitSlides as $bSlide)
                                <div class="slide">
                                    <div class="judul-sesi">
                                        {{ str_replace(' (Opsional)', '', $item->title) }} 
                                        @if(!empty($content['judul'])) - {{ $content['judul'] }} @endif 
                                        <span class="teks-kuning"> - Bait {{ $index + 1 }}</span>
                                    </div>
                                    <div class="isi-teks">{!! nl2br(e(is_string($bSlide) ? trim($bSlide) : '')) !!}</div>
                                </div>
                            @endforeach
                        @endforeach
                    
                    @endif
                
                @else
                    @php $slidesText = autoSplitText($content); @endphp
                    @foreach($slidesText as $slideTeks)
                        <div class="slide">
                            <div class="judul-sesi">{{ str_replace(' (Opsional)', '', $item->title) }}</div>
                            <div class="isi-teks">{!! nl2br(e(is_string($slideTeks) ? trim($slideTeks) : '')) !!}</div>
                        </div>
                    @endforeach
                @endif
            @endif

            @if(isset($customSlides[$item->id]))
                @foreach($customSlides[$item->id] as $cSlide)
                    @php $cSlidesText = autoSplitText($cSlide->content); @endphp
                    @foreach($cSlidesText as $cText)
                        <div class="slide">
                            <div class="judul-sesi judul-custom">{{ $cSlide->title }}</div>
                            <div class="isi-teks">{!! nl2br(e(is_string($cText) ? trim($cText) : '')) !!}</div>
                        </div>
                    @endforeach
                @endforeach
            @endif
        @endforeach

        <div class="slide">
            <div class="welcome-title text-center" style="margin: auto;">TUHAN YESUS<br><span class="teks-kuning">MEMBERKATI</span></div>
        </div>
    </div>

    <div class="nav-hint">Navigasi: Panah Kiri/Kanan, Atas/Bawah | F: Layar Penuh</div>

    <script>
        function launchFullscreen() {
            if (!document.fullscreenElement) {
                if (document.documentElement.requestFullscreen) document.documentElement.requestFullscreen();
                else if (document.documentElement.webkitRequestFullscreen) document.documentElement.webkitRequestFullscreen();
            }
            const overlay = document.getElementById('start-overlay');
            if (overlay) overlay.style.display = 'none';
        }

        function applyLiveDesign(settings) {
            if(!settings) return;

            if(settings.fontFamily) {
                document.body.style.fontFamily = settings.fontFamily;
            }

            const bgCenter = settings.bgCenterColor || settings.bgColor || '#1b2735';
            const bgEdge = settings.bgEdgeColor || '#050505';
            document.body.style.background = `radial-gradient(circle at center, ${bgCenter} 0%, ${bgEdge} 100%)`;

            const sColor = settings.shadowColor || '#000000';
            const sIntensity = settings.textShadow || '0.9';
            let r = 0, g = 0, b = 0;
            if (sColor.length === 7) {
                r = parseInt(sColor.substring(1,3), 16);
                g = parseInt(sColor.substring(3,5), 16);
                b = parseInt(sColor.substring(5,7), 16);
            }
            const shadowValue = `0px 4px 15px rgba(${r}, ${g}, ${b}, ${sIntensity})`;
            const titleShadowValue = `0px 5px 20px rgba(${r}, ${g}, ${b}, ${sIntensity})`;

            document.querySelectorAll('.isi-teks, .instruksi-jemaat').forEach(el => {
                el.style.fontSize = settings.fontSize; 
                el.style.color = settings.textColor;
                el.style.textShadow = shadowValue;
            });
            document.querySelectorAll('.welcome-title').forEach(el => {
                el.style.color = settings.textColor;
                el.style.textShadow = titleShadowValue;
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
                if (i === index) slide.classList.add('active');
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
            if (e.key === 'liturgy_update') location.reload();
            if (e.key === 'live_design_settings') applyLiveDesign(JSON.parse(e.newValue));
        });
    </script>
</body>
</html>