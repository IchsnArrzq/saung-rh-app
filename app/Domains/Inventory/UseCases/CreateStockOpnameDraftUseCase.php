<?php

namespace App\Domains\Inventory\UseCases;

use App\Domains\Inventory\Enums\DocumentStatus;
use App\Models\Ingredient;
use App\Models\StockOpname;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Opens a counting sheet: one line per active ingredient, each pre-filled with
 * the balance the system currently believes.
 */
class CreateStockOpnameDraftUseCase
{
    /**
     * @param  array<int, string>  $ingredientIds  Empty means every active ingredient.
     */
    public function handle(CarbonInterface $date, ?string $notes = null, array $ingredientIds = []): StockOpname
    {
        return DB::transaction(function () use ($date, $notes, $ingredientIds): StockOpname {
            $ingredients = Ingredient::query()
                ->where('is_active', true)
                ->when($ingredientIds !== [], fn ($query) => $query->whereIn('id', $ingredientIds))
                ->orderBy('name')
                ->get();

            $opname = StockOpname::query()->create([
                'code' => $this->generateCode($date),
                'opname_date' => $date->toDateString(),
                'status' => DocumentStatus::Draft->value,
                'notes' => $notes ?: null,
                'user_id' => auth()->id(),
            ]);

            foreach ($ingredients as $ingredient) {
                $opname->items()->create([
                    'ingredient_id' => $ingredient->id,
                    'system_qty' => (float) $ingredient->stock,
                    'physical_qty' => null,
                    'difference' => 0,
                ]);
            }

            return $opname;
        });
    }

    private function generateCode(CarbonInterface $date): string
    {
        $prefix = 'SO-'.$date->format('Ymd');

        $count = StockOpname::query()
            ->where('code', 'like', $prefix.'%')
            ->count();

        return $prefix.'-'.str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }
}
