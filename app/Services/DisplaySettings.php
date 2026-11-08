<?php

namespace App\Services;

use App\Models\Schedule;

/**
 * Pengaturan layar live per jadwal (disimpan di schedules.display_settings).
 * Semua input dari browser dibersihkan lewat sanitize(): hanya kunci yang
 * dikenal yang disimpan, angka dibatasi, warna harus berformat #rrggbb.
 */
class DisplaySettings
{
    public static function defaults(): array
    {
        return config('presentation.defaults');
    }

    /** URL logo untuk layar live: lihat Branding::logoUrl(). */
    public static function logoUrl(): string
    {
        return Branding::logoUrl();
    }

    /** Pengaturan lengkap untuk satu jadwal (default + yang tersimpan). */
    public static function for(Schedule $schedule): array
    {
        $defaults = self::defaults();
        if ($schedule->theme_color && self::isHex($schedule->theme_color)) {
            $defaults['design']['color1'] = $schedule->theme_color;
        }

        return self::sanitize($schedule->display_settings ?? [], $defaults);
    }

    /** Pengaturan untuk jadwal baru: mewarisi jadwal terakhir, tanpa ukuran huruf per slide. */
    public static function inheritedForNewSchedule(): ?array
    {
        $last = Schedule::whereNotNull('display_settings')->latest('id')->first();
        if (!$last) {
            return null;
        }
        $settings = self::sanitize($last->display_settings ?? []);
        // Ukuran huruf, slide tersembunyi & catatan milik slide jadwal lama: tidak diwariskan
        $settings['fonts'] = [];
        $settings['hidden'] = [];
        $settings['notes'] = [];

        return $settings;
    }

    public static function sanitize(array $input, ?array $base = null): array
    {
        $d = $base ?? self::defaults();
        $screenIn = (array) ($input['screen'] ?? []);
        $headerIn = (array) ($input['header'] ?? []);
        $designIn = (array) ($input['design'] ?? []);

        $width = self::num($screenIn['widthCm'] ?? null, 20, 2000, $d['screen']['widthCm']);
        $height = self::num($screenIn['heightCm'] ?? null, 10, 1200, $d['screen']['heightCm']);

        $screen = [
            'widthCm' => $width,
            'heightCm' => $height,
            'marginTopCm' => self::num($screenIn['marginTopCm'] ?? null, 0, $height * 0.7, min($d['screen']['marginTopCm'], $height * 0.7)),
            'marginSideCm' => self::num($screenIn['marginSideCm'] ?? null, 0, $width * 0.4, min($d['screen']['marginSideCm'], $width * 0.4)),
            'marginBottomCm' => self::num($screenIn['marginBottomCm'] ?? null, 0, $height * 0.4, min($d['screen']['marginBottomCm'], $height * 0.4)),
        ];
        // Sisakan minimal 15% tinggi layar untuk kotak tulisan.
        if ($screen['marginTopCm'] + $screen['marginBottomCm'] > $height * 0.85) {
            $screen['marginTopCm'] = min($d['screen']['marginTopCm'], $height * 0.6);
            $screen['marginBottomCm'] = min($d['screen']['marginBottomCm'], $height * 0.2);
        }

        $design = [
            'bg' => self::oneOf($designIn['bg'] ?? null, config('presentation.backgrounds'), $d['design']['bg']),
            'color1' => self::hex($designIn['color1'] ?? null, $d['design']['color1']),
            'color2' => self::hex($designIn['color2'] ?? null, $d['design']['color2']),
            'color3' => self::hex($designIn['color3'] ?? null, $d['design']['color3']),
            'speed' => self::num($designIn['speed'] ?? null, 0.25, 3, $d['design']['speed']),
            'intensity' => self::num($designIn['intensity'] ?? null, 0, 1, $d['design']['intensity']),
            'powerSave' => self::bool($designIn['powerSave'] ?? null, $d['design']['powerSave']),
            'font' => self::oneOf($designIn['font'] ?? null, config('presentation.fonts'), $d['design']['font']),
            'textColor' => self::hex($designIn['textColor'] ?? null, $d['design']['textColor']),
            'shadowColor' => self::hex($designIn['shadowColor'] ?? null, $d['design']['shadowColor']),
            'shadowStrength' => self::num($designIn['shadowStrength'] ?? null, 0, 1, $d['design']['shadowStrength']),
            'songMark' => self::bool($designIn['songMark'] ?? null, $d['design']['songMark'] ?? true),
            'songColor' => self::hex($designIn['songColor'] ?? null, $d['design']['songColor'] ?? '#ffe08a'),
            'songStyle' => self::oneOf($designIn['songStyle'] ?? null, config('presentation.song_styles', ['nomor']), $d['design']['songStyle'] ?? 'nomor'),
        ];

        $keyOk = fn ($key) => is_string($key) && preg_match('/^[A-Za-z0-9_-]{1,80}$/', $key);

        $hidden = [];
        foreach ((array) ($input['hidden'] ?? $d['hidden'] ?? []) as $key) {
            if (count($hidden) >= 1000) {
                break;
            }
            if ($keyOk($key)) {
                $hidden[$key] = true;
            }
        }

        $notes = [];
        foreach ((array) ($input['notes'] ?? $d['notes'] ?? []) as $key => $note) {
            if (count($notes) >= 500) {
                break;
            }
            $note = is_string($note) ? trim(mb_substr(strip_tags($note), 0, 1000)) : '';
            if ($keyOk($key) && $note !== '') {
                $notes[$key] = $note;
            }
        }

        $fonts = [];
        foreach ((array) ($input['fonts'] ?? $d['fonts']) as $key => $size) {
            if (count($fonts) >= 1000) {
                break;
            }
            if (is_string($key) && preg_match('/^[A-Za-z0-9_-]{1,80}$/', $key) && is_numeric($size)) {
                $fonts[$key] = (int) max(20, min(240, round($size)));
            }
        }

        return [
            'screen' => $screen,
            'header' => [
                'show' => self::bool($headerIn['show'] ?? null, $d['header']['show']),
                'logo' => self::bool($headerIn['logo'] ?? null, $d['header']['logo']),
            ],
            'design' => $design,
            'guides' => self::bool($input['guides'] ?? null, $d['guides']),
            'fonts' => $fonts,
            'hidden' => array_keys($hidden),
            'notes' => $notes,
        ];
    }

    private static function num($value, float $min, float $max, $fallback): float|int
    {
        if (!is_numeric($value)) {
            return $fallback;
        }
        $v = round(max($min, min($max, (float) $value)), 2);

        return floor($v) == $v ? (int) $v : $v;
    }

    private static function bool($value, bool $fallback): bool
    {
        if ($value === null || $value === '') {
            return $fallback;
        }
        $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $parsed ?? $fallback;
    }

    private static function hex($value, string $fallback): string
    {
        return is_string($value) && self::isHex($value) ? strtolower($value) : $fallback;
    }

    private static function isHex(string $value): bool
    {
        return (bool) preg_match('/^#[0-9a-fA-F]{6}$/', $value);
    }

    private static function oneOf($value, array $allowed, string $fallback): string
    {
        return in_array($value, $allowed, true) ? $value : $fallback;
    }
}
