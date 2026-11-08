<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            // Nomor bait asli, sejajar dengan kolom `verses` (mis. [1, 7]).
            // NULL = bait berurutan 1, 2, 3, ...
            $table->json('verse_numbers')->nullable()->after('verses');
        });
    }

    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->dropColumn('verse_numbers');
        });
    }
};
