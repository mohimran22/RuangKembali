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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('registration_type', 20)->default('regular')->after('payment_method');
            $table->string('complimentary_note')->nullable()->after('registration_type'); // mis. "Sponsor: PT ABC"
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('registration_type');
            $table->dropColumn('complimentary_note');
        });
    }
};
