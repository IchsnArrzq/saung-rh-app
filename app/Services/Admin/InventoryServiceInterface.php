<?php

namespace App\Services\Admin;

use App\Models\Ingredient;
use App\Models\Payment;
use App\Models\StockOpname;

interface InventoryServiceInterface
{
    /**
     * Kurangi stok bahan berdasarkan item dalam order yang sudah dibayar.
     */
    public function deductFromPayment(Payment $payment): void;

    /**
     * Tambah stok (pembelian bahan).
     */
    public function addStock(Ingredient $ingredient, float $qty, string $notes = ''): StockOpname;

    /**
     * Koreksi stok (penyesuaian stok opname).
     */
    public function adjustStock(Ingredient $ingredient, float $newQty, string $notes = ''): StockOpname;
}
