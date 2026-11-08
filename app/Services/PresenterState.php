<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Status tayangan untuk mode "2 komputer" (layar live di komputer lain).
 * Disimpan di cache (file): tidak menyentuh database, sangat ringan.
 * Setiap perubahan mendapat `seq` baru; layar live mengirim seq terakhirnya
 * dan server cukup membalas 204 bila tidak ada perubahan.
 */
class PresenterState
{
    private const TTL_SECONDS = 60 * 60 * 24;

    public static function get(int $scheduleId): array
    {
        return Cache::get(self::key($scheduleId)) ?? [
            'seq' => 0,
            'index' => 0,
            'key' => null,
            'blank' => null,
            'hideText' => false,
            'contentVersion' => 0,
            'ticker' => null,
            'client' => null,
        ];
    }

    public static function push(int $scheduleId, array $changes): array
    {
        $state = array_merge(self::get($scheduleId), $changes, ['seq' => self::nextSeq($scheduleId)]);
        Cache::put(self::key($scheduleId), $state, self::TTL_SECONDS);

        return $state;
    }

    /** Tandai isi/pengaturan berubah agar layar live mengambil ulang data slide. */
    public static function bumpContent(int $scheduleId): array
    {
        return self::push($scheduleId, ['contentVersion' => self::nextSeq($scheduleId)]);
    }

    private static function nextSeq(int $scheduleId): int
    {
        $now = (int) floor(microtime(true) * 1000);
        $current = Cache::get(self::key($scheduleId))['seq'] ?? 0;

        return max($now, $current + 1);
    }

    private static function key(int $scheduleId): string
    {
        return "presenter_state_{$scheduleId}";
    }
}
