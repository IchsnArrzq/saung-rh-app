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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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
    }
};
