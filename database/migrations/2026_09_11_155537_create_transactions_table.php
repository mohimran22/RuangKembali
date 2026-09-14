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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('transaction_code')->unique();
            $table->foreignUuid('event_id')->constrained('events');
            $table->foreignUuid('registered_by')->constrained('users'); // yang submit form
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('payment_method')->nullable(); // transfer | gateway
            $table->string('proof_of_payment')->nullable(); // path file bukti transfer
            $table->string('status')->default('pending'); // pending | paid | rejected | expired
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
