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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['name', 'slug', 'description', 'is_active']);
        });
    }
};
