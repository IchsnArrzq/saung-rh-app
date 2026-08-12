<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Step 1 of moving table status from a database table to a hardcoded Enum
 * (App\Domains\Table\Enums\TableStatus).
 *
 * Deliberately additive: the `table_status_id` foreign key stays in place so
 * this migration can be rolled back without losing anything. A later migration
 * drops the FK and the `table_statuses` table once the code no longer reads it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table): void {
            $table->string('status')->nullable()->index()->after('capacity');
        });

        // Backfill from the related row's key — the value the app already
        // exposed through Table::getStatusAttribute().
        DB::statement('
            UPDATE tables
            SET status = table_statuses.key
            FROM table_statuses
            WHERE tables.table_status_id = table_statuses.id
        ');

        // Any table that never had a status falls back to the seeded default.
        DB::table('tables')->whereNull('status')->update(['status' => 'available']);
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
