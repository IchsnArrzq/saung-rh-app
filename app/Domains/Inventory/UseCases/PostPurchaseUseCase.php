<?php

namespace App\Domains\Inventory\UseCases;

use App\Domains\Inventory\Actions\AddStockAction;
use App\Domains\Inventory\Enums\DocumentStatus;
use App\Domains\Inventory\Repositories\IngredientRepository;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

/**
 * Receives a purchase into stock and freezes the document.
 *
 * Idempotent by design — posting an already-posted purchase is a no-op rather
 * than an error, because the button is reachable from a stale page.
 */
class PostPurchaseUseCase
{
    public function __construct(
        private readonly AddStockAction $addStock,
        private readonly IngredientRepository $ingredients,
    ) {}

    public function handle(Purchase $purchase): void
    {
        if (DocumentStatus::from($purchase->status)->isPosted()) {
            return;
        }

        DB::transaction(function () use ($purchase): void {
            $purchase->loadMissing('items.ingredient');

            foreach ($purchase->items as $item) {
                $ingredient = $item->ingredient;

                if (! $ingredient) {
                    continue;
                }

                $this->addStock->handle(
                    $ingredient,
                    (float) $item->qty,
                    "Pembelian {$purchase->code}",
                    $purchase,
                );

                // Keep the ingredient's unit cost in sync with the latest buy price.
                if ((float) $item->unit_cost > 0) {
                    $this->ingredients->update($ingredient, ['cost_per_unit' => $item->unit_cost]);
                }
            }

            $purchase->update([
                'status' => DocumentStatus::Posted->value,
                'posted_at' => now(),
                'posted_by' => auth()->id(),
            ]);
        });
    }
}
