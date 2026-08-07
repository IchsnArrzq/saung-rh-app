<?php

namespace App\Domains\Inventory\Actions;

use App\Domains\Inventory\Enums\StockMovementType;
use App\Models\Ingredient;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;

/**
 * Adds to an ingredient's balance — goods received from a purchase.
 */
class AddStockAction
{
    public function __construct(private readonly RecordStockMovementAction $record) {}

    public function handle(Ingredient $ingredient, float $qty, string $notes = '', ?Model $reference = null): StockMovement
    {
        $before = (float) $ingredient->stock;

        return $this->record->handle(
            $ingredient,
            StockMovementType::In,
            $before,
            $qty,
            $before + $qty,
            $notes ?: 'Penambahan stok',
            $reference,
        );
    }
}
