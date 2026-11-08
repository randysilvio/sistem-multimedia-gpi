<?php

namespace App\Services;

use App\Models\ActivityLog as Entry;
use Illuminate\Database\Eloquent\Model;

/**
 * Catat perubahan penting untuk menu "Riwayat". Tidak pernah menggagalkan
 * proses utama: bila pencatatan gagal (mis. tabel belum dimigrasi), diabaikan.
 */
class ActivityLog
{
    public static function record(string $action, ?Model $subject, string $description): void
    {
        try {
            // Simpan otomatis (Kontrol Live) bisa terjadi tiap beberapa detik: satu catatan
            // per orang + perubahan yang sama dalam 10 menit sudah cukup.
            $recent = Entry::where('user_id', auth()->id())->latest('id')->first();
            if ($recent && $recent->action === $action && $recent->subject_type === ($subject ? class_basename($subject) : null)
                && (string) $recent->subject_id === (string) $subject?->getKey() && $recent->description === mb_substr($description, 0, 500)
                && $recent->created_at?->gt(now()->subMinutes(10))) {
                return;
            }

            Entry::create([
                'user_id' => auth()->id(),
                'action' => mb_substr($action, 0, 40),
                'subject_type' => $subject ? class_basename($subject) : null,
                'subject_id' => $subject?->getKey(),
                'description' => mb_substr($description, 0, 500),
                'ip' => request()?->ip(),
            ]);
        } catch (\Throwable) {
            // riwayat bersifat pelengkap
        }
    }
}
