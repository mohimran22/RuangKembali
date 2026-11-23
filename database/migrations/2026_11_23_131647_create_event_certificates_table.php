
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_certificates', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('event_id')
                ->constrained('events')
                ->cascadeOnDelete();

            $table->foreignUuid('registration_id')
                ->constrained('event_registrations')
                ->cascadeOnDelete();

            $table->foreignUuid('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('certificate_number')->unique();

            $table->string('file_path')->nullable();

            $table->timestamp('issued_at')->nullable();

            $table->timestamps();

            // Satu registrasi hanya boleh memiliki satu sertifikat.
            $table->unique('registration_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_certificates');
    }
};
