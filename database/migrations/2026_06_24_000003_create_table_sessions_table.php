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
        Schema::create('table_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('table_id')->constrained('tables')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->enum('visibility', ['private', 'public'])->default('private');
            $table->boolean('is_anonymous')->default(false);
            $table->unsignedInteger('pax')->nullable();
            $table->string('customer_name')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['table_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_sessions');
    }
};
