<?php

namespace App\Services\Admin;

use App\Models\Ingredient;
use App\Models\StockOpname;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class StockOpnameService
{
    public function __construct(
        private readonly InventoryService $inventory,
    ) {
    }

    public function createDraft(CarbonInterface $date, ?string $notes = null, array $ingredientIds = []): StockOpname
    {
        return DB::transaction(function () use ($date, $notes, $ingredientIds) {
            $ingredients = Ingredient::query()
                ->where('is_active', true)
                ->when($ingredientIds !== [], fn ($q) => $q->whereIn('id', $ingredientIds))
                ->orderBy('name')
                ->get();

            $opname = StockOpname::query()->create([
                'code' => $this->generateCode($date),
                'opname_date' => $date->toDateString(),
                'status' => 'draft',
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

    public function post(StockOpname $opname): void
    {
        if ($opname->isPosted()) {
            return;
        }

        DB::transaction(function () use ($opname) {
            $opname->loadMissing('items.ingredient');

            foreach ($opname->items as $item) {
                // No count entered → treat as "matches system", nothing to adjust.
                if ($item->physical_qty === null) {
                    continue;
                }

                $physical = (float) $item->physical_qty;
                $difference = $physical - (float) $item->system_qty;

                $item->update(['difference' => $difference]);

                if (abs($difference) < 0.0005) {
                    continue;
                }

                $ingredient = $item->ingredient;

                if (! $ingredient) {
                    continue;
                }

                $this->inventory->adjustStock(
                    $ingredient,
                    $physical,
                    "Stok opname {$opname->code}",
                    $opname,
                );
            }

            $opname->update([
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => auth()->id(),
            ]);
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
