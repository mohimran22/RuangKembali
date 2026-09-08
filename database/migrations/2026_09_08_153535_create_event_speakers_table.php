<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_speakers', function (Blueprint $table) {
            $table->id();

            $table->foreignUuid('event_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignUuid('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->timestamps();

            // Cegah duplikat: 1 speaker cuma bisa terdaftar sekali per event
            $table->unique(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_speakers');
    }
};