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
            $table->string('transfer_bank_name', 100)->nullable();
            $table->string('transfer_account_number', 50)->nullable();
            $table->string('transfer_account_holder', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'transfer_bank_name',
                'transfer_account_number',
                'transfer_account_holder',
            ]);
        });
    }
};
