<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('slug')->nullable();
        });

        // Isi slug untuk event yang sudah ada (termasuk yang sudah dihapus lunak)
        $used = [];
        DB::table('events')->orderBy('created_at')->get(['id', 'name'])->each(function ($e) use (&$used) {
            $base = Str::slug($e->name) ?: 'event';
            $slug = $base;
            $i = 2;
            while (in_array($slug, $used)) {
                $slug = $base . '-' . $i++;
            }
            $used[] = $slug;

            DB::table('events')->where('id', $e->id)->update(['slug' => $slug]);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
