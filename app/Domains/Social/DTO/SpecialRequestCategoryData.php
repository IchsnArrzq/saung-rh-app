<?php

namespace App\Domains\Social\DTO;

/**
 * Isian form kategori permintaan khusus, dari layar admin ke
 * SaveSpecialRequestCategoryUseCase.
 */
final readonly class SpecialRequestCategoryData
{
    public function __construct(
        public string $name,
        public ?string $icon,
        public ?string $description,
        public bool $isActive,
        /** Kosong = taruh paling akhir (kategori baru) atau pertahankan urutannya (ubah). */
        public ?int $sortOrder,
    ) {}
}
