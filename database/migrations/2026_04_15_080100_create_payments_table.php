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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->enum('method', ['cash', 'qris', 'debit_card', 'credit_card', 'transfer', 'ewallet'])->default('cash');
            $table->enum('type', ['full', 'dp', 'partial'])->default('full');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('paid');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
