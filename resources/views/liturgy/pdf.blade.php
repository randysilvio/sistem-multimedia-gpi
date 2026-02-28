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
        .judul-sesi { flex-shrink: 0; font-size: 24px; color: #718096; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; margin-top: 20px; margin-bottom: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; width: 85%; }
        .judul-custom { color: #2b6cb0; border-bottom-color: #bee3f8; }
        .isi-teks { margin-top: auto; margin-bottom: auto; font-size: 38px; font-weight: 700; line-height: 1.5; max-width: 90%; color: #2d3748; }
        .teks-kuning { color: #2b6cb0; }
        
        .welcome-wrapper { margin: auto; }
        .welcome-title { font-size: 48px; font-weight: 800; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 2px; color: #1a202c; }
        .welcome-sub { font-size: 24px; color: #718096; font-weight: 400; letter-spacing: 1px; }
    </style>
</head>
<body>
    @php
    if (!function_exists('autoSplitText')) {
        function autoSplitText($text, $maxLines = 4, $charsPerLine = 38) {
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
        <div class="slide">
            <div class="welcome-wrapper">
                <div class="welcome-title">{{ strtoupper($schedule->liturgy->name) }}</div>
                <div class="welcome-sub">{{ \Carbon\Carbon::parse($schedule->worship_date)->translatedFormat('l, d F Y') }}</div>
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
                $isInstruksi = str_contains(strtolower($item->title), 'sikap');
            @endphp

            @if($content && !$isEmptyArray && !$isEmptyString)
                @if($isInstruksi)
                    <div class="slide"><div class="instruksi-jemaat">{{ $content }}</div></div>
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
                        @foreach($content['bait'] as $bait)
                            @php $baitSlides = autoSplitText($bait); @endphp
                            @foreach($baitSlides as $bSlide)
                                <div class="slide">
                                    <div class="judul-sesi">{{ str_replace(' (Opsional)', '', $item->title) }} @if(!empty($content['judul'])) - {{ $content['judul'] }} @endif</div>
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