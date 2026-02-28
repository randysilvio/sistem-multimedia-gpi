<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liturgy_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('liturgy_id')->constrained()->cascadeOnDelete();
            $table->integer('order_number');
            $table->string('title'); 
            $table->boolean('is_dynamic')->default(false);
            $table->text('static_content')->nullable();
            $table->string('placeholder_tag')->nullable();
            
            // Kolom baru untuk Aksi Jemaat (Berdiri/Duduk)
            $table->string('congregation_action')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liturgy_items');
    }
};