<?php

namespace App\Domains\Social\UseCases;

use App\Domains\Social\DTO\SpecialRequestCategoryData;
use App\Domains\Social\Repositories\SpecialRequestCategoryRepository;
use App\Models\SpecialRequestCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Tambah atau ubah satu kategori permintaan khusus.
 *
 * Nama harus unik tanpa membedakan huruf besar-kecil — dua pilihan "Perayaan"
 * di panel tamu hanya membingungkan. Slug dibuat sekali saat kategori lahir dan
 * tidak ikut berubah saat namanya diganti, supaya rujukan yang sudah ada (seeder
 * demo, migrasi) tetap menunjuk baris yang sama.
 */
class SaveSpecialRequestCategoryUseCase
{
    public function __construct(private readonly SpecialRequestCategoryRepository $categories) {}

    public function handle(SpecialRequestCategoryData $data, ?SpecialRequestCategory $category = null): SpecialRequestCategory
    {
        $name = trim($data->name);

        if ($this->categories->nameTaken($name, $category?->id)) {
            throw ValidationException::withMessages([
                'name' => 'Sudah ada kategori bernama "'.$name.'". Pakai nama lain atau ubah kategori yang ada.',
            ]);
        }

        $attributes = [
            'name' => $name,
            'icon' => $data->icon ?: null,
            'description' => filled($data->description) ? trim((string) $data->description) : null,
            'is_active' => $data->isActive,
            'sort_order' => $data->sortOrder ?? ($category?->sort_order ?? $this->categories->nextSortOrder()),
        ];

        return DB::transaction(function () use ($category, $attributes, $name): SpecialRequestCategory {
            if ($category) {
                return $this->categories->update($category, $attributes);
            }

            return $this->categories->create([...$attributes, 'slug' => $this->uniqueSlug($name)]);
        });
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $suffix = 2;

        while ($this->categories->slugTaken($slug)) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
