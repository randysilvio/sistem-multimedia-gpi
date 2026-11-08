<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

/**
 * Alkitab Terjemahan Baru (TB): pembaca referensi + pengambil ayat.
 *
 * Referensi yang dipahami (spasi, titik, dan tanda pisah bebas):
 *   Yohanes 3:16          Yoh 3:16-18          Yoh. 3 : 16 - 18
 *   1 Yoh 1:9 / I Yoh 1:9 Yoh 3:16,18,20-22    Yoh 3:36-4:2
 *   Mazmur 23 (satu pasal)  Yudas 3 (kitab satu pasal)
 *   Yes 45:1-8; Mat 5:1-12 (beberapa perikop)   Yoh 3:16; 4:1-3 (kitab sama)
 *
 * Teks diambil per PASAL dari API SABDA (TB, sama dengan Alkitab LAI) dan
 * disimpan permanen di cache: pasal yang pernah ditarik tetap tersedia
 * walau internet di gereja terputus.
 */
class Bible
{
    public const VERSION = 'TB';

    /** [nama resmi TB, jumlah pasal, singkatan LAI & variasi umum] */
    private const BOOKS = [
        1 => ['Kejadian', 50, ['kej']],
        2 => ['Keluaran', 40, ['kel']],
        3 => ['Imamat', 27, ['im', 'ima']],
        4 => ['Bilangan', 36, ['bil']],
        5 => ['Ulangan', 34, ['ul', 'ula']],
        6 => ['Yosua', 24, ['yos']],
        7 => ['Hakim-hakim', 21, ['hak', 'hakim']],
        8 => ['Rut', 4, ['rut']],
        9 => ['1 Samuel', 31, ['1sam', '1sm']],
        10 => ['2 Samuel', 24, ['2sam', '2sm']],
        11 => ['1 Raja-raja', 22, ['1raj', '1raja', '1rj']],
        12 => ['2 Raja-raja', 25, ['2raj', '2raja', '2rj']],
        13 => ['1 Tawarikh', 29, ['1taw']],
        14 => ['2 Tawarikh', 36, ['2taw']],
        15 => ['Ezra', 10, ['ezr']],
        16 => ['Nehemia', 13, ['neh']],
        17 => ['Ester', 10, ['est']],
        18 => ['Ayub', 42, ['ayb', 'ayu']],
        19 => ['Mazmur', 150, ['mzm', 'maz', 'mazm', 'mz']],
        20 => ['Amsal', 31, ['ams']],
        21 => ['Pengkhotbah', 12, ['pkh', 'peng', 'pengk']],
        22 => ['Kidung Agung', 8, ['kid', 'kidung']],
        23 => ['Yesaya', 66, ['yes']],
        24 => ['Yeremia', 52, ['yer']],
        25 => ['Ratapan', 5, ['rat']],
        26 => ['Yehezkiel', 48, ['yeh', 'yehez']],
        27 => ['Daniel', 12, ['dan']],
        28 => ['Hosea', 14, ['hos']],
        29 => ['Yoel', 3, ['yl', 'yoe']],
        30 => ['Amos', 9, ['am', 'amo']],
        31 => ['Obaja', 1, ['ob', 'oba']],
        32 => ['Yunus', 4, ['yun']],
        33 => ['Mikha', 7, ['mi', 'mik']],
        34 => ['Nahum', 3, ['nah']],
        35 => ['Habakuk', 3, ['hab']],
        36 => ['Zefanya', 3, ['zef']],
        37 => ['Hagai', 2, ['hag']],
        38 => ['Zakharia', 14, ['za', 'zak']],
        39 => ['Maleakhi', 4, ['mal']],
        40 => ['Matius', 28, ['mat', 'mt']],
        41 => ['Markus', 16, ['mrk', 'mar', 'mark', 'mr']],
        42 => ['Lukas', 24, ['luk', 'lk']],
        43 => ['Yohanes', 21, ['yoh']],
        44 => ['Kisah Para Rasul', 28, ['kis', 'kisah']],
        45 => ['Roma', 16, ['rm', 'rom']],
        46 => ['1 Korintus', 16, ['1kor']],
        47 => ['2 Korintus', 13, ['2kor']],
        48 => ['Galatia', 6, ['gal']],
        49 => ['Efesus', 6, ['ef', 'efe', 'efs']],
        50 => ['Filipi', 4, ['flp', 'fil']],
        51 => ['Kolose', 4, ['kol']],
        52 => ['1 Tesalonika', 5, ['1tes']],
        53 => ['2 Tesalonika', 3, ['2tes']],
        54 => ['1 Timotius', 6, ['1tim']],
        55 => ['2 Timotius', 4, ['2tim']],
        56 => ['Titus', 3, ['tit']],
        57 => ['Filemon', 1, ['flm']],
        58 => ['Ibrani', 13, ['ibr']],
        59 => ['Yakobus', 5, ['yak']],
        60 => ['1 Petrus', 5, ['1ptr', '1pet']],
        61 => ['2 Petrus', 3, ['2ptr', '2pet']],
        62 => ['1 Yohanes', 5, ['1yoh']],
        63 => ['2 Yohanes', 1, ['2yoh']],
        64 => ['3 Yohanes', 1, ['3yoh']],
        65 => ['Yudas', 1, ['yud']],
        66 => ['Wahyu', 22, ['why', 'wah', 'wahy']],
    ];

    private static ?array $index = null;

    // ------------------------------------------------------------------
    // Pembaca referensi (tanpa internet)
    // ------------------------------------------------------------------

    /**
     * @return array<int, array{book: int, name: string, segments: array<int, array{0:int,1:?int,2:int,3:?int}>}>
     *         segments: [pasalAwal, ayatAwal|null, pasalAkhir, ayatAkhir|null] (null = sampai akhir pasal)
     *
     * @throws InvalidArgumentException  pesan berbahasa Indonesia yang bisa langsung ditampilkan
     */
    public function parse(string $query): array
    {
        $q = str_replace(["\u{2013}", "\u{2014}", "\u{2212}"], '-', trim($query));
        $q = preg_replace('/\s+/u', ' ', $q);
        if ($q === '') {
            throw new InvalidArgumentException('Ketik referensi ayat. Contoh: Yohanes 3:16-18');
        }

        $passages = [];
        $lastBook = null;
        foreach (preg_split('/\s*;\s*/', $q, -1, PREG_SPLIT_NO_EMPTY) as $part) {
            // Pisahkan nama kitab dari angka pasal: nama kitab boleh diawali angka (1 Yoh, 2Kor)
            if (preg_match('/^((?:[1-3]|i{1,3})?\s*\.?\s*[a-z][a-z\s\.\-]*?)\s*\.?\s*(\d.*)$/iu', $part, $m)) {
                $book = $this->resolveBook($m[1]);
                $rest = $m[2];
            } elseif ($lastBook !== null && preg_match('/^\d/', $part)) {
                $book = $lastBook;
                $rest = $part;
            } elseif (preg_match('/^[a-z\s\.\-0-9]+$/iu', $part) && !preg_match('/\d\s*$/', $part)) {
                throw new InvalidArgumentException("Tulis pasalnya juga. Contoh: {$this->titleCase($part)} 1:1");
            } else {
                throw new InvalidArgumentException("Referensi \"$part\" tidak dikenali. Contoh: Yohanes 3:16-18");
            }

            $passages[] = ['book' => $book, 'name' => self::BOOKS[$book][0], 'segments' => $this->parseNumbers($book, $rest)];
            $lastBook = $book;
        }

        return $passages;
    }

    /** Referensi rapi: "Yohanes 3:16-18, 20; Matius 5" */
    public function format(array $passages): string
    {
        $out = [];
        foreach ($passages as $p) {
            $parts = [];
            $prevChapter = null;
            foreach ($p['segments'] as [$c1, $v1, $c2, $v2]) {
                if ($v1 === null && $c1 === $c2) {
                    $parts[] = (string) $c1;
                    $prevChapter = null;
                    continue;
                }
                if ($v1 === null) {
                    $parts[] = "$c1-$c2";
                    $prevChapter = null;
                    continue;
                }
                $start = $prevChapter === $c1 ? (string) $v1 : "$c1:$v1";
                if ($c2 !== $c1) {
                    $end = '-' . $c2 . ':' . $v2;
                } elseif ($v2 !== null && $v2 !== $v1) {
                    $end = "-$v2";
                } else {
                    $end = '';
                }
                $parts[] = $start . $end;
                $prevChapter = $c2;
            }
            $out[] = $p['name'] . ' ' . implode(', ', $parts);
        }

        return implode('; ', $out);
    }

    /** Nama kitab -> nomor kitab. Menerima nama lengkap, singkatan LAI, angka romawi, dan salah ketik kecil. */
    public function resolveBook(string $raw): int
    {
        $key = $this->bookKey($raw);
        if ($key === '') {
            throw new InvalidArgumentException('Nama kitab belum diisi.');
        }
        $index = $this->index();

        if (isset($index['exact'][$key])) {
            return $index['exact'][$key];
        }

        // Awalan unik: "yohan" -> Yohanes, "1 korin" -> 1 Korintus
        $hits = array_unique(array_values(array_filter($index['full'], fn ($k) => str_starts_with($k, $key), ARRAY_FILTER_USE_KEY)));
        if (count($hits) === 1) {
            return $hits[0];
        }
        if (count($hits) > 1) {
            $names = implode(', ', array_map(fn ($id) => self::BOOKS[$id][0], array_slice($hits, 0, 4)));
            throw new InvalidArgumentException("Nama kitab \"$raw\" kurang jelas. Maksud Anda: $names?");
        }

        // Salah ketik kecil: "yohanis", "matius" -> "matieus", dsb.
        if (strlen($key) >= 4) {
            $best = null;
            $bestDist = PHP_INT_MAX;
            foreach ($index['full'] as $name => $id) {
                if (preg_match('/^\d/', $name) !== preg_match('/^\d/', $key)) {
                    continue;
                }
                $d = levenshtein($key, $name);
                if ($d < $bestDist) {
                    [$best, $bestDist] = [$id, $d];
                }
            }
            if ($best !== null && $bestDist <= (strlen($key) >= 7 ? 2 : 1)) {
                return $best;
            }
        }

        throw new InvalidArgumentException("Kitab \"$raw\" tidak dikenal. Cek ejaannya, contoh: Yohanes, Mzm, 1 Kor.");
    }

    private function bookKey(string $raw): string
    {
        $s = mb_strtolower(trim($raw));
        $s = preg_replace('/^(iii|ii|i)(?=[\s\.])/u', '$1#', $s); // tandai angka romawi di depan
        $s = str_replace(['iii#', 'ii#', 'i#'], ['3', '2', '1'], $s);

        return preg_replace('/[^a-z0-9]/', '', $s);
    }

    private function index(): array
    {
        if (self::$index !== null) {
            return self::$index;
        }
        $exact = [];
        $full = [];
        foreach (self::BOOKS as $id => [$name, , $aliases]) {
            $k = preg_replace('/[^a-z0-9]/', '', mb_strtolower($name));
            $exact[$k] = $id;
            $full[$k] = $id;
            foreach ($aliases as $a) {
                $exact[$a] = $id;
            }
        }

        return self::$index = ['exact' => $exact, 'full' => $full];
    }

    /** "3:16-18,20" / "3:36-4:2" / "23" / "1-3" -> segmen */
    private function parseNumbers(int $book, string $rest): array
    {
        $name = self::BOOKS[$book][0];
        $chapters = self::BOOKS[$book][1];
        $s = preg_replace('/\s+/', '', $rest);
        $s = preg_replace('/(?<=\d)\.(?=\d)/', ':', $s); // "3.16" -> "3:16"
        $s = rtrim($s, '.,');

        if (!preg_match('/^[\d:,\-]+$/', $s)) {
            throw new InvalidArgumentException("Bagian \"$rest\" tidak dikenali. Contoh: $name 3:16-18");
        }

        // Kitab satu pasal: "Yudas 3-5" berarti ayat 3-5
        if ($chapters === 1 && !str_contains($s, ':') && $s !== '1') {
            $s = '1:' . $s;
        }

        $segments = [];
        if (!str_contains($s, ':')) {
            // Hanya pasal: "23" atau "1-3"
            foreach (explode(',', $s) as $range) {
                [$a, $b] = array_pad(explode('-', $range, 2), 2, null);
                $a = (int) $a;
                $b = $b === null || $b === '' ? $a : (int) $b;
                $segments[] = [$a, null, $b, null];
            }
        } else {
            [$chapter, $verses] = explode(':', $s, 2);
            $chapter = (int) $chapter;
            foreach (explode(',', $verses) as $range) {
                if ($range === '') {
                    continue;
                }
                if (preg_match('/^(\d+):(\d+)(?:-(\d+)(?::(\d+))?)?$/', $range, $m)) {
                    // "4:1" atau "4:1-3" setelah koma: pindah pasal
                    $chapter = (int) $m[1];
                    $v1 = (int) $m[2];
                    if (isset($m[4])) {
                        $segments[] = [$chapter, $v1, (int) $m[3], (int) $m[4]];
                        $chapter = (int) $m[3];
                    } else {
                        $segments[] = [$chapter, $v1, $chapter, isset($m[3]) ? (int) $m[3] : $v1];
                    }
                } elseif (preg_match('/^(\d+)(?:-(\d+)(?::(\d+))?)?$/', $range, $m)) {
                    $v1 = (int) $m[1];
                    if (isset($m[3])) {
                        $segments[] = [$chapter, $v1, (int) $m[2], (int) $m[3]];
                        $chapter = (int) $m[2];
                    } else {
                        $segments[] = [$chapter, $v1, $chapter, isset($m[2]) ? (int) $m[2] : $v1];
                    }
                } else {
                    throw new InvalidArgumentException("Bagian ayat \"$range\" tidak dikenali. Contoh: $name 3:16-18, 20");
                }
            }
        }

        foreach ($segments as [$c1, $v1, $c2, $v2]) {
            foreach ([$c1, $c2] as $c) {
                if ($c < 1 || $c > $chapters) {
                    throw new InvalidArgumentException($chapters === 1
                        ? "$name hanya 1 pasal."
                        : "$name hanya sampai pasal $chapters.");
                }
            }
            if ($c2 < $c1 || ($c2 === $c1 && $v1 !== null && $v2 !== null && $v2 < $v1)) {
                throw new InvalidArgumentException('Urutan ayat terbalik. Tulis dari yang kecil, contoh: 16-18.');
            }
            if ($v1 === 0) {
                throw new InvalidArgumentException('Nomor ayat dimulai dari 1.');
            }
        }
        if (count($segments) > 40) {
            throw new InvalidArgumentException('Terlalu banyak potongan ayat.');
        }

        return $segments;
    }

    private function titleCase(string $s): string
    {
        return mb_convert_case(trim($s), MB_CASE_TITLE);
    }

    // ------------------------------------------------------------------
    // Pengambil ayat
    // ------------------------------------------------------------------

    /**
     * @return array{reference: string, version: string, verses: array<int, array{book:int, chapter:int, verse:int, label:string, text:string, passage:int}>, text: string}
     *
     * @throws InvalidArgumentException  referensi salah / ayat tidak ada
     * @throws RuntimeException          server Alkitab tidak bisa dihubungi dan pasal belum ada di cache
     */
    public function passage(string $query): array
    {
        $passages = $this->parse($query);
        $verses = [];
        $total = 0;

        foreach ($passages as $pi => $p) {
            $multiChapter = count(array_unique(array_merge(...array_map(fn ($s) => range($s[0], $s[2]), $p['segments'])))) > 1;
            foreach ($p['segments'] as $si => [$c1, $v1, $c2, $v2]) {
                for ($c = $c1; $c <= $c2; $c++) {
                    $chapter = $this->chapter($p['book'], $c);
                    $last = max(array_keys($chapter));
                    $from = ($c === $c1 && $v1 !== null) ? $v1 : 1;
                    $to = ($c === $c2 && $v2 !== null) ? $v2 : $last;
                    if ($from > $last) {
                        throw new InvalidArgumentException("{$p['name']} $c hanya sampai ayat $last.");
                    }
                    if ($to > $last) {
                        // "Yoh 3:35-40" -> referensi dirapikan menjadi 3:35-36
                        $to = $last;
                        $passages[$pi]['segments'][$si][3] = $last;
                    }
                    for ($v = $from; $v <= $to; $v++) {
                        if (!isset($chapter[$v])) {
                            continue; // ayat yang memang tidak ada di TB (mis. Mat 17:21)
                        }
                        $verses[] = [
                            'book' => $p['book'],
                            'chapter' => $c,
                            'verse' => $v,
                            'label' => $multiChapter ? "$c:$v" : (string) $v,
                            'text' => $chapter[$v],
                            'passage' => $pi,
                        ];
                        if (++$total > 400) {
                            throw new InvalidArgumentException('Bacaan terlalu panjang (lebih dari 400 ayat). Persempit referensinya.');
                        }
                    }
                }
            }
        }

        if (!$verses) {
            throw new InvalidArgumentException('Ayat tidak ditemukan.');
        }

        $reference = $this->format($passages);
        $lines = [];
        foreach ($passages as $pi => $p) {
            if (count($passages) > 1) {
                $lines[] = '# ' . $this->format([$p]);
            }
            foreach ($verses as $v) {
                if ($v['passage'] === $pi) {
                    $lines[] = $v['label'] . ' ' . $v['text'];
                }
            }
        }

        return ['reference' => $reference, 'version' => self::VERSION, 'verses' => $verses, 'text' => implode("\n", $lines)];
    }

    /** @return array<int, string> nomor ayat => teks */
    public function chapter(int $book, int $chapter): array
    {
        $key = 'alkitab:tb:' . $book . ':' . $chapter;
        $cached = Cache::get($key);
        if (is_array($cached) && $cached) {
            return $cached;
        }

        $name = self::BOOKS[$book][0];
        try {
            $response = Http::timeout(12)->retry(2, 400, throw: false)
                ->get('https://alkitab.sabda.org/api/passage.php', ['passage' => "$name $chapter"]);
        } catch (\Throwable $e) {
            throw new RuntimeException("Tidak bisa terhubung ke server Alkitab. Periksa internet, lalu coba lagi.");
        }
        if (!$response->successful()) {
            throw new RuntimeException('Server Alkitab sedang sibuk (status ' . $response->status() . '). Coba lagi sebentar.');
        }

        $verses = $this->parseSabda($response->body(), $book, $chapter);
        if (!$verses) {
            throw new RuntimeException("Isi $name $chapter tidak terbaca dari server Alkitab.");
        }
        Cache::forever($key, $verses);

        return $verses;
    }

    /** @return array<int, string> */
    public function parseSabda(string $xml, int $book, int $chapter): array
    {
        $prev = libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml);
        libxml_use_internal_errors($prev);
        if (!$doc) {
            return [];
        }

        $out = [];
        foreach ($doc->book as $b) {
            if ((int) $b->book_id !== $book) {
                continue; // SABDA diam-diam mengganti kitab yang salah dengan Kejadian
            }
            foreach ($b->chapter as $c) {
                if ((int) $c->chap !== $chapter) {
                    continue;
                }
                foreach ($c->verses->verse as $v) {
                    $text = $this->cleanText((string) $v->text);
                    if ($text !== '') {
                        $out[(int) $v->number] = $text;
                    }
                }
            }
        }
        ksort($out);

        return $out;
    }

    private function cleanText(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\(\d+-\d+[a-z]?\)\s*/u', '', $text); // penanda perikop "(8-1b)"

        return trim(preg_replace('/\s+/u', ' ', $text));
    }
}
