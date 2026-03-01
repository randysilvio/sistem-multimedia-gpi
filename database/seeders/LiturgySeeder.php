<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Liturgy;
use App\Models\LiturgyItem;
use Illuminate\Support\Facades\Schema;

class LiturgySeeder extends Seeder
{
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        LiturgyItem::truncate();
        Liturgy::truncate();
        Schema::enableForeignKeyConstraints();

        // Tidak ada input otomatis. Database bersih, siap menerima template buatan Anda.
    }
}