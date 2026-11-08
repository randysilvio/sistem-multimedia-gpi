<?php

namespace App\Services;

use App\Models\Liturgy;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\Song;

/**
 * Membuat jadwal ibadah lengkap secara otomatis (dipakai "Buat dari Buletin")
 * dan menduplikat jadwal lama ke tanggal baru.
 */
class ScheduleFactory
{
    public function __construct(private Bible $bible, private SlideBuilder $builder)
    {
    }

    /**
     * @param  array<int, array{book?: ?string, number?: ?string, verses?: ?array}>  $songs  per id bagian liturgi (slot nyanyian)
     * @return array{schedule: Schedule, warnings: array<int, string>}
     */
    public function create(Liturgy $liturgy, string $date, ?string $theme, ?string $preacher, array $songs, ?string $bibleRef): array
    {
        $warnings = [];
        $passage = null;
        $bibleRef = trim((string) $bibleRef);
        if ($bibleRef !== '') {
            try {
                $passage = $this->bible->passage($bibleRef);
            } catch (\Throwable $e) {
                $warnings[] = "Ayat \"$bibleRef\" belum bisa ditarik ({$e->getMessage()}). Referensinya tetap disimpan; tarik ulang di halaman edit.";
            }
        }

        $schedule = Schedule::create([
            'liturgy_id' => $liturgy->id,
            'worship_date' => $date,
            'theme' => $theme ?: null,
            'preacher_name' => $preacher ?: null,
            'theme_color' => '#1b2735',
            'display_settings' => DisplaySettings::inheritedForNewSchedule(),
        ]);

        foreach ($liturgy->items()->orderBy('order_number')->get() as $item) {
            $type = SlideBuilder::itemType($item->title);
            if ($type === 'nyanyian') {
                $pick = $songs[$item->id] ?? null;
                if (!$pick || trim((string) ($pick['number'] ?? '')) === '') {
                    $warnings[] = "Slot \"{$item->title}\" belum diisi lagu.";
                    continue;
                }
                $song = Song::findByRef($pick['book'] ?? '', $pick['number']);
                if (!$song) {
                    $warnings[] = "Lagu {$pick['book']} {$pick['number']} untuk \"{$item->title}\" belum ada di Database Lagu. Tambahkan dulu, lalu tarik di halaman edit.";
                    continue;
                }
                if ($song->is_incomplete) {
                    $warnings[] = "Lirik {$song->displayTitle()} belum lengkap, periksa baitnya.";
                }
                $content = $song->slideContent($pick['verses'] ?? null) + ['use_camera' => false];
            } elseif ($type === 'alkitab') {
                if ($bibleRef === '') {
                    $warnings[] = 'Bacaan Alkitab belum diisi.';
                    continue;
                }
                $content = ['ref' => $passage['reference'] ?? $bibleRef, 'content' => $passage['text'] ?? '', 'use_camera' => false];
            } else {
                $content = ['custom_title' => $this->builder->cleanTitle($item->title), 'content' => (string) $item->static_content, 'use_camera' => false];
            }

            ScheduleDetail::create(['schedule_id' => $schedule->id, 'liturgy_item_id' => $item->id, 'dynamic_content' => $content]);
        }

        return ['schedule' => $schedule, 'warnings' => $warnings];
    }

    /** Salin jadwal (isi, slide sisipan, tampilan) ke tanggal baru. */
    public function duplicate(Schedule $source, string $date): Schedule
    {
        $source->loadMissing(['details', 'customSlides']);

        $copy = $source->replicate(['worship_date']);
        $copy->worship_date = $date;
        $copy->save();

        foreach ($source->details as $detail) {
            $copy->details()->create($detail->only(['liturgy_item_id', 'dynamic_content']));
        }
        foreach ($source->customSlides as $slide) {
            $copy->customSlides()->create($slide->only(['liturgy_item_id', 'kind', 'title', 'content']));
        }

        return $copy;
    }
}
