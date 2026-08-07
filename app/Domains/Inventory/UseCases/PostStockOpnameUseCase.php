<?php

namespace App\Domains\Inventory\UseCases;

use App\Domains\Inventory\Actions\AdjustStockAction;
use App\Domains\Inventory\Enums\DocumentStatus;
use App\Models\StockOpname;
use Illuminate\Support\Facades\DB;

/**
 * Applies a physical count: every line with a counted quantity that differs
 * from the system figure adjusts the balance.
 *
 * Lines left blank mean "not counted", not "counted zero", so they are skipped.
 */
class PostStockOpnameUseCase
{
    /** Differences smaller than this are float noise, not real discrepancies. */
    private const EPSILON = 0.0005;

    public function __construct(private readonly AdjustStockAction $adjustStock) {}

    public function handle(StockOpname $opname): void
    {
        if (DocumentStatus::from($opname->status)->isPosted()) {
            return;
        }

        DB::transaction(function () use ($opname): void {
            $opname->loadMissing('items.ingredient');

            foreach ($opname->items as $item) {
                if ($item->physical_qty === null) {
                    continue;
                }

                $physical = (float) $item->physical_qty;
                $difference = $physical - (float) $item->system_qty;

                $item->update(['difference' => $difference]);

                if (abs($difference) < self::EPSILON) {
                    continue;
                }

                $ingredient = $item->ingredient;

                if (! $ingredient) {
                    continue;
                }

                $this->adjustStock->handle(
                    $ingredient,
                    $physical,
                    "Stok opname {$opname->code}",
                    $opname,
                );
            }

            $opname->update([
                'status' => DocumentStatus::Posted->value,
                'posted_at' => now(),
                'posted_by' => auth()->id(),
            ]);
        });
    }
}
