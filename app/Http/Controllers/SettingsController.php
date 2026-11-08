<?php

namespace App\Http\Controllers;

use App\Services\ActivityLog;
use App\Models\Setting;
use App\Services\Branding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit', [
            'logoUrl' => Branding::logoUrl(),
            'hasUploadedLogo' => Branding::hasUploadedLogo(),
            'videoUrl' => Branding::videoUrl(),
            'hasUploadedVideo' => Branding::hasUploadedVideo(),
            'churchName' => Branding::churchName(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'church_name' => 'nullable|string|max:120',
            // SVG sengaja ditolak: file SVG dapat berisi script.
            'logo' => 'nullable|file|mimes:png,jpg,jpeg,webp|mimetypes:image/png,image/jpeg,image/webp|max:4096',
            'remove_logo' => 'nullable|boolean',
            'background_video' => 'nullable|file|mimes:mp4,webm|mimetypes:video/mp4,video/webm|max:38912',
            'remove_video' => 'nullable|boolean',
        ], [
            'background_video.mimes' => 'Video latar harus MP4 atau WEBM.',
            'background_video.mimetypes' => 'Video latar harus MP4 atau WEBM.',
            'background_video.max' => 'Ukuran video latar maksimal 38 MB.',
            'logo.mimes' => 'Logo harus berupa gambar PNG, JPG, atau WEBP.',
            'logo.mimetypes' => 'Logo harus berupa gambar PNG, JPG, atau WEBP.',
            'logo.max' => 'Ukuran logo maksimal 4 MB.',
        ]);

        Setting::put('church_name', trim((string) ($data['church_name'] ?? '')) ?: null);

        $old = Setting::get('logo_path');

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $path = $file->storeAs('branding', 'logo-' . now()->format('YmdHis') . '.' . strtolower($file->extension()), 'public');
            Setting::put('logo_path', $path);
            $this->deleteLogo($old, $path);
            $message = 'Logo baru berhasil diunggah dan langsung dipakai di semua layar.';
        } elseif ($request->boolean('remove_logo')) {
            Setting::put('logo_path', null);
            $this->deleteLogo($old);
            $message = 'Logo unggahan dihapus. Aplikasi kembali memakai logo bawaan.';
        } else {
            $message = 'Pengaturan berhasil disimpan.';
        }

        $oldVideo = Setting::get('background_video');
        if ($request->hasFile('background_video')) {
            $video = $request->file('background_video');
            $path = $video->storeAs('branding', 'latar-' . now()->format('YmdHis') . '.' . strtolower($video->extension()), 'public');
            Setting::put('background_video', $path);
            if ($oldVideo && $oldVideo !== $path) {
                Storage::disk('public')->delete($oldVideo);
            }
            $message .= ' Video latar baru dipakai untuk pilihan latar "Video".';
        } elseif ($request->boolean('remove_video') && $oldVideo) {
            Setting::put('background_video', null);
            Storage::disk('public')->delete($oldVideo);
            $message .= ' Video latar unggahan dihapus.';
        }

        ActivityLog::record('pengaturan.ubah', null, 'Mengubah pengaturan gereja / logo / video latar');
        return redirect()->route('settings.edit')->with('success', $message);
    }

    private function deleteLogo(?string $path, ?string $keep = null): void
    {
        if ($path && $path !== $keep && str_starts_with($path, 'branding/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
