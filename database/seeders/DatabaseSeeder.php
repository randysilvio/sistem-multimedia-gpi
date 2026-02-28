<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Membuat akun Admin Default
        User::factory()->create([
            'name' => 'Admin Multimedia',
            'email' => 'admin@gpipapua.org',
            'password' => Hash::make('password123'), // Ganti dengan password yang Anda inginkan
        ]);

        // Memanggil seeder liturgi yang sudah kita buat sebelumnya
        $this->call([
            LiturgySeeder::class,
        ]);
    }
}