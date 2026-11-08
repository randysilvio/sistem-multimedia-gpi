<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Pengaturan layar live per jadwal: desain latar, ukuran layar fisik & batas (cm),
            // area judul atas, dan ukuran huruf manual per slide.
            $table->json('display_settings')->nullable()->after('theme_color');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('display_settings');
        });
    }
};
