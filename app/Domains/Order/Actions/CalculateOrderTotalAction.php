<?php

namespace App\Domains\Order\Actions;

use App\Domains\Order\DTO\OrderTotals;

/**
 * Normalises raw order lines and computes subtotal / tax / total.
 *
 * Extracted into its own Action because it is genuinely reused — CreateOrder,
 * UpdateOrder and PlaceCartOrder all need identical money maths, and any drift
 * between them would show up as a wrong bill. Single-use logic stays inside its
 * UseCase instead (AGENTS.md § UseCase merge rule).
 */
class CalculateOrderTotalAction
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function handle(array $items, float $tax = 0.0): OrderTotals
    {
        $normalised = $this->normalise($items);
        $subtotal = array_sum(array_column($normalised, 'line_total'));
        $tax = max($tax, 0.0);

        return new OrderTotals(
            items: $normalised,
            subtotal: $subtotal,
            tax: $tax,
            total: max($subtotal + $tax, 0.0),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function normalise(array $items): array
    {
        $normalised = [];

        foreach ($items as $item) {
            $qty = (int) ($item['qty'] ?? 0);
            $price = (float) ($item['price'] ?? 0);

            $normalised[] = [
                'menu_id' => $item['menu_id'] ?: null,
                'menu_name_snapshot' => $item['menu_name_snapshot'] ?: null,
                'qty' => $qty,
                'price' => $price,
                'line_total' => $qty * $price,
                'notes' => ($item['notes'] ?? null) ?: null,
            ];
        }

        return $normalised;
    }
}
