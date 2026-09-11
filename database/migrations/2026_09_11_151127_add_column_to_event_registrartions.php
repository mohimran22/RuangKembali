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
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->foreignUuid('registered_by')->nullable()->constrained('users');
            $table->string('payment_method')->nullable();
            $table->decimal('price', 12, 2)->default(0); // snapshot harga saat mendaftar
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            //
        });
    }
};
