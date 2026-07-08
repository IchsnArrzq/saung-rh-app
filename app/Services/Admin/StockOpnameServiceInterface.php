<?php

namespace App\Services\Admin;

use App\Models\StockOpname;
use Carbon\CarbonInterface;

interface StockOpnameServiceInterface
{
    /**
     * Create a draft opname, snapshotting the current stock of the given
     * ingredients (all active ingredients when none supplied).
     *
     * @param  array<int, string>  $ingredientIds
     */
    public function createDraft(CarbonInterface $date, ?string $notes = null, array $ingredientIds = []): StockOpname;

    /**
     * Apply the counted quantities: adjust each ingredient's stock to its
     * physical count and record the difference as a stock movement.
     */
    public function post(StockOpname $opname): void;
}
