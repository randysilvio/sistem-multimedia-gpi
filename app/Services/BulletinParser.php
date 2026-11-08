<?php

namespace App\Services;

use App\Models\Liturgy;
use App\Models\Song;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Membaca teks tata ibadah (buletin) lalu menebak: template, tanggal, tema,
 * bacaan Alkitab, dan lagu untuk tiap slot nyanyian.
 *
 * Lagu dicocokkan ke slot lewat KATA KUNCI di sekitarnya ("Nyanyian Persiapan",
 * "Jabat Tangan", "INTROITUS", "DOA SYAFAAT", ...), sisanya berurutan. Hasilnya
 * selalu ditampilkan dulu untuk diperiksa operator sebelum disimpan.
 */
class BulletinParser
{
    public const BOOKS = ['KJ', 'NKB', 'PKJ', 'NR', 'DSL', 'GPM'];

    private const MONTHS = [
        'januari' => 1, 'jan' => 1, 'februari' => 2, 'feb' => 2, 'pebruari' => 2, 'maret' => 3, 'mar' => 3,
        'april' => 4, 'apr' => 4, 'mei' => 5, 'juni' => 6, 'jun' => 6, 'juli' => 7, 'jul' => 7,
        'agustus' => 8, 'agu' => 8, 'agt' => 8, 'september' => 9, 'sep' => 9, 'sept' => 9,
        'oktober' => 10, 'okt' => 10, 'november' => 11, 'nov' => 11, 'nopember' => 11, 'desember' => 12, 'des' => 12,
    ];

    private const STOPWORDS = ['nyanyian', 'jemaat', 'slide', 'bebas', 'berdiri', 'duduk', 'pujian', 'tuhan', 'allah', 'yesus', 'kristus', 'dengan', 'kepada', 'dalam', 'untuk', 'tangan', 'saudara'];

    public function __construct(private Bible $bible)
    {
    }

    /**
     * @return array{
     *   liturgy: ?Liturgy, date: ?string, theme: ?string, bible: ?string,
     *   slots: array<int, array{item_id: int, title: string, pick: ?array}>,
     *   unused: array<int, string>, templateSongs: array<int, string>
     * }
     */
    public function parse(string $text, ?Liturgy $forced = null): array
    {
        $lines = $this->lines($text);
        $refs = $this->songRefs($lines);
        $liturgy = $forced ?? $this->guessTemplate($text, $refs);

        $result = [
            'liturgy' => $liturgy,
            'date' => $this->guessDate($text),
            'theme' => $this->guessTheme($lines),
            'bible' => $this->guessBible($lines),
            'slots' => [],
            'unused' => [],
            'templateSongs' => [],
        ];
        if (!$liturgy) {
            $result['unused'] = array_map(fn ($r) => $r['display'], $refs);

            return $result;
        }

        $items = $liturgy->items()->orderBy('order_number')->get()->values();
        // Lagu yang sudah tertulis di judul bagian template (mis. "Jawaban Berkat (KJ 348)") bukan slot
        $templateTitles = mb_strtoupper($items->pluck('title')->implode(' | '));
        $refs = array_values(array_filter($refs, function ($r) use ($templateTitles, &$result) {
            if (preg_match('/\b' . preg_quote($r['book'], '/') . '\s*' . preg_quote($r['number'], '/') . '\b/u', $templateTitles)) {
                $result['templateSongs'][] = $r['display'];

                return false;
            }

            return true;
        }));

        $slots = $this->slots($items);
        [$assign, $unused] = $this->assign($slots, $refs);

        foreach ($slots as $si => $slot) {
            $ref = isset($assign[$si]) ? $refs[$assign[$si]] : null;
            $result['slots'][] = [
                'item_id' => $slot['item']->id,
                'title' => $slot['item']->title,
                'pick' => $ref ? ['book' => $ref['book'], 'number' => $ref['number'], 'verses' => $ref['verses'], 'label' => $ref['display']] : null,
            ];
        }
        $result['unused'] = array_map(fn ($i) => $refs[$i]['display'], $unused);

        return $result;
    }

    /** @return array<int, string> */
    private function lines(string $text): array
    {
        $text = str_replace(["\u{201C}", "\u{201D}", "\u{2018}", "\u{2019}", "\u{00A0}", "\r"], ['"', '"', "'", "'", ' ', ''], $text);

        return array_values(array_filter(array_map(fn ($l) => trim(preg_replace('/[ \t]+/u', ' ', $l)), explode("\n", $text)), fn ($l) => $l !== ''));
    }

    /**
     * Semua penyebutan lagu, berurutan: "KJ 81 : 1, 2", "Ny.GPM.No. 65", "DSL. 181 : 1 dst",
     * atau judul berkutip tanpa nomor ("SEKA-SEKA YO") yang ada di Database Lagu.
     */
    private function songRefs(array $lines): array
    {
        $refs = [];
        $books = implode('|', self::BOOKS);
        foreach ($lines as $li => $line) {
            $ctx = mb_strtolower(implode(' ', array_slice($lines, max(0, $li - 3), 3)));
            $found = false;
            $re = '/\b(' . $books . ')\b\.?\s*(?:No\.?\s*)?(\d{1,3})\b\s*(?::\s*([\d\s,&\-]*\d(?:\s*dst)?|dst))?/iu';
            if (preg_match_all($re, $line, $ms, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
                foreach ($ms as $m) {
                    $prefix = trim(mb_substr($line, 0, mb_strlen(substr($line, 0, $m[0][1]))));
                    // Iringan instrumen saja (mis. "Doa diiringi instrumen PKJ 46") bukan lagu yang dinyanyikan
                    if (preg_match('/iring|instrumen/iu', $line)) {
                        continue;
                    }
                    $book = strtoupper($m[1][0]);
                    $number = (string) (int) $m[2][0];
                    $refs[] = [
                        'book' => $book,
                        'number' => $number,
                        'verses' => $this->verses($m[3][0] ?? ''),
                        'label' => mb_strtolower($prefix),
                        'ctx' => $ctx . ' ' . mb_strtolower($prefix),
                        'display' => trim($book . ' ' . $number . (isset($m[3]) && $m[3][0] !== '' ? ' : ' . trim($m[3][0]) : '')),
                        'line' => $li,
                    ];
                    $found = true;
                }
            }
            if ($found) {
                continue;
            }
            // Judul berkutip tanpa nomor buku: cari di database lagu
            if (preg_match('/"\s*([^"]{3,60}?)\s*"/u', $line, $q)
                && (preg_match('/lagu|nyanyi|menyanyi/iu', $line) || preg_match('/^"[^"]+"$/u', $line))) {
                $song = $this->songByTitle($q[1]);
                $last = end($refs);
                if ($song && !($last && $last['book'] === $song->book && $last['number'] === $song->number && $li - $last['line'] <= 2)) {
                    $prefix = mb_strtolower(trim(mb_substr($line, 0, (int) mb_strpos($line, '"'))));
                    $refs[] = [
                        'book' => $song->book,
                        'number' => $song->number,
                        'verses' => null,
                        'label' => $prefix,
                        'ctx' => $ctx . ' ' . $prefix,
                        'display' => '"' . trim($q[1]) . '" (' . $song->displayTitle() . ')',
                        'line' => $li,
                    ];
                }
            }
        }

        return $refs;
    }

    /** "1, 2" -> [1, 2]; "1 dst" / "dst" / kosong -> null (semua bait) */
    private function verses(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '' || preg_match('/dst/iu', $raw)) {
            return null;
        }
        $nums = [];
        foreach (preg_split('/[,&]/u', $raw) as $part) {
            if (preg_match('/^\s*(\d+)\s*-\s*(\d+)\s*$/u', $part, $r)) {
                $nums = array_merge($nums, range((int) $r[1], min((int) $r[2], (int) $r[1] + 20)));
            } elseif (preg_match('/\d+/u', $part, $n)) {
                $nums[] = (int) $n[0];
            }
        }
        $nums = array_values(array_unique(array_filter($nums, fn ($n) => $n > 0 && $n < 100)));

        return $nums ?: null;
    }

    private function songByTitle(string $title): ?Song
    {
        $words = array_filter(preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($title)), fn ($w) => mb_strlen($w) >= 2);
        if (count($words) < 2 && mb_strlen(implode('', $words)) < 5) {
            return null;
        }
        $like = '%' . implode('%', $words) . '%';

        return Song::whereRaw('LOWER(title) LIKE ?', [$like])->orderByRaw("CASE WHEN book = 'BEBAS' THEN 1 ELSE 0 END")->first();
    }

    /**
     * Slot nyanyian template + kata kuncinya: dari judul slot (bobot 2) dan bagian
     * sebelumnya (bobot 1), mis. slot sesudah "INTROITUS" mendapat kata "introitus".
     */
    private function slots(Collection $items): array
    {
        $slots = [];
        foreach ($items as $i => $item) {
            if (SlideBuilder::itemType($item->title) !== 'nyanyian') {
                continue;
            }
            $prev = $items[$i - 1] ?? null;
            if ($prev && SlideBuilder::itemType($prev->title) === 'nyanyian') {
                $prev = null; // slot lagu sebelumnya bukan petunjuk untuk slot ini
            }
            $prevText = $prev ? (str_contains(mb_strtolower($prev->title), 'slide bebas') ? strtok((string) $prev->static_content, "\n") : $prev->title) : '';
            $slots[] = ['item' => $item, 'own' => $this->keywords($item->title), 'prev' => $this->keywords((string) $prevText), 'phrases' => $this->bigrams((string) $prevText)];
        }

        return $slots;
    }

    /** Pasangan kata dari bagian sebelumnya, mis. "pengakuan dosa" vs "pengakuan iman". */
    private function bigrams(string $text): array
    {
        $w = array_values(array_filter(preg_split('/[^\p{L}]+/u', mb_strtolower($text)), fn ($x) => mb_strlen($x) >= 3));
        $out = [];
        for ($i = 0; $i < count($w) - 1; $i++) {
            $out[] = $w[$i] . ' ' . $w[$i + 1];
        }

        return $out;
    }

    private function keywords(string $text): array
    {
        $words = preg_split('/[^\p{L}]+/u', mb_strtolower($text));

        return array_values(array_unique(array_filter($words, fn ($w) => mb_strlen($w) >= 5 && !in_array($w, self::STOPWORDS, true))));
    }

    /** @return array{0: array<int, int>, 1: array<int, int>} [slot => ref], ref tak terpakai */
    private function assign(array $slots, array $refs): array
    {
        $assign = [];
        $pending = [];
        foreach ($refs as $ri => $ref) {
            $best = null;
            $bestScore = 0;
            foreach ($slots as $si => $slot) {
                if (isset($assign[$si])) {
                    continue;
                }
                $score = 0;
                foreach ($slot['own'] as $w) {
                    $score += str_contains($ref['ctx'], $this->stem($w)) ? 2 : 0;
                }
                foreach ($slot['prev'] as $w) {
                    $score += str_contains($ref['ctx'], $this->stem($w)) ? 1 : 0;
                }
                foreach ($slot['phrases'] as $p) {
                    $score += str_contains($ref['ctx'], $p) ? 2 : 0;
                }
                if ($score > $bestScore) {
                    $best = $si;
                    $bestScore = $score;
                }
            }
            if ($best !== null) {
                $assign[$best] = $ri;
            } else {
                $pending[] = $ri;
            }
        }
        // Sisanya: isi slot kosong berurutan
        foreach ($slots as $si => $slot) {
            if (!isset($assign[$si]) && $pending) {
                $assign[$si] = array_shift($pending);
            }
        }

        return [$assign, $pending];
    }

    /** "penyesalan" cocok juga dengan "menyesal"; potong akhiran umum */
    private function stem(string $w): string
    {
        return mb_strlen($w) > 7 ? mb_substr($w, 0, 7) : $w;
    }

    private function guessTemplate(string $text, array $refs): ?Liturgy
    {
        $templates = Liturgy::templates()->orderBy('id')->get();
        $t = mb_strtolower($text);
        $find = fn (string $needle) => $templates->first(fn ($l) => str_contains(mb_strtolower($l->name), $needle));

        if (preg_match('/\badven/u', $t) && ($l = $find('adven'))) {
            return $l;
        }
        if (preg_match('/khusus anak|ibadah anak|minggu anak/u', $t) && ($l = $find('anak'))) {
            return $l;
        }
        if (preg_match('/kunci bulan/u', $t) && ($l = $find('kunci'))) {
            return $l;
        }
        if (preg_match('/minggu\s*ke\s*-?\s*(\d)|minggu\s+(i{1,3}|iv|v)\b/u', $t, $m)) {
            $roman = ['1' => 'I', '2' => 'II', '3' => 'III', '4' => 'IV', '5' => 'V'][$m[1] ?? ''] ?? strtoupper($m[2] ?? '');
            if ($roman && ($l = $templates->first(fn ($l) => preg_match('/^Ibadah Minggu ' . $roman . '$/u', $l->name)))) {
                return $l;
            }
        }
        // Buku lagu terbanyak: KJ -> I, NKB -> II, PKJ -> III, DSL -> IV, NR -> V
        $count = array_count_values(array_column($refs, 'book'));
        arsort($count);
        $map = ['KJ' => 'I', 'NKB' => 'II', 'PKJ' => 'III', 'DSL' => 'IV', 'NR' => 'V'];
        foreach (array_keys($count) as $book) {
            if (isset($map[$book]) && ($l = $templates->first(fn ($l) => $l->name === 'Ibadah Minggu ' . $map[$book]))) {
                return $l;
            }
        }

        return $templates->first();
    }

    private function guessDate(string $text): ?string
    {
        $months = implode('|', array_keys(self::MONTHS));
        if (preg_match('/\b(\d{1,2})\s+(' . $months . ')\.?\s+(\d{4})\b/iu', $text, $m)) {
            try {
                return Carbon::create((int) $m[3], self::MONTHS[mb_strtolower($m[2])], (int) $m[1])->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    private function guessTheme(array $lines): ?string
    {
        foreach ($lines as $line) {
            if (preg_match('/^TEMA\s*:\s*(.+)$/iu', $line, $m)) {
                return mb_convert_case(mb_strtolower(trim($m[1], " .\"")), MB_CASE_TITLE);
            }
        }

        return null;
    }

    /** "PEMBACAAN ALKITAB : EFESUS 1 : 15 - 23" (bukan "DOA PEMBACAAN ALKITAB"), bisa di baris berikutnya. */
    private function guessBible(array $lines): ?string
    {
        foreach ($lines as $i => $line) {
            if (!preg_match('/pembacaan\s+alkitab/iu', $line) || preg_match('/\bdoa\b/iu', $line)) {
                continue;
            }
            $candidates = [preg_replace('/^.*?pembacaan\s+alkitab\s*:?\s*/iu', '', $line), $lines[$i + 1] ?? '', $lines[$i + 2] ?? ''];
            foreach ($candidates as $cand) {
                $cand = preg_replace('/^[^:]*\)\s*:\s*/u', '', (string) $cand);        // "Anak ( yang bertugas ) : ESTER 4"
                $cand = preg_replace('/\(.*$/u', '', $cand);                          // buang "(Oleh Pnt …)"
                $cand = trim(preg_replace('/\s*([:\-])\s*/u', '$1', $cand), " .:");
                if ($cand === '' || !preg_match('/\d/u', $cand)) {
                    continue;
                }
                try {
                    return $this->bible->format($this->bible->parse($cand));
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return null;
    }
}
