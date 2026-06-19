<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ingredient_id')->constrained()->cascadeOnDelete();
            // 'in' = penambahan stok, 'out' = pemakaian, 'adjustment' = koreksi manual
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->decimal('qty_before', 10, 3);
            $table->decimal('qty_change', 10, 3); // positif = tambah, negatif = kurang
            $table->decimal('qty_after', 10, 3);
            $table->nullableMorphs('reference'); // morphable ke Payment, dll
            $table->string('notes', 255)->nullable();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
