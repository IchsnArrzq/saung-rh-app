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
        Schema::create('tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->nullable()->unique();
            $table->string('name')->nullable();
            $table->unsignedInteger('capacity')->default(4);
            $table->enum('status', ['available', 'occupied', 'order_in', 'cleaning'])->default('available');
            $table->uuid('table_status_id')->nullable();
            $table->uuid('table_category_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
