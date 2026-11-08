<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Pengaturan aplikasi berbentuk kunci-nilai (mis. logo_path, church_name).
 * Nilai di-cache di container aplikasi (per request) supaya tidak query
 * berulang di satu halaman.
 */
class Setting extends Model
{
    private const CACHE = 'settings.values';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        if (!app()->bound(self::CACHE)) {
            // Aman dipanggil sebelum migrasi dijalankan (mis. saat instalasi awal).
            $values = Schema::hasTable('settings') ? static::query()->pluck('value', 'key')->all() : [];
            app()->instance(self::CACHE, $values);
        }

        return app(self::CACHE)[$key] ?? $default;
    }

    public static function put(string $key, $value): void
    {
        if ($value === null) {
            static::where('key', $key)->delete();
        } else {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        app()->forgetInstance(self::CACHE);
    }
}
