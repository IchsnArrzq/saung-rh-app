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
        Schema::create('reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->string('customer_name');
            $table->string('phone', 30)->nullable();
            $table->unsignedInteger('pax')->default(1);
            $table->timestamp('reservation_at');
            $table->enum('status', ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'])->default('pending');
            // Down payment / deposit hold (Fase 4)
            $table->decimal('deposit_amount', 12, 2)->nullable();
            $table->timestamp('deposit_paid_at')->nullable();
            $table->timestamp('hold_until')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->string('release_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'hold_until']);
            $table->index(['status', 'reservation_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
