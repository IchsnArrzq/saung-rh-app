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
        Schema::table('tables', function (Blueprint $table) {
            $table->string('code')->nullable()->unique();
            $table->string('name')->nullable();
            $table->unsignedInteger('capacity')->default(4);
            $table->enum('status', ['available', 'occupied', 'order_in', 'cleaning'])->default('available');
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'name', 'capacity', 'status', 'notes']);
        });
    }
};
