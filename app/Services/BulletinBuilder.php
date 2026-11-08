<?php

namespace App\Services;

use App\Models\Schedule;
use Carbon\Carbon;

/**
 * Mengubah jadwal menjadi blok-blok tata ibadah untuk DICETAK (buletin):
 * judul bagian, kalimat liturgi per pembicara, lagu lengkap dengan bait & reff,
 * bacaan Alkitab, dan petunjuk sikap jemaat. Halaman & booklet disusun di browser.
 *
 * Blok: ['t' => cover|heading|rubric|line|song|verse, ...]
 */
class BulletinBuilder
{
    public function __construct(private SlideBuilder $slides)
    {
    }

    public function blocks(Schedule $schedule): array
    {
        $schedule->loadMissing(['details', 'customSlides', 'liturgy.items']);
        $details = $schedule->details->keyBy('liturgy_item_id');
        $customs = $schedule->customSlides->groupBy('liturgy_item_id');

        $out = [[
            't' => 'cover',
            // "Ibadah Minggu Anak" -> "TATA IBADAH MINGGU ANAK" (tanpa "IBADAH" ganda)
            'title' => 'TATA IBADAH ' . mb_strtoupper(preg_replace('/^ibadah\s+/iu', '', str_replace(' (Custom)', '', $schedule->liturgy->name ?? ''))),
            'date' => Carbon::parse($schedule->worship_date)->locale('id')->translatedFormat('l, d F Y'),
            'theme' => $schedule->theme,
            'preacher' => $schedule->preacher_name,
        ]];

        foreach ($schedule->liturgy->items ?? [] as $item) {
            $content = $details->get($item->id)?->dynamic_content ?? $item->static_content;
            $title = $this->slides->cleanTitle($item->title);
            $type = SlideBuilder::itemType($item->title);

            if ($type === 'nyanyian' && is_array($content) && !empty($content['bait'])) {
                $this->song($out, $title !== '' ? $title : 'Nyanyian', (string) ($content['judul'] ?? ''), (array) $content['bait']);
            } elseif ($type === 'alkitab' && is_array($content)) {
                $ref = trim((string) ($content['ref'] ?? ''));
                if ($ref !== '') {
                    $out[] = ['t' => 'heading', 'text' => 'PEMBACAAN ALKITAB : ' . mb_strtoupper($ref)];
                }
            } elseif ((is_array($content) && array_key_exists('custom_title', $content) && trim((string) $content['custom_title']) === '')
                || (is_string($content) && $title === '')) {
                // "Slide Bebas" = petunjuk/judul (teks bawaan template atau yang sudah diedit)
                $this->instruction($out, is_array($content) ? (string) ($content['content'] ?? '') : $content);
            } elseif (is_array($content) || is_string($content)) {
                $text = is_array($content) ? (string) ($content['content'] ?? '') : (string) $content;
                $heading = is_array($content) && trim((string) ($content['custom_title'] ?? '')) !== '' ? $this->slides->cleanTitle($content['custom_title']) : $title;
                if (trim($text) !== '') {
                    if ($heading !== '') {
                        $out[] = ['t' => 'heading', 'text' => mb_strtoupper($heading)];
                    }
                    $this->lines($out, $text);
                }
            }

            foreach ($customs->get($item->id, collect()) as $custom) {
                if ($custom->isSong()) {
                    $song = $custom->songData();
                    $this->song($out, 'Nyanyian', (string) $song['judul'], (array) $song['bait']);
                } else {
                    $text = str_replace(SlideBuilder::CAMERA_MARKER, '', (string) $custom->content);
                    $out[] = ['t' => 'heading', 'text' => mb_strtoupper($this->slides->cleanTitle($custom->title))];
                    $this->lines($out, $text);
                }
            }
        }

        return $out;
    }

    /** "JEMAAT BERDIRI" -> (Jemaat berdiri); judul seperti "INTROITUS" tetap judul. */
    private function instruction(array &$out, string $content): void
    {
        foreach (preg_split('/\r?\n/', trim($content)) as $line) {
            $line = trim($line, " .…");
            if ($line === '') {
                continue;
            }
            if (preg_match('/^(jemaat|anak-anak)\s+(berdiri|duduk)$/iu', $line)) {
                $out[] = ['t' => 'rubric', 'text' => '(' . mb_convert_case(mb_strtolower($line), MB_CASE_TITLE) . ')'];
            } elseif (preg_match('/^(haleluya|hosiana|maranatha|amin)/iu', $line)) {
                $out[] = ['t' => 'line', 'speaker' => 'J', 'text' => str_replace("\n", ' ', $content)];

                return;
            } else {
                $out[] = ['t' => 'heading', 'text' => mb_strtoupper($line)];
            }
        }
    }

    /** Teks liturgi: penanda [PF]/[JEMAAT] jadi label; potongan berurutan dari pembicara sama digabung. */
    private function lines(array &$out, string $text): void
    {
        $pieces = preg_split('/\s*' . preg_quote(SlideBuilder::BREAK_MARKER, '/') . '\s*/u', trim($text));
        $speaker = '';
        $buffer = '';
        $flush = function () use (&$out, &$speaker, &$buffer) {
            if (trim($buffer) !== '') {
                $out[] = ['t' => 'line', 'speaker' => $this->shortSpeaker($speaker), 'text' => trim($buffer)];
            }
            $buffer = '';
        };
        foreach ($pieces as $piece) {
            $piece = trim($piece);
            if (preg_match('/^\[([^\]]{1,40})\]\s*\n?(.*)$/su', $piece, $m)) {
                if (mb_strtoupper(trim($m[1])) !== $speaker) {
                    $flush();
                    $speaker = mb_strtoupper(trim($m[1]));
                }
                $piece = trim($m[2]);
            }
            $buffer .= ($buffer === '' ? '' : ' ') . preg_replace('/\s*\n\s*/u', ' ', $piece);
        }
        $flush();
    }

    private function shortSpeaker(string $s): string
    {
        return match ($s) {
            'JEMAAT' => 'J',
            'JEMAAT MENYANYIKAN' => 'J',
            'PF & JEMAAT', 'PF DAN JEMAAT' => 'PF+J',
            default => $s,
        };
    }

    /** Lagu: "Nyanyian Persiapan : KJ 21 : 1, 2 - Judul", lalu bait; reff ditulis sekali, berikutnya "…Reff". */
    private function song(array &$out, string $label, string $judul, array $bait): void
    {
        $nums = [];
        $verses = [];
        $reff = null;
        foreach ($bait as $key => $text) {
            $text = trim((string) $text);
            if ($text === '') {
                continue;
            }
            if (str_starts_with((string) $key, 'ref') || preg_match('/^\[?REFF\]?/iu', $text)) {
                $reff ??= trim(preg_replace('/^\[?REFF\]?\s*:?\s*/iu', '', $text));
                if ($verses) {
                    $verses[count($verses) - 1]['reff'] = true;
                }
                continue;
            }
            $n = preg_match('/\d+/', (string) $key, $m) ? $m[0] : (string) $key;
            $nums[] = $n;
            $verses[] = ['n' => $n, 'text' => $text, 'reff' => false];
        }
        if (!$verses) {
            return;
        }
        [$code, $title] = array_pad(explode(' - ', $judul, 2), 2, '');
        $out[] = ['t' => 'song', 'label' => $label, 'code' => trim($code), 'verses' => implode(', ', $nums), 'title' => trim($title)];
        foreach ($verses as $i => $v) {
            $out[] = ['t' => 'verse', 'n' => $v['n'], 'text' => $v['text'], 'reff' => $i === 0 && $reff ? $reff : null, 'reffMark' => $i > 0 && $v['reff']];
        }
    }
}
