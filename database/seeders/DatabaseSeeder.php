<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Data awal. AMAN dijalankan berulang dan di server produksi:
 * tidak ada tabel yang dikosongkan, data yang sudah ada tidak ditimpa.
 *
 *   php artisan db:seed --force
 *
 * Akun admin pertama dibuat dari .env (ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD).
 * Bila ADMIN_PASSWORD kosong, kata sandi acak dibuat dan DITAMPILKAN sekali di terminal.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() === 0) {
            $password = env('ADMIN_PASSWORD') ?: Str::password(14, symbols: false);
            $user = new User([
                'name' => env('ADMIN_NAME', 'Admin Multimedia'),
                'email' => env('ADMIN_EMAIL', 'admin@gpipapua.org'),
                'password' => Hash::make($password),
            ]);
            $user->email_verified_at = now();
            $user->save();

            $this->command?->warn("Akun admin dibuat: {$user->email}");
            if (!env('ADMIN_PASSWORD')) {
                $this->command?->warn("Kata sandi sementara: {$password}  (segera ganti di menu Profil Saya)");
            }
        }

        $this->call([
            TemplateIbadahMingguSeeder::class,
            LaguIbadahMingguSeeder::class,
        ]);
    }
}
