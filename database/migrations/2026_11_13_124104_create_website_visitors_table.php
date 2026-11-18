
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_visitors', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // UUID anonim dari cookie first-party
            $table->uuid('visitor_key')->unique();

            $table->string('device_type', 20)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('operating_system', 100)->nullable();

            $table->timestampTz('first_seen_at');
            $table->timestampTz('last_seen_at');

            $table->timestampsTz();

            $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_visitors');
    }
};
