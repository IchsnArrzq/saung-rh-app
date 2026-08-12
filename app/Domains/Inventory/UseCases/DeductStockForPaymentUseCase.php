<?php

namespace App\Domains\Inventory\UseCases;

use App\Domains\Inventory\Actions\ReduceStockAction;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Consumes the ingredients behind a paid order, following each order line's
 * menu recipe.
 *
 * Triggered by PaymentObserver the moment a payment reaches "paid", so no
 * UseCase has to remember to call it (AGENTS.md § Audit Log).
 */
class DeductStockForPaymentUseCase
{
    public function __construct(private readonly ReduceStockAction $reduceStock) {}

    public function handle(Payment $payment): void
    {
        // Deposits (e.g. reservation down payments) carry no order and consume
        // no inventory — nothing to deduct.
        if ($payment->type === 'deposit' || is_null($payment->order_id)) {
            return;
        }

        $payment->load(['order.items.menu.menuIngredients.ingredient']);

        if (! $payment->order) {
            return;
        }

        DB::transaction(function () use ($payment): void {
            foreach ($payment->order->items as $orderItem) {
                $recipe = $orderItem->menu?->menuIngredients ?? collect();

                foreach ($recipe as $line) {
                    $ingredient = $line->ingredient;

                    if (! $ingredient) {
                        continue;
                    }

                    $this->reduceStock->handle(
                        $ingredient,
                        (float) $line->qty * (int) $orderItem->qty,
                        'Pemakaian otomatis: Order #'.$payment->order->order_number,
                        $payment,
                    );
                }
            }
        });
    }
}
