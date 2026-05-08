<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->foreignUuid('table_status_id')->nullable()->after('status')->constrained('table_statuses')->nullOnDelete();
            $table->foreignUuid('table_category_id')->nullable()->after('table_status_id')->constrained('table_categories')->nullOnDelete();
        });

        $defaultStatuses = [
            ['key' => 'available', 'name' => 'Tersedia', 'color' => 'success', 'sort_order' => 1, 'is_default' => true],
            ['key' => 'occupied', 'name' => 'Terisi', 'color' => 'error', 'sort_order' => 2, 'is_default' => false],
            ['key' => 'order_in', 'name' => 'Pesanan Masuk', 'color' => 'warning', 'sort_order' => 3, 'is_default' => false],
            ['key' => 'cleaning', 'name' => 'Perlu Dibersihkan', 'color' => 'info', 'sort_order' => 4, 'is_default' => false],
        ];

        $statusIdMap = [];

        foreach ($defaultStatuses as $status) {
            $id = (string) Str::uuid();

            DB::table('table_statuses')->insert([
                'id' => $id,
                'key' => $status['key'],
                'name' => $status['name'],
                'color' => $status['color'],
                'sort_order' => $status['sort_order'],
                'is_active' => true,
                'is_default' => $status['is_default'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $statusIdMap[$status['key']] = $id;
        }

        foreach ($statusIdMap as $statusKey => $statusId) {
            DB::table('tables')
                ->where('status', $statusKey)
                ->update(['table_status_id' => $statusId]);
        }

        if (isset($statusIdMap['available'])) {
            DB::table('tables')
                ->whereNull('table_status_id')
                ->update(['table_status_id' => $statusIdMap['available']]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeign(['table_status_id']);
            $table->dropForeign(['table_category_id']);
            $table->dropColumn(['table_status_id', 'table_category_id']);
        });
    }
};

