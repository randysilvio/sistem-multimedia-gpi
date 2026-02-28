<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Memanggil LiturgySeeder untuk mengisi kerangka liturgi
        $this->call([
            LiturgySeeder::class,
        ]);
    }
}