<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menambahkan kolom warna bawaan di tabel liturgies
        Schema::table('liturgies', function (Blueprint $table) {
            $table->string('default_color')->default('#198754'); // Default Hijau GPI
        });

        // Menambahkan kolom warna tema yang bisa diubah operator di tabel schedules
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('theme_color')->default('#198754');
        });
    }

    public function down(): void
    {
        Schema::table('liturgies', function (Blueprint $table) {
            $table->dropColumn('default_color');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('theme_color');
        });
    }
};