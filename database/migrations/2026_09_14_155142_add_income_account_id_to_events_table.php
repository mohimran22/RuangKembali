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
        Schema::table('events', function (Blueprint $table) {

            $table->uuid('income_account_id')
                ->nullable()
                ->after('event_category_id');

            $table->foreign('income_account_id')
                ->references('id')
                ->on('accounting_accounts')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {

            $table->dropForeign([
                'income_account_id'
            ]);

            $table->dropColumn('income_account_id');
        });
    }
};
