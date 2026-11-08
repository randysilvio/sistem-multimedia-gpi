<?php
// FUNGSI PEMBERSIH JUDUL (Anti Dobel & Hapus Awalan)
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

// Pemotong teks panjang menjadi beberapa slide
if (!function_exists('smartSplitText')) {
    function smartSplitText($text) {
        if (is_array($text)) { $text = $text['content'] ?? ''; }
        if (!is_string($text)) $text = '';
        
        // 1. Jika user pakai tag pemisah manual
        if (str_contains($text, '===SLIDE_BREAK===')) {
            return array_filter(array_map('trim', explode('===SLIDE_BREAK===', $text)));
        }
        
        // 2. Pertahankan Enter bawaan dari user
        $text = preg_replace("/[\r\n]+/", " \n ", trim($text));
        $words = explode(' ', $text);
        
        $slides = [];
        $currentSlideLines = [];
        $currentLine = '';
        
        $maxCharsPerLine = 24; // Ideal 23-25 karakter
        $tolerance = 8; // Toleransi sisa huruf (mencegah kata "Amin." terbuang ke slide baru)

        $totalWords = count($words);
        for ($i = 0; $i < $totalWords; $i++) {
            $word = trim($words[$i]);
            if ($word === '') continue;

            $remainingText = trim(implode(' ', array_slice($words, $i)));
            $remainingLength = strlen($remainingText);

            // Jika kata ini adalah Enter bawaan user
            if ($word === "\n") {
                if (!empty($currentLine)) {
                    $currentSlideLines[] = trim($currentLine);
                    $currentLine = '';
                }
                continue;
            }

            // Jika kata ini membuat baris kepenuhan
            if (strlen($currentLine) + strlen($word) + 1 > $maxCharsPerLine && !empty($currentLine)) {
                
                // ORPHAN CONTROL: Jika sisa huruf tinggal sedikit, paksakan masuk baris ini
                if ($remainingLength <= $tolerance) {
                    $currentLine .= ' ' . $word;
                    continue; 
                }

                // Tutup baris saat ini
                $currentSlideLines[] = trim($currentLine);
                $currentLine = $word;
                
                $lastWordPushed = $currentSlideLines[count($currentSlideLines)-1];
                $endsInPunctuation = preg_match('/[.?!,;:]$/', $lastWordPushed);

                // Potong slide setelah 3 baris, atau 2 baris yang berakhir dengan tanda baca
                if (count($currentSlideLines) >= 3 || (count($currentSlideLines) >= 2 && $endsInPunctuation)) {
                    $slides[] = implode("\n", $currentSlideLines);
                    $currentSlideLines = [];
                }
            } else {
                $currentLine = empty($currentLine) ? $word : $currentLine . ' ' . $word;
            }
        }

        if (!empty($currentLine)) {
            $currentSlideLines[] = trim($currentLine);
        }
        if (!empty($currentSlideLines)) {
            $slides[] = implode("\n", $currentSlideLines);
        }
        
        return empty($slides) ? [$text] : $slides;
    }
}
?>