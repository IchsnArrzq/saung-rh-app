<?php

namespace App\Services\Admin;

use App\Models\Ingredient;
use App\Models\Payment;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;

interface InventoryServiceInterface
{
    /**
     * Kurangi stok bahan berdasarkan item dalam order yang sudah dibayar.
     */
    public function deductFromPayment(Payment $payment): void;

    /**
     * Tambah stok (mis. pembelian bahan). Opsional dikaitkan ke dokumen sumber.
     */
    public function addStock(Ingredient $ingredient, float $qty, string $notes = '', ?Model $reference = null): StockMovement;

    /**
     * Kurangi stok (mis. penjualan/pemakaian). Opsional dikaitkan ke dokumen sumber.
     */
    public function reduceStock(Ingredient $ingredient, float $qty, string $notes = '', ?Model $reference = null): StockMovement;

    /**
     * Koreksi stok ke nilai baru (penyesuaian stok opname).
     */
    public function adjustStock(Ingredient $ingredient, float $newQty, string $notes = '', ?Model $reference = null): StockMovement;
}
