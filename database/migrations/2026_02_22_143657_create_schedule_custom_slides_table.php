<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('schedule_custom_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            // Penanda: Slide ini akan disisipkan SETELAH item liturgi ini
            $table->foreignId('liturgy_item_id')->constrained()->cascadeOnDelete(); 
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('schedule_custom_slides');
    }
};