<?php

namespace App\Domains\Inventory\Repositories;

use App\Models\Ingredient;
use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface IngredientRepositoryInterface
{
    public function find(string $id): ?Ingredient;

    public function allOrdered(): Collection;

    public function paginateForAdmin(int $perPage = 12, string $search = ''): LengthAwarePaginator;

    /** Ingredients at or below their minimum level. */
    public function lowStock(): Collection;

    public function setStock(Ingredient $ingredient, float $stock): Ingredient;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Ingredient $ingredient, array $attributes): Ingredient;

    /** Append a row to the stock ledger. */
    public function recordMovement(array $attributes): StockMovement;

    public function paginateMovements(int $perPage = 20, string $search = ''): LengthAwarePaginator;
}
