<?php

namespace App\Services\Admin;

use App\Models\Purchase;

interface PurchaseServiceInterface
{
    /**
     * Post a draft purchase: add each item's qty to ingredient stock, update
     * the ingredient's latest unit cost, and mark the purchase posted.
     */
    public function post(Purchase $purchase): void;
}
