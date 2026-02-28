<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('book')->nullable(); // Contoh: KJ, NKB, PKJ, Bebas
            $table->string('number')->nullable(); // Contoh: 15, 20A
            $table->string('title'); // Judul Lagu
            $table->json('verses'); // Array untuk menyimpan bait-bait
            $table->text('chorus')->nullable(); // Teks Reff (opsional)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('songs');
    }
};