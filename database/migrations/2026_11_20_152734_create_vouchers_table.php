<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- transaksi: simpan voucher dan rincian potongan ---
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignUuid('voucher_id')->nullable()->constrained('event_vouchers')->nullOnDelete();
            $table->string('voucher_code', 50)->nullable();
            $table->decimal('subtotal_amount', 15, 2)->nullable();
            $table->decimal('discount_amount', 15, 2)->default(0);
        });

        // --- voucher: tambahkan hanya kolom yang belum ada ---
        Schema::table('event_vouchers', function (Blueprint $table) {
            if (! Schema::hasColumn('event_vouchers', 'type')) {
                $table->string('type', 10)->default('percent');          // percent | fixed
            }
            if (! Schema::hasColumn('event_vouchers', 'value')) {
                $table->decimal('value', 15, 2)->default(0);
            }
            if (! Schema::hasColumn('event_vouchers', 'max_discount')) {
                $table->decimal('max_discount', 15, 2)->nullable();      // batas potongan per tiket (untuk persen)
            }
            if (! Schema::hasColumn('event_vouchers', 'quota')) {
                $table->unsignedInteger('quota')->nullable();            // maksimal pemakaian (transaksi); null = tak terbatas
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
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voucher_id');
            $table->dropColumn(['voucher_code', 'subtotal_amount', 'discount_amount']);
        });
    }
};
