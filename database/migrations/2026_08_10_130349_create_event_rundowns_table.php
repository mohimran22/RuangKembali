<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_rundowns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')
                ->constrained('events')
                ->cascadeOnDelete();
            $table->date('rundown_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('activity');
            $table->text('description')->nullable();
            $table->string('speaker')->nullable();
            $table->string('location')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index([
                'event_id',
                'rundown_date',
                'sort_order'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_rundowns');
    }
};
