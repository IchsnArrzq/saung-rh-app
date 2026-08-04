<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Step 2: the code now reads `menus.status`, so the lookup table and its
 * foreign key can go.
 *
 * `down()` rebuilds the table, re-seeds the four rows and relinks every menu
 * from the `status` column, keeping the pair fully reversible.
 */
return new class extends Migration
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private const SEEDED_STATUSES = [
        ['key' => 'available', 'name' => 'Tersedia', 'color' => 'success', 'sort_order' => 1, 'is_default' => true],
        ['key' => 'unavailable', 'name' => 'Tidak Tersedia', 'color' => 'error', 'sort_order' => 2, 'is_default' => false],
        ['key' => 'sold_out', 'name' => 'Habis', 'color' => 'warning', 'sort_order' => 3, 'is_default' => false],
        ['key' => 'seasonal', 'name' => 'Musiman', 'color' => 'info', 'sort_order' => 4, 'is_default' => false],
    ];

    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table): void {
            $table->dropForeign(['menu_status_id']);
            $table->dropColumn('menu_status_id');
        });

        Schema::dropIfExists('menu_statuses');
    }

    public function down(): void
    {
        Schema::create('menu_statuses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('color')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        foreach (self::SEEDED_STATUSES as $status) {
            DB::table('menu_statuses')->insert($status + [
                'id' => (string) Str::uuid(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('menus', function (Blueprint $table): void {
            $table->uuid('menu_status_id')->nullable()->index()->after('price');
            $table->foreign('menu_status_id')->references('id')->on('menu_statuses')->nullOnDelete();
        });

        DB::statement('
            UPDATE menus
            SET menu_status_id = menu_statuses.id
            FROM menu_statuses
            WHERE menus.status = menu_statuses.key
        ');
    }
};
