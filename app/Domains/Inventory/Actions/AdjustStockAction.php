<?php

namespace App\Domains\Inventory\Actions;

use App\Domains\Inventory\Enums\StockMovementType;
use App\Models\Ingredient;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;

/**
 * Sets the balance outright from a physical count (stock opname). The delta is
 * whatever it takes to get there, so it may be positive or negative.
 */
class AdjustStockAction
{
    public function __construct(private readonly RecordStockMovementAction $record) {}

    public function handle(Ingredient $ingredient, float $countedQty, string $notes = '', ?Model $reference = null): StockMovement
    {
        $before = (float) $ingredient->stock;

        return $this->record->handle(
            $ingredient,
            StockMovementType::Adjustment,
            $before,
            $countedQty - $before,
            $countedQty,
            $notes ?: 'Koreksi stok opname',
            $reference,
        );
    }
}
