<?php

namespace App\Domains\Inventory\Actions;

use App\Domains\Inventory\Enums\StockMovementType;
use App\Domains\Inventory\Repositories\IngredientRepository;
use App\Models\Ingredient;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;

/**
 * Writes one line to the stock ledger and moves the ingredient's balance to
 * match. Every stock change in the system goes through here, so the balance and
 * the ledger can never drift apart.
 *
 * Earns its own Action: used by all three of Add/Reduce/AdjustStockAction.
 */
class RecordStockMovementAction
{
    public function __construct(private readonly IngredientRepository $ingredients) {}

    public function handle(
        Ingredient $ingredient,
        StockMovementType $type,
        float $qtyBefore,
        float $qtyChange,
        float $qtyAfter,
        string $notes,
        ?Model $reference = null,
    ): StockMovement {
        $this->ingredients->setStock($ingredient, $qtyAfter);

        return $this->ingredients->recordMovement([
            'ingredient_id' => $ingredient->id,
            'type' => $type->value,
            'qty_before' => $qtyBefore,
            'qty_change' => $qtyChange,
            'qty_after' => $qtyAfter,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'notes' => $notes,
            'user_id' => auth()->id(),
        ]);
    }
}
