<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\Song;

/**
 * Daftar periksa kesiapan satu jadwal ibadah (ditampilkan di Dashboard).
 * Setiap butir: ['level' => ok|warn|error|info, 'text' => ..., 'url' => ?string]
 */
class ReadinessCheck
{
    public static function for(Schedule $schedule): array
    {
        $schedule->loadMissing(['details', 'liturgy.items', 'customSlides']);
        $details = $schedule->details->keyBy('liturgy_item_id');
        $editUrl = route('liturgy.edit', $schedule);
        $out = [];

        $emptySongs = [];
        $incomplete = [];
        $bibleState = null;
        foreach ($schedule->liturgy->items ?? [] as $item) {
            $type = SlideBuilder::itemType($item->title);
            $content = $details->get($item->id)?->dynamic_content;
            if ($type === 'nyanyian') {
                $bait = is_array($content) ? array_filter((array) ($content['bait'] ?? []), fn ($b) => trim((string) $b) !== '') : [];
                if (!$bait) {
                    $emptySongs[] = $item->title;
                    continue;
                }
                if (preg_match('/^\s*([A-Z]+)\s+(\S+)\s*-/u', (string) ($content['judul'] ?? ''), $m)) {
                    $song = Song::where('book', $m[1])->where('number', $m[2])->first();
                    if ($song?->is_incomplete) {
                        $incomplete[] = $song->displayTitle();
                    }
                }
            } elseif ($type === 'alkitab') {
                $ref = is_array($content) ? trim((string) ($content['ref'] ?? '')) : '';
                $text = is_array($content) ? trim((string) ($content['content'] ?? '')) : '';
                $bibleState = $ref === '' && $text === '' ? 'empty' : ($text === '' ? 'ref-only' : 'ok');
            }
        }

        if ($emptySongs) {
            $out[] = ['level' => 'error', 'text' => 'Slot lagu belum diisi: ' . implode(', ', array_unique($emptySongs)), 'url' => $editUrl];
        }
        if ($bibleState === 'empty') {
            $out[] = ['level' => 'error', 'text' => 'Bacaan Alkitab belum diisi.', 'url' => $editUrl];
        } elseif ($bibleState === 'ref-only') {
            $out[] = ['level' => 'warn', 'text' => 'Ayat Alkitab belum ditarik, layar hanya menampilkan referensinya.', 'url' => $editUrl];
        }
        if ($incomplete) {
            $out[] = ['level' => 'warn', 'text' => 'Lirik ditandai belum lengkap: ' . implode(', ', array_unique($incomplete)) . '. Periksa bait yang akan dinyanyikan.', 'url' => route('songs.index', ['incomplete' => 1])];
        }
        if (!trim((string) $schedule->preacher_name)) {
            $out[] = ['level' => 'warn', 'text' => 'Nama Pelayan Firman belum diisi (tampil di slide pembukaan).', 'url' => $editUrl];
        }
        $hidden = count(DisplaySettings::for($schedule)['hidden'] ?? []);
        if ($hidden) {
            $out[] = ['level' => 'info', 'text' => "$hidden slide disembunyikan dan akan dilewati saat tayang.", 'url' => route('liturgy.control', $schedule)];
        }
        if (!Branding::hasUploadedLogo()) {
            $out[] = ['level' => 'info', 'text' => 'Logo gereja belum diunggah.', 'url' => route('settings.edit')];
        }
        if (!array_filter($out, fn ($i) => in_array($i['level'], ['error', 'warn'], true))) {
            array_unshift($out, ['level' => 'ok', 'text' => 'Semua lagu, bacaan dan keterangan sudah lengkap.', 'url' => null]);
        }

        return $out;
    }
}
