<?php

namespace App\Services\Admin;

use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        private readonly InventoryService $inventory,
    ) {
    }

    public function post(Purchase $purchase): void
    {
        if ($purchase->isPosted()) {
            return;
        }

        DB::transaction(function () use ($purchase) {
            $purchase->loadMissing('items.ingredient');

            foreach ($purchase->items as $item) {
                $ingredient = $item->ingredient;

                if (! $ingredient) {
                    continue;
                }

                $this->inventory->addStock(
                    $ingredient,
                    (float) $item->qty,
                    "Pembelian {$purchase->code}",
                    $purchase,
                );

                // Keep the ingredient's unit cost in sync with the latest buy price.
                if ((float) $item->unit_cost > 0) {
                    $ingredient->update(['cost_per_unit' => $item->unit_cost]);
                }
            }

            $purchase->update([
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => auth()->id(),
            ]);
        });
    }
}
