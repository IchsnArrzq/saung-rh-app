<?php

namespace App\Services\Admin;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class SaleServiceImplement implements SaleServiceInterface
{
    public function __construct(
        private readonly InventoryServiceInterface $inventory,
    ) {
    }

    public function post(Sale $sale): void
    {
        if ($sale->isPosted()) {
            return;
        }

        DB::transaction(function () use ($sale) {
            $sale->loadMissing('items.ingredient');

            foreach ($sale->items as $item) {
                $ingredient = $item->ingredient;

                if (! $ingredient) {
                    continue;
                }

                $this->inventory->reduceStock(
                    $ingredient,
                    (float) $item->qty,
                    "Penjualan {$sale->code}",
                    $sale,
                );
            }

            $sale->update([
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => auth()->id(),
            ]);
        });
    }
}
