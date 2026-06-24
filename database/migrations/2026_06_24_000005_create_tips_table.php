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
        Schema::create('tips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('waiter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->foreignUuid('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('note')->nullable();
            $table->timestamp('received_at');
            $table->timestamps();

            $table->index(['waiter_id', 'received_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tips');
    }
};
