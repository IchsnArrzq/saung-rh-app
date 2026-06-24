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
        Schema::create('service_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('waiter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->foreignUuid('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->enum('type', ['greeting', 'refill', 'cleanup', 'special_request', 'other'])->default('other');
            $table->text('description')->nullable();
            $table->timestamp('served_at');
            $table->timestamps();

            $table->index(['waiter_id', 'served_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_logs');
    }
};
