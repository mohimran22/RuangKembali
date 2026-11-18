<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index untuk tabel analytics website.
 *
 * - unique visitor_key & session_key: wajib agar insertOrIgnore() benar-benar
 *   mencegah visitor/sesi ganda saat dua request datang bersamaan.
 * - index viewed_at / started_at / session_id / visitor_id: mempercepat query dashboard.
 *
 * SEBELUM migrate, pastikan belum ada data ganda (kalau ada, unique index gagal):
 *   select visitor_key, count(*) from website_visitors group by 1 having count(*) > 1;
 *   select session_key, count(*) from website_sessions group by 1 having count(*) > 1;
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_visitors', function (Blueprint $table) {
            if (! $this->indexExists('website_visitors', 'website_visitors_visitor_key_unique')) {
                $table->unique('visitor_key');
            }
        });

        Schema::table('website_sessions', function (Blueprint $table) {
            if (! $this->indexExists('website_sessions', 'website_sessions_session_key_unique')) {
                $table->unique('session_key');
            }
            if (! $this->indexExists('website_sessions', 'website_sessions_started_at_index')) {
                $table->index('started_at');
            }
        });

        Schema::table('website_page_views', function (Blueprint $table) {
            if (! $this->indexExists('website_page_views', 'website_page_views_viewed_at_index')) {
                $table->index('viewed_at');
            }
            if (! $this->indexExists('website_page_views', 'website_page_views_session_id_index')) {
                $table->index('session_id');
            }
            if (! $this->indexExists('website_page_views', 'website_page_views_visitor_id_index')) {
                $table->index('visitor_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('website_visitors', function (Blueprint $table) {
            $table->dropUnique('website_visitors_visitor_key_unique');
        });

        Schema::table('website_sessions', function (Blueprint $table) {
            $table->dropUnique('website_sessions_session_key_unique');
            $table->dropIndex('website_sessions_started_at_index');
        });

        Schema::table('website_page_views', function (Blueprint $table) {
            $table->dropIndex('website_page_views_viewed_at_index');
            $table->dropIndex('website_page_views_session_id_index');
            $table->dropIndex('website_page_views_visitor_id_index');
        });
    }

    private function indexExists(string $table, string $name): bool
    {
        try {
            return collect(Schema::getIndexes($table))
                ->contains(fn ($index) => $index['name'] === $name);
        } catch (\Throwable) {
            return false;
        }
    }
};