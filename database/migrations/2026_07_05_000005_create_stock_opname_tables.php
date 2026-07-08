<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Physical stock count (opname) as a header + item document, mirroring the
 * other inventory documents. Creating a draft snapshots the current stock of
 * each ingredient; posting adjusts the ingredient stock to the counted amount
 * and records the difference as a stock movement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 40)->nullable()->unique();
            $table->date('opname_date');
            $table->enum('status', ['draft', 'posted'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->foreignUuid('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('stock_opname_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('system_qty', 10, 3)->default(0);  // snapshot at draft time
            $table->decimal('physical_qty', 10, 3)->nullable(); // counted amount
            $table->decimal('difference', 10, 3)->default(0);   // physical - system
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['stock_opname_id', 'ingredient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
    }
};
