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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignUuid('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->string('order_number')->nullable()->unique();
            $table->string('customer_name')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'preparing', 'ready', 'served', 'paid', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamp('ordered_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->dropUnique(['order_number']);
            $table->dropColumn([
                'table_id',
                'order_number',
                'customer_name',
                'status',
                'notes',
                'subtotal',
                'discount',
                'tax',
                'total',
                'ordered_at',
            ]);
        });
    }
};
