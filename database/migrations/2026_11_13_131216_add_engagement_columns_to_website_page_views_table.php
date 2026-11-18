<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('website_page_views', function (Blueprint $table) {
        $table->integer('active_seconds')->default(0);
        $table->integer('max_scroll_percent')->default(0);
        $table->integer('interaction_count')->default(0);
        $table->boolean('is_engaged')->default(false);
        $table->timestampTz('last_activity_at')->nullable();
    });
}

public function down(): void
{
    Schema::table('website_page_views', function (Blueprint $table) {
        $table->dropColumn([
            'active_seconds',
            'max_scroll_percent',
            'interaction_count',
            'is_engaged',
            'last_activity_at',
        ]);
    });
}
};
