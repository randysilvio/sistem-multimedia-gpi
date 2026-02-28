<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Export PDF Slide Ibadah - GPI Papua</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        
        body, html { 
            margin: 0; padding: 0; width: 100%; 
            background: #ffffff;
            color: #1a202c; 
            font-family: 'Inter', Tahoma, sans-serif; 
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact;
        }
        
        .slide { 
            width: 100vw; height: 100vh; 
            display: flex; flex-direction: column; justify-content: flex-start; align-items: center; 
            text-align: center; padding: 6vh 5vw; box-sizing: border-box; 
            page-break-after: always; 
            position: relative; border-bottom: 1px solid #e2e8f0;
        }
        
        .instruksi-jemaat { margin: auto; font-size: 40px; color: #4a5568; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; }
        
        .judul-sesi { flex-shrink: 0; font-size: 24px; color: #718096; text-transform: uppercase; letter-spacing: 3px; font-weight: 600; margin-top: 2vh; margin-bottom: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; width: 85%; }
        .judul-custom { color: #3182ce; border-bottom-color: #bee3f8; }
        
        .isi-teks { margin-top: auto; margin-bottom: auto; font-size: 42px; font-weight: 700; line-height: 1.5; max-width: 90%; }
        .teks-kuning { color: #d69e2e; }
        
        .welcome-wrapper { margin: auto; }
        .welcome-title { font-size: 60px; font-weight: 900; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 3px; }
        .welcome-sub { font-size: 26px; color: #4a5568; font-weight: 400; letter-spacing: 1px; }

        .btn-print {
            position: fixed; top: 20px; right: 20px; z-index: 1000;
            background: #2b6cb0; color: white; padding: 10px 20px;
            border: none; border-radius: 5px; font-size: 16px; cursor: pointer; font-weight: 600;
        }
        @media print { .btn-print { display: none; } .slide { border-bottom: none; } }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">🖨️ Cetak PDF / Simpan</button>
    
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

    <div id="presentation-print">
        <div class="slide">
            <div class="welcome-wrapper">
                <img src="https://gpipapua.org/storage/logos/gKF2JZ5RvUZrE57otn9yjHep9ArI9dhVmtGYX3gq.png" alt="Logo GPI Papua" style="height: 150px; margin: 0 auto 30px auto;">
                
                <div class="welcome-title">{{ strtoupper($schedule->liturgy->name) }}</div>
                <div class="welcome-sub" style="color: {{ $schedule->theme_color == '#ffffff' ? '#e2c050' : $schedule->theme_color }};">
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
                                <div class="isi-teks">{!! nl2br(e(trim($slideTeks))) !!}</div>
                            </div>
                        @endforeach
                    
                    @elseif(!empty($content['bait']))
                        
                        <div class="slide">
                            <div style="margin: auto; text-align: center;">
                                <div class="welcome-sub" style="font-size: 30px; margin-bottom: 15px; text-transform: uppercase;">
                                    {{ str_replace(' (Opsional)', '', $item->title) }}
                                </div>
                                @if(!empty($content['judul']))
                                    <div class="welcome-title" style="font-size: 65px;">
                                        {{ $content['judul'] }}
                                    </div>
                                @endif
                                <div class="isi-teks teks-kuning" style="font-size: 30px; margin-top: 25px;">
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
                                    <div class="isi-teks">{!! nl2br(e(trim($bSlide))) !!}</div>
                                </div>
                            @endforeach
                        @endforeach
                    
                    @endif
                
                @else
                    @php $slidesText = autoSplitText($content); @endphp
                    @foreach($slidesText as $slideTeks)
                        <div class="slide">
                            <div class="judul-sesi">{{ str_replace(' (Opsional)', '', $item->title) }}</div>
                            <div class="isi-teks">{!! nl2br(e(trim($slideTeks))) !!}</div>
                        </div>
                    @endforeach
                @endif
            @endif
        @endforeach

        <div class="slide">
            <div class="welcome-title text-center" style="margin: auto;">TUHAN YESUS<br><span class="teks-kuning">MEMBERKATI</span></div>
        </div>
    </div>
</body>
</html>