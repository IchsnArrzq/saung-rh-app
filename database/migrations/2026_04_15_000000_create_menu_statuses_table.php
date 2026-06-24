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
        Schema::create('menu_statuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key', 60)->unique();
            $table->string('name', 120);
            $table->string('color', 40)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->foreign('menu_status_id')->references('id')->on('menu_statuses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropForeign(['menu_status_id']);
        });

        Schema::dropIfExists('menu_statuses');
    }
};
