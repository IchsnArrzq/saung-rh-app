<?php

namespace App\Domains\Inventory\Actions;

use App\Domains\Inventory\Enums\StockMovementType;
use App\Models\Ingredient;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;

/**
 * Takes stock out — a sale, or ingredients consumed by a paid order.
 *
 * The balance floors at zero rather than going negative: the kitchen can plate
 * a dish the books did not know about, and a negative balance would be a
 * worse lie than zero.
 */
class ReduceStockAction
{
    public function __construct(private readonly RecordStockMovementAction $record) {}

    public function handle(Ingredient $ingredient, float $qty, string $notes = '', ?Model $reference = null): StockMovement
    {
        $before = (float) $ingredient->stock;

        return $this->record->handle(
            $ingredient,
            StockMovementType::Out,
            $before,
            -$qty,
            max(0.0, $before - $qty),
            $notes ?: 'Pengurangan stok',
            $reference,
        );
    }
}
