<?php

namespace App\Services\Admin;

use App\Models\Ingredient;
use App\Models\Payment;
use App\Models\StockOpname;
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

                    StockOpname::query()->create([
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
    public function addStock(Ingredient $ingredient, float $qty, string $notes = ''): StockOpname
    {
        return DB::transaction(function () use ($ingredient, $qty, $notes) {
            $qtyBefore = (float) $ingredient->stock;
            $qtyAfter = $qtyBefore + $qty;

            $ingredient->update(['stock' => $qtyAfter]);

            return StockOpname::query()->create([
                'ingredient_id' => $ingredient->id,
                'type' => 'in',
                'qty_before' => $qtyBefore,
                'qty_change' => $qty,
                'qty_after' => $qtyAfter,
                'notes' => $notes ?: 'Penambahan stok',
                'user_id' => auth()->id(),
            ]);
        });
    }

    /**
     * Koreksi stok (penyesuaian stok opname).
     */
    public function adjustStock(Ingredient $ingredient, float $newQty, string $notes = ''): StockOpname
    {
        return DB::transaction(function () use ($ingredient, $newQty, $notes) {
            $qtyBefore = (float) $ingredient->stock;
            $qtyChange = $newQty - $qtyBefore;

            $ingredient->update(['stock' => $newQty]);

            return StockOpname::query()->create([
                'ingredient_id' => $ingredient->id,
                'type' => 'adjustment',
                'qty_before' => $qtyBefore,
                'qty_change' => $qtyChange,
                'qty_after' => $newQty,
                'notes' => $notes ?: 'Koreksi stok opname',
                'user_id' => auth()->id(),
            ]);
        });
    }
}
