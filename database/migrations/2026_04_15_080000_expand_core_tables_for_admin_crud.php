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
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->foreignId('menu_category_id')->nullable()->constrained('menu_categories')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->string('sku')->nullable()->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('image_url')->nullable();
            $table->boolean('is_available')->default(true);
        });

        Schema::table('tables', function (Blueprint $table) {
            $table->string('code')->nullable()->unique();
            $table->string('name')->nullable();
            $table->unsignedInteger('capacity')->default(4);
            $table->enum('status', ['available', 'occupied', 'order_in', 'cleaning'])->default('available');
            $table->text('notes')->nullable();
        });

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

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignUuid('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('menu_id')->nullable()->constrained('menus')->nullOnDelete();
            $table->string('menu_name_snapshot')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['menu_id']);
            $table->dropColumn(['order_id', 'menu_id', 'menu_name_snapshot', 'qty', 'price', 'line_total', 'notes']);
        });

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

        Schema::table('tables', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'name', 'capacity', 'status', 'notes']);
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropForeign(['menu_category_id']);
            $table->dropUnique(['slug']);
            $table->dropUnique(['sku']);
            $table->dropColumn([
                'menu_category_id',
                'name',
                'slug',
                'sku',
                'description',
                'price',
                'image_url',
                'is_available',
            ]);
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['name', 'slug', 'description', 'is_active']);
        });
    }
};
