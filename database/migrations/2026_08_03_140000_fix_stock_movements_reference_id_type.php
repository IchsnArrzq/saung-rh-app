<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fixes a latent schema bug: `stock_movements.reference_id` was created by
 * `$table->nullableMorphs('reference')`, which makes a **bigint** column — but
 * every model it points at (Payment, Purchase, Sale, StockOpname) uses UUID
 * keys. It should have been `nullableUuidMorphs`.
 *
 * The result was that any stock movement carrying a reference threw
 * "invalid input syntax for type bigint". It never surfaced because the
 * database has no ingredients or menu recipes yet, so
 * InventoryService::deductFromPayment always looped zero times — the automatic
 * stock deduction would have failed the first time a recipe existed.
 *
 * Safe to change outright: the table has no rows.
 */
return new class extends Migration
{
    /**
     * The table was renamed from `stock_opnames`, so its index kept the old
     * name — drop it explicitly rather than by column convention.
     */
    private const LEGACY_INDEX = 'stock_opnames_reference_type_reference_id_index';

    public function up(): void
    {
        DB::statement('DROP INDEX IF EXISTS '.self::LEGACY_INDEX);

        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->dropColumn('reference_id');
        });

        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->uuid('reference_id')->nullable()->after('reference_type');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->dropIndex(['reference_type', 'reference_id']);
            $table->dropColumn('reference_id');
        });

        Schema::table('stock_movements', function (Blueprint $table): void {
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            $table->index(['reference_type', 'reference_id']);
        });
    }
};
