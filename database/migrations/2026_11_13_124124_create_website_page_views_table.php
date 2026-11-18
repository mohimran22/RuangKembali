
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_page_views', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('visitor_id')
                ->constrained('website_visitors')
                ->cascadeOnDelete();

            $table->foreignUuid('session_id')
                ->constrained('website_sessions')
                ->cascadeOnDelete();

            $table->text('path');
            $table->string('title', 500)->nullable();
            $table->text('referrer')->nullable();

            $table->timestampTz('viewed_at');

            // Durasi akan diisi melalui mekanisme tracking berikutnya
            $table->integer('duration_seconds')->nullable();

            $table->timestampsTz();

            $table->index(['viewed_at', 'path']);
            $table->index(['session_id', 'viewed_at']);
            $table->index(['visitor_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_page_views');
    }
};
