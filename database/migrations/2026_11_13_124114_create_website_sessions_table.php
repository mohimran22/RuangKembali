
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('visitor_id')
                ->constrained('website_visitors')
                ->cascadeOnDelete();

            $table->uuid('session_key')->unique();

            $table->text('landing_page')->nullable();
            $table->text('referrer')->nullable();

            $table->string('source', 255)->nullable();
            $table->string('medium', 255)->nullable();
            $table->string('campaign', 255)->nullable();

            $table->timestampTz('started_at');
            $table->timestampTz('last_activity_at');
            $table->timestampTz('ended_at')->nullable();

            $table->timestampsTz();

            $table->index(['visitor_id', 'started_at']);
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_sessions');
    }
};
