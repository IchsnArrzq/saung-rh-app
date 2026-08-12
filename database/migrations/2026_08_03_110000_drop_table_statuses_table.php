<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Step 2: the code now reads `tables.status`, so the old lookup table and its
 * foreign key can go.
 *
 * `down()` rebuilds the table, re-seeds the five rows and re-links every table
 * from the `status` column, so the pair of migrations is fully reversible.
 */
return new class extends Migration
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private const SEEDED_STATUSES = [
        ['key' => 'available', 'name' => 'Tersedia', 'color' => 'success', 'sort_order' => 1, 'is_default' => true],
        ['key' => 'occupied', 'name' => 'Terisi', 'color' => 'error', 'sort_order' => 2, 'is_default' => false],
        ['key' => 'order_in', 'name' => 'Pesanan Masuk', 'color' => 'warning', 'sort_order' => 3, 'is_default' => false],
        ['key' => 'reserved', 'name' => 'Direservasi', 'color' => 'secondary', 'sort_order' => 4, 'is_default' => false],
        ['key' => 'cleaning', 'name' => 'Perlu Dibersihkan', 'color' => 'info', 'sort_order' => 5, 'is_default' => false],
    ];

    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table): void {
            $table->dropForeign(['table_status_id']);
            $table->dropColumn('table_status_id');
        });

        Schema::dropIfExists('table_statuses');
    }

    public function down(): void
    {
        Schema::create('table_statuses', function (Blueprint $table): void {
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
            DB::table('table_statuses')->insert($status + [
                'id' => (string) Str::uuid(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('tables', function (Blueprint $table): void {
            $table->uuid('table_status_id')->nullable()->index()->after('capacity');
            $table->foreign('table_status_id')->references('id')->on('table_statuses')->nullOnDelete();
        });

        DB::statement('
            UPDATE tables
            SET table_status_id = table_statuses.id
            FROM table_statuses
            WHERE tables.status = table_statuses.key
        ');
    }
};
