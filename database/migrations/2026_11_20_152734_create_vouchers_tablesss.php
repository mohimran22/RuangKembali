<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_vouchers', function (Blueprint $table) {
            if (! Schema::hasColumn('event_vouchers', 'voucher_number')) {
                $table->string('voucher_number', 20)->nullable()->unique();
            }
            if (! Schema::hasColumn('event_vouchers', 'name')) {
                $table->string('name', 150)->nullable();
            }
            if (! Schema::hasColumn('event_vouchers', 'type')) {
                $table->string('type', 10)->default('percent');       // percent | fixed
            }
            if (! Schema::hasColumn('event_vouchers', 'value')) {
                $table->decimal('value', 15, 2)->default(0);
            }
            if (! Schema::hasColumn('event_vouchers', 'max_discount')) {
                $table->decimal('max_discount', 15, 2)->nullable();
            }
            if (! Schema::hasColumn('event_vouchers', 'quota')) {
                $table->unsignedInteger('quota')->nullable();
            }
            if (! Schema::hasColumn('event_vouchers', 'per_user_limit')) {
                $table->unsignedInteger('per_user_limit')->nullable();
            }
            if (! Schema::hasColumn('event_vouchers', 'valid_from')) {
                $table->timestamp('valid_from')->nullable();
            }
            if (! Schema::hasColumn('event_vouchers', 'valid_until')) {
                $table->timestamp('valid_until')->nullable();
            }
            if (! Schema::hasColumn('event_vouchers', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        // "Semua Event" = event_id kosong
        DB::statement('ALTER TABLE event_vouchers ALTER COLUMN event_id DROP NOT NULL');

        // Isi Id Voucher dan nama untuk data lama
        $n = 0;
        DB::table('event_vouchers')->orderBy('created_at')->get(['id', 'code'])->each(function ($row) use (&$n) {
            $n++;
            DB::table('event_vouchers')->where('id', $row->id)->update([
                'voucher_number' => 'VCH-' . str_pad($n, 4, '0', STR_PAD_LEFT),
                'name'           => DB::raw("COALESCE(name, '" . str_replace("'", "''", (string) $row->code) . "')"),
            ]);
        });
    }

    public function down(): void
    {
        // Sengaja kosong: kolom tidak dihapus agar data voucher tidak hilang
    }
};
