<?php

namespace App\Services\Admin;

use App\Models\Ingredient;
use App\Models\Payment;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Kurangi stok bahan berdasarkan item dalam order yang sudah dibayar.
     */
    public function deductFromPayment(Payment $payment): void
    {
        // Deposits (e.g. reservation down payments) carry no order and consume
        // no inventory — nothing to deduct.
        if ($payment->type === 'deposit' || is_null($payment->order_id)) {
            return;
        }

        $payment->load(['order.items.menu.menuIngredients.ingredient']);

        DB::transaction(function () use ($payment) {
            foreach ($payment->order->items as $orderItem) {
                $menuIngredients = $orderItem->menu?->menuIngredients ?? collect();

                foreach ($menuIngredients as $menuIngredient) {
                    $ingredient = $menuIngredient->ingredient;
                    $totalQty = (float) $menuIngredient->qty * (int) $orderItem->qty;

                    $qtyBefore = (float) $ingredient->stock;
                    $qtyAfter = max(0, $qtyBefore - $totalQty);

                    $ingredient->update(['stock' => $qtyAfter]);

                    StockMovement::query()->create([
                        'ingredient_id' => $ingredient->id,
                        'type' => 'out',
                        'qty_before' => $qtyBefore,
                        'qty_change' => -$totalQty,
                        'qty_after' => $qtyAfter,
                        'reference_type' => Payment::class,
                        'reference_id' => $payment->id,
                        'notes' => 'Pemakaian otomatis: Order #' . $payment->order->order_number,
                        'user_id' => auth()->id(),
                    ]);
                }
            }
        });
    }

    /**
     * Tambah stok (pembelian bahan).
     */
    public function addStock(Ingredient $ingredient, float $qty, string $notes = '', ?Model $reference = null): StockMovement
    {
        return DB::transaction(function () use ($ingredient, $qty, $notes, $reference) {
            $qtyBefore = (float) $ingredient->stock;
            $qtyAfter = $qtyBefore + $qty;

            $ingredient->update(['stock' => $qtyAfter]);

            return $this->record($ingredient, 'in', $qtyBefore, $qty, $qtyAfter, $notes ?: 'Penambahan stok', $reference);
        });
    }

    /**
     * Kurangi stok (pemakaian / penjualan).
     */
    public function reduceStock(Ingredient $ingredient, float $qty, string $notes = '', ?Model $reference = null): StockMovement
    {
        return DB::transaction(function () use ($ingredient, $qty, $notes, $reference) {
            $qtyBefore = (float) $ingredient->stock;
            $qtyAfter = max(0, $qtyBefore - $qty);

            $ingredient->update(['stock' => $qtyAfter]);

            return $this->record($ingredient, 'out', $qtyBefore, -$qty, $qtyAfter, $notes ?: 'Pengurangan stok', $reference);
        });
    }

    /**
     * Koreksi stok (penyesuaian stok opname).
     */
    public function adjustStock(Ingredient $ingredient, float $newQty, string $notes = '', ?Model $reference = null): StockMovement
    {
        return DB::transaction(function () use ($ingredient, $newQty, $notes, $reference) {
            $qtyBefore = (float) $ingredient->stock;
            $qtyChange = $newQty - $qtyBefore;

            $ingredient->update(['stock' => $newQty]);

            return $this->record($ingredient, 'adjustment', $qtyBefore, $qtyChange, $newQty, $notes ?: 'Koreksi stok opname', $reference);
        });
    }

    private function record(Ingredient $ingredient, string $type, float $before, float $change, float $after, string $notes, ?Model $reference): StockMovement
    {
        return StockMovement::query()->create([
            'ingredient_id' => $ingredient->id,
            'type' => $type,
            'qty_before' => $before,
            'qty_change' => $change,
            'qty_after' => $after,
            'reference_type' => $reference ? $reference->getMorphClass() : null,
            'reference_id' => $reference?->getKey(),
            'notes' => $notes,
            'user_id' => auth()->id(),
        ]);
    }
}
