<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * The original "stock_opnames" table was actually a stock-movement ledger
 * (in / out / adjustment rows). It is renamed to "stock_movements" so the
 * "stock_opnames" name can be reused for a proper header/detail physical count.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_opnames') && ! Schema::hasTable('stock_movements')) {
            Schema::rename('stock_opnames', 'stock_movements');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stock_movements') && ! Schema::hasTable('stock_opnames')) {
            Schema::rename('stock_movements', 'stock_opnames');
        }
    }
};
