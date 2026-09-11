<?php

namespace App\Domains\Social\QueryUseCases;

use App\Domains\Social\Repositories\SpecialRequestCategoryRepository;
use App\Models\SpecialRequestCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Master kategori permintaan khusus: pilihan aktif untuk tamu, dan daftar
 * lengkap untuk layar admin.
 */
class GetSpecialRequestCategoriesQueryUseCase
{
    public function __construct(private readonly SpecialRequestCategoryRepository $categories) {}

    /**
     * @return Collection<int, SpecialRequestCategory>
     */
    public function active(): Collection
    {
        return $this->categories->active();
    }

    public function findActive(string $id): ?SpecialRequestCategory
    {
        return $this->categories->findActive($id);
    }

    public function find(string $id): ?SpecialRequestCategory
    {
        return $this->categories->find($id);
    }

    public function paginate(string $search = '', int $perPage = 12): LengthAwarePaginator
    {
        return $this->categories->paginateForAdmin($search, $perPage);
    }
}
