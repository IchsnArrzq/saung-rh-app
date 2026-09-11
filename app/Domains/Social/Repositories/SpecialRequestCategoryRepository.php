<?php

namespace App\Domains\Social\Repositories;

use App\Models\SpecialRequestCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class SpecialRequestCategoryRepository
{
    /**
     * Postgres menolak string non-UUID di kolom uuid dengan error, bukan hasil
     * kosong — id dari form tamu harus disaring dulu.
     */
    public function find(string $id): ?SpecialRequestCategory
    {
        return Str::isUuid($id) ? SpecialRequestCategory::query()->find($id) : null;
    }

    public function findActive(string $id): ?SpecialRequestCategory
    {
        return Str::isUuid($id) ? SpecialRequestCategory::query()->active()->find($id) : null;
    }

    /**
     * Pilihan di panel meja tamu.
     *
     * @return Collection<int, SpecialRequestCategory>
     */
    public function active(): Collection
    {
        return SpecialRequestCategory::query()->active()->ordered()->get();
    }

    public function paginateForAdmin(string $search = '', int $perPage = 12): LengthAwarePaginator
    {
        $search = trim($search);

        return SpecialRequestCategory::query()
            ->withCount('specialRequests')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->whereLike('name', '%'.$search.'%')
                        ->orWhereLike('description', '%'.$search.'%');
                });
            })
            ->ordered()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function slugTaken(string $slug, ?string $ignoreId = null): bool
    {
        return SpecialRequestCategory::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    public function nameTaken(string $name, ?string $ignoreId = null): bool
    {
        return SpecialRequestCategory::query()
            ->whereRaw('lower(name) = ?', [mb_strtolower(trim($name))])
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    public function nextSortOrder(): int
    {
        return (int) SpecialRequestCategory::query()->max('sort_order') + 10;
    }

    public function requestCount(SpecialRequestCategory $category): int
    {
        return $category->specialRequests()->count();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): SpecialRequestCategory
    {
        return SpecialRequestCategory::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(SpecialRequestCategory $category, array $attributes): SpecialRequestCategory
    {
        $category->update($attributes);

        return $category;
    }

    public function delete(SpecialRequestCategory $category): void
    {
        $category->delete();
    }
}
