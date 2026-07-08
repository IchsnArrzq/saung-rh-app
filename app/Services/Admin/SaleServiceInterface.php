<?php

namespace App\Services\Admin;

use App\Models\Sale;

interface SaleServiceInterface
{
    /**
     * Post a draft sale: reduce each item's qty from ingredient stock and mark
     * the sale posted.
     */
    public function post(Sale $sale): void;
}
