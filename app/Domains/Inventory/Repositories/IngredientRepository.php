<?php

namespace App\Domains\Inventory\Repositories;

use App\Models\Ingredient;
use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class IngredientRepository
{
    public function find(string $id): ?Ingredient
    {
        return Ingredient::query()->find($id);
    }

    public function allOrdered(): Collection
    {
        return Ingredient::query()->orderBy('name')->get();
    }

    public function paginateForAdmin(int $perPage = 12, string $search = ''): LengthAwarePaginator
    {
        $search = trim($search);

        return Ingredient::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('unit', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** Ingredients at or below their minimum level. */
    public function lowStock(): Collection
    {
        return Ingredient::query()
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('name')
            ->get();
    }

    public function setStock(Ingredient $ingredient, float $stock): Ingredient
    {
        $ingredient->update(['stock' => $stock]);

        return $ingredient;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Ingredient $ingredient, array $attributes): Ingredient
    {
        $ingredient->update($attributes);

        return $ingredient;
    }

    /** Append a row to the stock ledger. */
    public function recordMovement(array $attributes): StockMovement
    {
        return StockMovement::query()->create($attributes);
    }

    public function paginateMovements(int $perPage = 20, string $search = ''): LengthAwarePaginator
    {
        $search = trim($search);

        return StockMovement::query()
            ->with('ingredient')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('type', 'like', '%'.$search.'%')
                        ->orWhere('notes', 'like', '%'.$search.'%')
                        ->orWhereHas('ingredient', fn (Builder $ing) => $ing->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
