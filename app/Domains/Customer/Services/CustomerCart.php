<?php

namespace App\Domains\Customer\Services;

use Illuminate\Support\Collection;

/**
 * The seated customer's cart, kept in the session and keyed per table — a party
 * at table 3 must never see what table 4 is ordering.
 *
 * A Service, not a Repository: it holds no database query at all, only session
 * state and the arithmetic over it (AGENTS.md § Service). Extracted from the
 * old OrderCartService, which mixed this session juggling with Menu queries,
 * Table lookups and order creation.
 *
 * Distinct from App\Support\RestaurantCart, which backs the anonymous QR guest
 * and stores exactly one cart per session with no table key.
 */
class CustomerCart
{
    private const CART_SESSION_PREFIX = 'customer.order.cart';

    private const ACTIVE_TABLE_KEY = 'customer.order.active_table';

    /**
     * Remember where the customer is seated, so extra rounds do not send them
     * back to the table picker once the first order flips the table to
     * "order_in" and it stops looking selectable.
     */
    public function setActiveTable(string $tableId): void
    {
        session([self::ACTIVE_TABLE_KEY => $tableId]);
    }

    public function activeTableId(): ?string
    {
        return session(self::ACTIVE_TABLE_KEY);
    }

    public function forgetActiveTable(): void
    {
        session()->forget(self::ACTIVE_TABLE_KEY);
    }

    /**
     * @param  array{id:string,name:string,price:float,image_url:?string}  $menu
     */
    public function addItem(string $tableId, array $menu, int $qty = 1, ?string $notes = null): void
    {
        $cart = $this->cart($tableId);
        $existingQty = (int) ($cart[$menu['id']]['qty'] ?? 0);
        $notes = trim((string) $notes);

        $cart[$menu['id']] = [
            'menu_id' => $menu['id'],
            'name' => $menu['name'],
            'image_url' => $menu['image_url'] ?? null,
            'price' => (float) $menu['price'],
            'qty' => min($existingQty + max(1, $qty), 50),
            'notes' => $notes !== '' ? $notes : ($cart[$menu['id']]['notes'] ?? null),
        ];

        $this->put($tableId, $cart);
    }

    public function setQty(string $tableId, string $menuId, int $qty): void
    {
        $cart = $this->cart($tableId);

        if (! isset($cart[$menuId])) {
            return;
        }

        $cart[$menuId]['qty'] = max(1, min($qty, 50));

        $this->put($tableId, $cart);
    }

    public function setNotes(string $tableId, string $menuId, ?string $notes): void
    {
        $cart = $this->cart($tableId);

        if (! isset($cart[$menuId])) {
            return;
        }

        $notes = trim((string) $notes);
        $cart[$menuId]['notes'] = $notes !== '' ? $notes : null;

        $this->put($tableId, $cart);
    }

    public function removeItem(string $tableId, string $menuId): void
    {
        $cart = $this->cart($tableId);
        unset($cart[$menuId]);

        $this->put($tableId, $cart);
    }

    public function empty(string $tableId): void
    {
        session()->forget($this->cartKey($tableId));
    }

    public function isEmpty(string $tableId): bool
    {
        return $this->cart($tableId) === [];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function items(string $tableId): Collection
    {
        return collect($this->cart($tableId))->values();
    }

    public function count(string $tableId): int
    {
        return (int) collect($this->cart($tableId))->sum('qty');
    }

    public function subtotal(string $tableId): float
    {
        return (float) collect($this->cart($tableId))
            ->sum(fn (array $item) => ((float) $item['price']) * ((int) $item['qty']));
    }

    public function qtyOf(string $tableId, string $menuId): int
    {
        return (int) ($this->cart($tableId)[$menuId]['qty'] ?? 0);
    }

    /**
     * Cart lines in the shape the Order domain expects (see
     * App\Domains\Order\Actions\CalculateOrderTotalAction).
     *
     * @return array<int, array<string, mixed>>
     */
    public function toOrderItems(string $tableId): array
    {
        return array_values(array_map(fn (array $item): array => [
            'menu_id' => $item['menu_id'],
            'menu_name_snapshot' => $item['name'],
            'qty' => (int) $item['qty'],
            'price' => (float) $item['price'],
            'notes' => $item['notes'] ?? null,
        ], $this->cart($tableId)));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function cart(string $tableId): array
    {
        return session($this->cartKey($tableId), []);
    }

    /**
     * @param  array<string, array<string, mixed>>  $cart
     */
    private function put(string $tableId, array $cart): void
    {
        session([$this->cartKey($tableId) => $cart]);
    }

    private function cartKey(string $tableId): string
    {
        return self::CART_SESSION_PREFIX.'.'.$tableId;
    }
}
