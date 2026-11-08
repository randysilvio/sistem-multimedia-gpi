<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * Identitas gereja yang tampil di seluruh aplikasi & layar live.
 * Logo diambil berurutan dari: unggahan admin (Pengaturan) -> PRESENTATION_LOGO_URL
 * -> file public/images/logo.png -> lambang netral bawaan (tanpa ketergantungan situs luar).
 */
class Branding
{
    public const DEFAULT_NAME = 'GPI Papua';

    public static function logoUrl(): string
    {
        $uploaded = Setting::get('logo_path');
        if ($uploaded && Storage::disk('public')->exists($uploaded)) {
            return asset('storage/' . $uploaded);
        }
        if ($url = config('presentation.logo_url')) {
            return $url;
        }
        $file = config('presentation.logo_file');
        if ($file && is_file(public_path($file))) {
            return asset($file);
        }

        return asset(config('presentation.logo_default', 'images/logo-default.svg'));
    }

    public static function hasUploadedLogo(): bool
    {
        $path = Setting::get('logo_path');

        return $path && Storage::disk('public')->exists($path);
    }

    /** Video latar: hanya video yang diunggah di Pengaturan (public/video-gereja.mp4 adalah klip "matikan suara HP", bukan latar). */
    public static function videoUrl(): ?string
    {
        $uploaded = Setting::get('background_video');
        if ($uploaded && Storage::disk('public')->exists($uploaded)) {
            return asset('storage/' . $uploaded);
        }

        return null;
    }

    public static function hasUploadedVideo(): bool
    {
        $path = Setting::get('background_video');

        return $path && Storage::disk('public')->exists($path);
    }

    public static function churchName(): string
    {
        return Setting::get('church_name') ?: self::DEFAULT_NAME;
    }
}
