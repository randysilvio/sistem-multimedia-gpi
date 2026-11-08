<?php

namespace App\Services;

use App\Models\Schedule;
use Carbon\Carbon;

/**
 * Satu-satunya tempat yang mengubah data jadwal menjadi daftar slide.
 * Dipakai oleh control panel, layar live, dan endpoint JSON, sehingga
 * pratinjau operator selalu sama dengan yang tampil di layar.
 *
 * Setiap slide punya `key` yang stabil (tidak bergeser bila slide lain
 * ditambah/dihapus): dipakai untuk menyimpan ukuran huruf manual per slide.
 */
class SlideBuilder
{
    public const MAX_CHARS_PER_LINE = 45;
    public const MAX_LINES_PER_SLIDE = 4;
    /** Perkiraan huruf per slide ayat: muat ±5 baris besar di TV 55 inci */
    public const BIBLE_CHARS_PER_SLIDE = 220;
    public const BREAK_MARKER = '===SLIDE_BREAK===';
    public const CAMERA_MARKER = '[KAMERA_AKTIF]';

    /**
     * @param  iterable  $announcements  Warta sinode aktif (gambar)
     * @return array<int, array>
     */
    public function build(Schedule $schedule, iterable $announcements = []): array
    {
        $schedule->loadMissing(['details', 'customSlides', 'liturgy.items']);

        $details = $schedule->details->keyBy('liturgy_item_id');
        $customSlides = $schedule->customSlides->groupBy('liturgy_item_id');
        $slides = [];

        $images = [];
        foreach ($announcements as $ann) {
            $images[] = [
                'image_url' => asset('storage/' . $ann->image_path),
                'caption' => mb_strtoupper($ann->title ?? ''),
                'duration' => max(1, (int) ($ann->duration ?? 5)),
            ];
        }
        if ($images) {
            $slides[] = $this->slide('warta', 'announcements_slideshow', 'Warta Sinode', ['title' => 'WARTA SINODE', 'images' => $images]);
        }

        $slides[] = $this->slide('cover', 'cover', 'Pembukaan', [
            'title' => str_replace(' (CUSTOM)', '', mb_strtoupper($schedule->liturgy->name ?? 'IBADAH')),
            'date' => Carbon::parse($schedule->worship_date)->locale('id')->translatedFormat('l, d F Y'),
            'theme' => mb_strtoupper($schedule->theme ?? ''),
            'preacher' => mb_strtoupper($schedule->preacher_name ?? ''),
        ]);

        foreach ($schedule->liturgy->items ?? [] as $item) {
            $detail = $details->get($item->id);
            $content = $detail ? $detail->dynamic_content : $item->static_content;
            $base = $this->cleanTitle($item->title);
            $section = $base !== '' ? $base : 'Instruksi';
            $prefix = 'i' . $item->id;
            $camera = is_array($content) && filter_var($content['use_camera'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if (!$this->isEmptyContent($content)) {
                if (self::itemType($item->title) === 'alkitab' && is_array($content) && !array_key_exists('custom_title', $content)) {
                    $this->pushBible($slides, $prefix, $section, $base, $content, $camera);
                } elseif (is_array($content) && array_key_exists('custom_title', $content) && trim((string) $content['custom_title']) === '') {
                    // Judul dikosongkan = slide instruksi jemaat (teks besar di tengah)
                    $instruction = trim((string) ($content['content'] ?? ''));
                    $slides[] = $this->slide("$prefix-0", 'instruksi', $base !== '' ? $section : $this->instructionSection($instruction), [
                        'content' => $instruction,
                        'use_camera' => $camera,
                    ]);
                } elseif (is_array($content) && trim((string) ($content['custom_title'] ?? '')) !== '') {
                    $title = $this->cleanTitle($content['custom_title']);
                    $this->pushText($slides, $prefix, 'text', $title !== '' ? $title : $section, $title, $content['content'] ?? '', $camera);
                } elseif (is_array($content) && !empty($content['bait'])) {
                    $this->pushSong($slides, $prefix, $base, $content, $camera);
                } elseif (is_array($content)) {
                    $this->pushText($slides, $prefix, 'text', $section, $base, $content['content'] ?? '', $camera);
                } else {
                    $this->pushText($slides, $prefix, 'text', $section, $base, $content, false);
                }
            }

            foreach ($customSlides->get($item->id, collect())->values() as $ci => $custom) {
                if ($custom->kind === \App\Models\ScheduleCustomSlide::KIND_SONG) {
                    // Lagu dadakan (disisipkan dari Kontrol Live) tampil sebagai lagu biasa
                    $song = $custom->songData();
                    if (!empty($song['bait'])) {
                        $this->pushSong($slides, "$prefix-x$ci", 'Nyanyian Tambahan', ['judul' => (string) $song['judul'], 'bait' => (array) $song['bait']], false);
                    }
                    continue;
                }
                $text = (string) $custom->content;
                $customCamera = str_contains($text, self::CAMERA_MARKER);
                $text = str_replace(self::CAMERA_MARKER, '', $text);
                $title = $this->cleanTitle($custom->title);
                $customPrefix = "$prefix-x$ci";

                if ($title === '') {
                    $slides[] = $this->slide("$customPrefix-0", 'instruksi', $section, ['content' => trim($text), 'use_camera' => $customCamera]);
                } else {
                    $this->pushText($slides, $customPrefix, 'custom', $section, $title, $text, $customCamera);
                }
            }
        }

        $slides[] = $this->slide('closing', 'closing', 'Penutup', ['title' => 'PENUTUP', 'content' => "TUHAN YESUS\nMEMBERKATI"]);

        return $slides;
    }

    /**
     * Jenis bagian liturgi dari judulnya: dipakai editor dan pembuat slide.
     * "Doa Pembacaan Alkitab" adalah teks doa, bukan slot ayat.
     */
    public static function itemType(?string $title): string
    {
        $t = mb_strtolower((string) $title);
        if (str_contains($t, 'nyanyian') || str_contains($t, 'pujian')) {
            return 'nyanyian';
        }
        if ((str_contains($t, 'alkitab') || preg_match('/\bbacaan\b/u', $t)) && !preg_match('/\bdoa\b/u', $t)) {
            return 'alkitab';
        }

        return 'teks';
    }

    public function cleanTitle($title): string
    {
        if (!is_string($title)) {
            return '';
        }
        $title = str_ireplace('(opsional)', '', $title);
        $title = preg_replace('/slide bebas\s*[:\-]?\s*/i', '', $title);
        $title = preg_replace('/^nyanyian\s*[:\-]\s*/i', '', $title);
        $title = preg_replace('/^nyanyian\s*$/i', '', trim($title));

        return trim($title, " -:");
    }

    /**
     * Pecah teks menjadi potongan slide.
     * - `===SLIDE_BREAK===` = pemisah manual. Potongan manual tetap dipecah
     *   lagi HANYA bila melebihi batas baris (dulu tidak, sehingga satu
     *   perikop Alkitab bisa menjadi satu slide raksasa).
     * - Potongan lebih dari 4 baris dibagi RATA (5 -> 2+3), di akhir kalimat terdekat.
     */
    public function splitText($text): array
    {
        return array_column($this->splitPieces($text), 'content');
    }

    /**
     * Seperti splitText(), ditambah label pembicara. Bagian yang diawali baris
     * `[PNT]`, `[JEMAAT]`, dsb. mendapat label itu: juga pada setiap slide
     * lanjutan bila bagian tersebut terlalu panjang dan dipecah.
     *
     * @return array<int, array{content: string, speaker: string}>
     */
    public function splitPieces($text): array
    {
        if (is_array($text)) {
            $text = $text['content'] ?? '';
        }
        if (!is_string($text)) {
            return [];
        }

        $manual = str_contains($text, self::BREAK_MARKER);
        $out = [];
        foreach ($manual ? explode(self::BREAK_MARKER, $text) : [$text] as $part) {
            $speaker = '';
            if (preg_match('/^\s*\[([^\]\r\n]{1,30})\][ \t]*(?:\r?\n|$)(.*)$/s', $part, $m) && !preg_match('/^\s*reff?\s*$/i', $m[1])) {
                $speaker = mb_strtoupper(trim($m[1]));
                $part = $m[2];
            }
            foreach ($this->autoSplit($part) as $piece) {
                $out[] = ['content' => $piece, 'speaker' => $speaker];
            }
        }

        return $out;
    }

    private function autoSplit(string $text): array
    {
        $text = preg_replace("/[\r\n]+/", "\n", trim($text));
        if ($text === '') {
            return [];
        }

        // Batas 45 karakter hanya untuk MENGHITUNG baris per slide. Potongan
        // lanjutan dari satu baris asli digabung kembali dengan spasi, supaya
        // kotak tulisan yang membungkus teks (tanpa kata yatim seperti "orang").
        $lines = [];
        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            foreach (explode("\n", wordwrap($line, self::MAX_CHARS_PER_LINE, "\n", false)) as $n => $wrapped) {
                $lines[] = ['text' => trim($wrapped), 'continues' => $n > 0];
            }
        }

        // Satu paragraf panjang (tanpa baris baru): potong di akhir kalimat, bukan di
        // batas baris hitungan 45 huruf ("…pemerasan dan" / "penyelewengan…").
        if (count($lines) > self::MAX_LINES_PER_SLIDE && !str_contains($text, "\n")) {
            return $this->splitLong($text, self::MAX_LINES_PER_SLIDE * self::MAX_CHARS_PER_LINE);
        }

        // Bagi rata, bukan rakus: 5 baris -> 2+3 (bukan 4+1 dengan satu baris yatim
        // seperti "Bersyukurlah!"). Titik potong digeser ±1 baris ke akhir kalimat
        // atau koma terdekat agar tiap slide enak dinyanyikan/dibaca.
        $n = count($lines);
        $max = self::MAX_LINES_PER_SLIDE;
        $k = (int) ceil($n / $max);
        $cuts = [0];
        for ($j = 1; $j < $k; $j++) {
            $prev = end($cuts);
            $target = (int) round($j * $n / $k);
            $best = null;
            $bestScore = -1;
            foreach ([$target, $target - 1, $target + 1] as $c) {
                $feasible = $c - $prev >= 1 && $c - $prev <= $max && $n - $c >= 1 && $n - $c <= ($k - $j) * $max;
                if (!$feasible) {
                    continue;
                }
                $end = $lines[$c - 1]['text'];
                $score = (preg_match('/[.?!;:]["\')]*$/u', $end) ? 4 : (preg_match('/,["\')]*$/u', $end) ? 2 : 0))
                    + ($lines[$c]['continues'] ? 0 : 1); // lebih baik di batas baris asli

                if ($score > $bestScore) {
                    [$best, $bestScore] = [$c, $score];
                }
            }
            $cuts[] = $best ?? min($prev + $max, $target);
        }
        $cuts[] = $n;

        $slides = [];
        for ($i = 0; $i < count($cuts) - 1; $i++) {
            $slides[] = $this->joinLines(array_slice($lines, $cuts[$i], $cuts[$i + 1] - $cuts[$i]));
        }

        return $slides;
    }

    private function joinLines(array $lines): string
    {
        $out = '';
        foreach ($lines as $i => $line) {
            $out .= ($i === 0 ? '' : ($line['continues'] ? ' ' : "\n")) . $line['text'];
        }

        return $out;
    }

    private function pushText(array &$slides, string $prefix, string $type, string $section, string $title, $text, bool $camera): void
    {
        foreach ($this->splitPieces($text) as $n => $piece) {
            $slides[] = $this->slide("$prefix-$n", $type, $section, [
                'title' => $title,
                'content' => $piece['content'],
                'speaker' => $piece['speaker'],
                // Respons yang dinyanyikan ([JEMAAT MENYANYIKAN]) tampil bergaya nyanyian di layar
                'sung' => self::isSungSpeaker($piece['speaker']),
                'use_camera' => $camera,
            ]);
        }
    }

    public static function isSungSpeaker(?string $speaker): bool
    {
        return (bool) preg_match('/MENYANYI|BERNYANYI/u', mb_strtoupper((string) $speaker));
    }

    /** Nama bagian untuk slide instruksi tanpa judul: baris pertamanya ("JEMAAT BERDIRI" -> "Jemaat Berdiri"). */
    private function instructionSection(string $content): string
    {
        $first = trim(strtok(trim($content), "\n") ?: '');
        $first = trim($first, " .…");

        return $first !== '' ? mb_convert_case(mb_strtolower($first), MB_CASE_TITLE) : 'Instruksi';
    }

    private function pushSong(array &$slides, string $prefix, string $base, array $content, bool $camera): void
    {
        $songTitle = trim((string) ($content['judul'] ?? ''));
        $section = $base !== '' ? $base : ($songTitle !== '' ? $songTitle : 'Nyanyian');
        $coverSub = $base;
        if ($songTitle !== '' && $base !== '' && stripos($songTitle, $base) !== false) {
            $coverSub = '';
        }

        $slides[] = $this->slide("$prefix-cover", 'song_cover', $section, [
            'title' => $coverSub,
            'content' => $songTitle,
            'use_camera' => $camera,
        ]);

        if ($songTitle !== '') {
            $lyricTitle = ($base !== '' && stripos($songTitle, $base) !== false) ? $songTitle : ($base !== '' ? "$base - " : '') . $songTitle;
        } else {
            $lyricTitle = $base;
        }

        $used = [];
        foreach ($content['bait'] as $key => $baitText) {
            $raw = trim((string) $baitText);
            if ($raw === '') {
                continue;
            }

            $isReff = (is_string($key) && stripos($key, 'ref') !== false) || preg_match('/^\[?reff?\]?[\s\:\.\-]?/i', $raw);
            $clean = preg_replace('/^\[?REFF\]?\s*/i', '', $raw);

            $watermark = '';
            if (!$isReff) {
                $watermark = preg_match('/\d+/', (string) $key, $m) ? $m[0] : (string) $key;
            }

            $keyPart = preg_replace('/[^A-Za-z0-9]/', '', (string) $key) ?: 'b';
            $baitKey = "$prefix-b$keyPart";
            if (isset($used[$baitKey])) {
                $baitKey .= '_' . (++$used[$baitKey]);
            } else {
                $used[$baitKey] = 0;
            }

            foreach ($this->splitText($clean) as $n => $piece) {
                $slides[] = $this->slide("$baitKey-$n", 'song_lyric', $section, [
                    'title' => $lyricTitle . ($isReff ? ' (Reff)' : ''),
                    'content' => $piece,
                    'watermark' => $watermark,
                    'is_reff' => (bool) $isReff,
                    'use_camera' => $camera,
                ]);
            }
        }
    }

    /**
     * Bacaan Alkitab:
     *  1. slide referensi besar ("Yesaya 45:1-8"): cukup ini bila isi ayat dikosongkan;
     *  2. ayat-ayat dikelompokkan per slide utuh per ayat (ayat panjang dipecah di
     *     akhir kalimat/klausa), tiap slide membawa referensinya sendiri ("Yesaya 45:4-6").
     *
     * Format isi (hasil tombol Tarik, boleh diedit tangan):
     *   "16 Karena begitu besar ..."  /  "4:1 Ketika ..."  (nomor ayat di awal baris)
     *   "# Matius 5:1-3"             (awal perikop berikutnya)
     *   "===SLIDE_BREAK==="          (paksa pindah slide)
     * Format lama "YOHANES 3:16\n===SLIDE_BREAK===\n16. ..." tetap terbaca.
     */
    private function pushBible(array &$slides, string $prefix, string $section, string $base, array $content, bool $camera): void
    {
        $bible = app(Bible::class);
        $ref = trim((string) ($content['ref'] ?? ''));
        $groups = explode(self::BREAK_MARKER, (string) ($content['content'] ?? ''));

        // Format lama: referensi ada di potongan pertama
        if (count($groups) > 1 && !$this->hasVerseLines($groups[0]) && mb_strlen(trim($groups[0])) <= 120) {
            $first = trim(array_shift($groups));
            if ($ref === '') {
                $ref = $first;
            }
        }

        $passages = [];
        if ($ref !== '') {
            try {
                $passages = $bible->parse($ref);
                $ref = $bible->format($passages);
            } catch (\InvalidArgumentException) {
                // referensi tulisan bebas: tampilkan apa adanya
            }
        }

        if ($ref !== '') {
            $slides[] = $this->slide("$prefix-ref", 'bible_ref', $section, [
                'title' => $base,
                'content' => $ref,
                'version' => Bible::VERSION,
                'use_camera' => $camera,
            ]);
        }

        if (!array_filter($groups, fn ($g) => $this->hasVerseLines($g))) {
            // Teks bebas tanpa nomor ayat: pecah seperti teks biasa
            $this->pushText($slides, $prefix, 'text', $section, $ref !== '' ? $ref : $base, implode(self::BREAK_MARKER, $groups), $camera);

            return;
        }

        $ctx = ['name' => $passages[0]['name'] ?? '', 'chapter' => $passages[0]['segments'][0][0] ?? null];
        $n = 0;
        foreach ($groups as $group) {
            $current = [];
            $length = 0;
            $flush = function () use (&$current, &$length, &$slides, &$n, $prefix, $section, $base, $camera) {
                if (!$current) {
                    return;
                }
                $slides[] = $this->slide("$prefix-v$n", 'bible', $section, [
                    'title' => $base,
                    'content' => implode("\n", array_map(fn ($c) => trim($c['label'] . ' ' . $c['text']), $current)),
                    'verses' => array_map(fn ($c) => ['n' => $c['label'], 'text' => $c['text']], $current),
                    'ref' => $this->localRef($current),
                    'use_camera' => $camera,
                ]);
                $n++;
                $current = [];
                $length = 0;
            };

            foreach ($this->parseVerseLines($group, $ctx, $bible) as $verse) {
                if ($verse === null) { // "# Referensi" = perikop baru, mulai slide baru
                    $flush();
                    continue;
                }
                foreach ($this->splitLong($verse['text'], self::BIBLE_CHARS_PER_SLIDE) as $i => $piece) {
                    $chunk = array_merge($verse, ['label' => $i === 0 ? $verse['label'] : '']);
                    $chunk['text'] = $piece;
                    $size = mb_strlen($piece) + 4;
                    if ($current && $length + $size > self::BIBLE_CHARS_PER_SLIDE) {
                        $flush();
                    }
                    if ($i > 0 && $current) {
                        // lanjutan ayat yang sama di slide yang sama: satu paragraf
                        $current[array_key_last($current)]['text'] .= ' ' . $piece;
                    } else {
                        $current[] = $chunk;
                    }
                    $length += $size;
                }
            }
            $flush();
        }
    }

    private function hasVerseLines(string $text): bool
    {
        return (bool) preg_match('/^\s*\d+(?::\d+)?[.)]?\s+\S/mu', $text);
    }

    /**
     * @return array<int, array{label:string, text:string, name:string, chapter:?int, verse:?int}|null>
     */
    private function parseVerseLines(string $text, array &$ctx, Bible $bible): array
    {
        $out = [];
        $last = null;
        foreach (preg_split("/\r?\n/", $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_starts_with($line, '#')) {
                try {
                    $p = $bible->parse(ltrim($line, "# \t"))[0];
                    $ctx = ['name' => $p['name'], 'chapter' => $p['segments'][0][0]];
                } catch (\InvalidArgumentException) {
                }
                $out[] = null;
                $last = null;
                continue;
            }
            if (preg_match('/^(\d+)(?::(\d+))?[.)]?\s+(.+)$/u', $line, $m)) {
                if (isset($m[2]) && $m[2] !== '') {
                    $ctx['chapter'] = (int) $m[1];
                    $verse = (int) $m[2];
                } else {
                    $verse = (int) $m[1];
                }
                $out[] = ['label' => $m[1] . (isset($m[2]) && $m[2] !== '' ? ':' . $m[2] : ''), 'text' => $m[3],
                    'name' => $ctx['name'], 'chapter' => $ctx['chapter'], 'verse' => $verse];
                $last = array_key_last($out);
            } elseif ($last !== null) {
                $out[$last]['text'] .= ' ' . $line; // baris lanjutan dari ayat sebelumnya
            } else {
                $out[] = ['label' => '', 'text' => $line, 'name' => $ctx['name'], 'chapter' => $ctx['chapter'], 'verse' => null];
                $last = array_key_last($out);
            }
        }

        return $out;
    }

    /** "Yesaya 45:4-6" untuk potongan ayat pada satu slide */
    private function localRef(array $chunks): string
    {
        $withVerse = array_values(array_filter($chunks, fn ($c) => $c['verse'] !== null && $c['chapter'] !== null));
        if (!$withVerse || $withVerse[0]['name'] === '') {
            return '';
        }
        $a = $withVerse[0];
        $b = end($withVerse);
        $ref = "{$a['name']} {$a['chapter']}:{$a['verse']}";
        if ($b['chapter'] !== $a['chapter']) {
            $ref .= "-{$b['chapter']}:{$b['verse']}";
        } elseif ($b['verse'] !== $a['verse']) {
            $ref .= "-{$b['verse']}";
        }

        return $ref;
    }

    /**
     * Pecah ayat panjang menjadi potongan yang RATA (300 huruf -> 2 x ±150), dengan
     * titik potong di akhir kalimat/klausa terdekat dari titik idealnya.
     */
    private function splitLong(string $text, int $max): array
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        $len = mb_strlen($text);
        if ($len <= (int) ($max * 1.2)) {
            return [$text]; // sedikit lebih panjang: huruf dikecilkan, ayat tetap utuh
        }

        $words = explode(' ', $text);
        $ends = []; // posisi huruf di akhir tiap kata
        $pos = 0;
        foreach ($words as $i => $w) {
            $pos += mb_strlen($w) + ($i > 0 ? 1 : 0);
            $ends[$i] = $pos;
        }

        $k = (int) ceil($len / $max);
        $target = $len / $k;
        $cuts = [];
        $prev = -1;
        for ($j = 1; $j < $k; $j++) {
            $ideal = $j * $target;
            $best = null;
            $bestScore = -INF;
            for ($i = $prev + 1; $i < count($words) - 1; $i++) {
                $dist = abs($ends[$i] - $ideal);
                if ($dist > $target * 0.4) {
                    continue;
                }
                $punct = preg_match('/[.!?;:]["\')]*$/u', $words[$i]) ? 3 : (preg_match('/,["\')]*$/u', $words[$i]) ? 2 : 0);
                $score = $punct * $target - $dist;
                if ($score > $bestScore) {
                    [$best, $bestScore] = [$i, $score];
                }
            }
            if ($best !== null) {
                $cuts[] = $best;
                $prev = $best;
            }
        }

        $out = [];
        $start = 0;
        foreach (array_merge($cuts, [count($words) - 1]) as $cut) {
            $out[] = implode(' ', array_slice($words, $start, $cut - $start + 1));
            $start = $cut + 1;
        }

        return $out;
    }

    private function isEmptyContent($content): bool
    {
        if (is_array($content)) {
            return empty($content['judul']) && empty($content['bait']) && trim((string) ($content['content'] ?? '')) === ''
                && trim((string) ($content['ref'] ?? '')) === '';
        }

        return trim((string) $content) === '';
    }

    private function slide(string $key, string $type, string $section, array $data): array
    {
        return array_merge([
            'key' => $key,
            'type' => $type,
            'section' => $section,
            'title' => '',
            'content' => '',
            'watermark' => '',
            'speaker' => '',
            'use_camera' => false,
        ], $data);
    }
}
