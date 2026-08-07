<?php

namespace App\Domains\Inventory\UseCases;

use App\Domains\Inventory\Actions\ReduceStockAction;
use App\Domains\Inventory\Enums\DocumentStatus;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

/**
 * Takes a sale out of stock and freezes the document. Idempotent, like posting
 * a purchase.
 */
class PostSaleUseCase
{
    public function __construct(private readonly ReduceStockAction $reduceStock) {}

    public function handle(Sale $sale): void
    {
        if (DocumentStatus::from($sale->status)->isPosted()) {
            return;
        }

        DB::transaction(function () use ($sale): void {
            $sale->loadMissing('items.ingredient');

            foreach ($sale->items as $item) {
                $ingredient = $item->ingredient;

                if (! $ingredient) {
                    continue;
                }

                $this->reduceStock->handle(
                    $ingredient,
                    (float) $item->qty,
                    "Penjualan {$sale->code}",
                    $sale,
                );
            }

            $sale->update([
                'status' => DocumentStatus::Posted->value,
                'posted_at' => now(),
                'posted_by' => auth()->id(),
            ]);
        });
    }
}
