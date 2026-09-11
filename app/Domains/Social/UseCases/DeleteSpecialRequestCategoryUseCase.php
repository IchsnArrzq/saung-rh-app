<?php

namespace App\Domains\Social\UseCases;

use App\Domains\Social\Repositories\SpecialRequestCategoryRepository;
use App\Models\SpecialRequestCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Hapus kategori yang belum pernah dipakai. Kategori yang sudah dipakai
 * permintaan tamu tidak dihapus — riwayatnya akan kehilangan jenisnya —
 * melainkan dinonaktifkan dari form ubah.
 */
class DeleteSpecialRequestCategoryUseCase
{
    public function __construct(private readonly SpecialRequestCategoryRepository $categories) {}

    public function handle(SpecialRequestCategory $category): void
    {
        $used = $this->categories->requestCount($category);

        if ($used > 0) {
            throw ValidationException::withMessages([
                'category' => 'Kategori "'.$category->name.'" sudah dipakai '.$used.' permintaan, jadi tidak bisa dihapus. '
                    .'Nonaktifkan saja supaya tidak muncul lagi di panel meja tamu.',
            ]);
        }

        DB::transaction(fn () => $this->categories->delete($category));
    }
}
