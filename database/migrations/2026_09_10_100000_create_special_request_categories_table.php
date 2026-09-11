<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Kategori permintaan khusus pindah dari enum yang dikodekan (service, kitchen,
 * ambience, celebration, other) ke master data yang bisa ditambah, diubah, dan
 * dinonaktifkan dari layar admin. Baris lama dipetakan ke kategori bawaan yang
 * setara, lalu kolom enum-nya dibuang.
 *
 * Sekalian `staff_note`: catatan staf saat menangani atau menolak permintaan.
 * Alur persetujuan manajer dihapus, jadi catatan inilah jejak "kenapa" yang
 * dibaca tamu di panel mejanya.
 */
return new class extends Migration
{
    /**
     * Kategori bawaan. `legacy` = nilai enum lama yang dipetakan ke baris ini.
     */
    private const DEFAULTS = [
        ['slug' => 'pelayanan', 'name' => 'Pelayanan', 'icon' => 'ri-service-line', 'legacy' => 'service'],
        ['slug' => 'dapur', 'name' => 'Dapur', 'icon' => 'ri-restaurant-2-line', 'legacy' => 'kitchen'],
        ['slug' => 'suasana', 'name' => 'Suasana', 'icon' => 'ri-music-2-line', 'legacy' => 'ambience'],
        ['slug' => 'perayaan', 'name' => 'Perayaan', 'icon' => 'ri-cake-3-line', 'legacy' => 'celebration'],
        ['slug' => 'lainnya', 'name' => 'Lainnya', 'icon' => 'ri-more-line', 'legacy' => 'other'],
    ];

    public function up(): void
    {
        Schema::create('special_request_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 80);
            $table->string('slug', 100)->unique();
            $table->string('icon', 60)->nullable();
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('special_requests', function (Blueprint $table) {
            $table->foreignUuid('special_request_category_id')->nullable()
                ->constrained('special_request_categories')->nullOnDelete();
            $table->text('staff_note')->nullable();
        });

        $now = now();

        foreach (self::DEFAULTS as $index => $row) {
            $id = (string) Str::uuid();

            DB::table('special_request_categories')->insert([
                'id' => $id,
                'name' => $row['name'],
                'slug' => $row['slug'],
                'icon' => $row['icon'],
                'is_active' => true,
                'sort_order' => ($index + 1) * 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('special_requests')
                ->where('category', $row['legacy'])
                ->update(['special_request_category_id' => $id]);
        }

        Schema::table('special_requests', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('special_requests', function (Blueprint $table) {
            $table->enum('category', ['service', 'kitchen', 'ambience', 'celebration', 'other'])->default('other');
        });

        $legacyBySlug = array_column(self::DEFAULTS, 'legacy', 'slug');

        foreach (DB::table('special_request_categories')->get(['id', 'slug']) as $category) {
            DB::table('special_requests')
                ->where('special_request_category_id', $category->id)
                ->update(['category' => $legacyBySlug[$category->slug] ?? 'other']);
        }

        Schema::table('special_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('special_request_category_id');
            $table->dropColumn('staff_note');
        });

        Schema::dropIfExists('special_request_categories');
    }
};
