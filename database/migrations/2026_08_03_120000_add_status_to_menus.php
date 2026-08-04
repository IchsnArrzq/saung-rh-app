<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Step 1 of moving menu availability from a database table to a hardcoded Enum
 * (App\Domains\Menu\Enums\MenuAvailability).
 *
 * Additive on purpose: `menu_status_id` stays so this is reversible. A second
 * migration drops it once the code no longer reads it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table): void {
            $table->string('status')->nullable()->index()->after('price');
        });

        DB::statement('
            UPDATE menus
            SET status = menu_statuses.key
            FROM menu_statuses
            WHERE menus.menu_status_id = menu_statuses.id
        ');

        DB::table('menus')->whereNull('status')->update(['status' => 'available']);
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
