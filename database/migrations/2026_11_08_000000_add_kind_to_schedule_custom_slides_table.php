<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slide sisipan bisa berupa teks biasa (kind = null) atau LAGU (kind = 'song',
 * content = JSON {judul, bait}): dipakai fitur "Lagu dadakan" di Kontrol Live.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_custom_slides', function (Blueprint $table) {
            $table->string('kind', 20)->nullable()->after('liturgy_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('schedule_custom_slides', function (Blueprint $table) {
            $table->dropColumn('kind');
        });
    }
};
