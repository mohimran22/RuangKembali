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
        Schema::table('accounting_journals', function (Blueprint $table) {
            $table->string('reference_code')->nullable()->after('journal_code');
            $table->uuid('transaction_id')->nullable()->after('reference_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounting_journals', function (Blueprint $table) {
            //
        });
    }
};
