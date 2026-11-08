<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tanda "lirik belum lengkap" (mis. lagu DSL/GPM yang baitnya hanya dari buletin). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->boolean('is_incomplete')->default(false)->after('chorus');
        });
    }

    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->dropColumn('is_incomplete');
        });
    }
};
