<?php

namespace App\Support;

use App\Models\Menu;
use Illuminate\Http\Request;

class RestaurantCart
{
    public const SESSION_CART_KEY = 'restaurant.cart.items';

    public const SESSION_CONTEXT_KEY = 'restaurant.cart.context';

    public const MODE_ONLINE = 'online';

    public const MODE_OFFLINE = 'offline';

    /**
     * The table is never taken from the URL or a picker: only a QR check-in
     * binds one (TableSessionContext), so a guest cannot put an order on a
     * table they are not sitting at. `?table_id=` links are simply ignored.
     *
     * @return array{mode:string,table_id:?string}
     */
    public static function context(): array
    {
        $tableId = TableSessionContext::current()['table_id'] ?? null;

        return [
            'mode' => $tableId ? self::MODE_OFFLINE : session(self::SESSION_CONTEXT_KEY.'.mode', self::MODE_ONLINE),
            'table_id' => $tableId,
        ];
    }

    /**
     * @return array{mode:string,table_id:?string}
     */
    public static function syncContextFromRequest(Request $request): array
    {
        return self::setMode((string) $request->query('mode'));
    }

    /**
     * @return array{mode:string,table_id:?string}
     */
    public static function setMode(string $mode): array
    {
        if (in_array($mode, [self::MODE_ONLINE, self::MODE_OFFLINE], true)) {
            session([self::SESSION_CONTEXT_KEY => ['mode' => $mode]]);
        }

        return self::context();
    }

    /**
     * @return array<string, array{menu_id:string,name:string,image_url:?string,price:float,qty:int,notes:?string}>
     */
    public static function cart(): array
    {
        return session(self::SESSION_CART_KEY, []);
    }

    public static function addItem(Menu $menu, int $qty = 1, ?string $notes = null): void
    {
        $cart = self::cart();
        $existingQty = (int) ($cart[$menu->id]['qty'] ?? 0);

        $cart[$menu->id] = [
            'menu_id' => $menu->id,
            'name' => $menu->name,
            'image_url' => $menu->display_image_url,
            'price' => (float) $menu->price,
            'qty' => min($existingQty + $qty, 50),
            'notes' => $notes ?: ($cart[$menu->id]['notes'] ?? null),
        ];

        session([self::SESSION_CART_KEY => $cart]);
    }

    public static function setQty(string $menuId, int $qty): void
    {
        $cart = self::cart();

        if (! isset($cart[$menuId])) {
            return;
        }

        $cart[$menuId]['qty'] = max(1, min($qty, 50));

        session([self::SESSION_CART_KEY => $cart]);
    }

    public static function removeItem(string $menuId): void
    {
        $cart = self::cart();
        unset($cart[$menuId]);

        session([self::SESSION_CART_KEY => $cart]);
    }

    public static function clearCart(): void
    {
        session()->forget(self::SESSION_CART_KEY);
    }

    /**
     * Cart lines translated into the shape the Order domain expects (see
     * App\Domains\Order\Actions\CalculateOrderTotalAction). Lives here so the
     * two checkouts reading this session — the QR guest and the POS — cannot
     * map it differently.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function toOrderItems(): array
    {
        return array_values(array_map(fn (array $item): array => [
            'menu_id' => $item['menu_id'],
            'menu_name_snapshot' => $item['name'],
            'qty' => (int) $item['qty'],
            'price' => (float) $item['price'],
            'notes' => $item['notes'] ?? null,
        ], self::cart()));
    }

    public static function count(): int
    {
        return collect(self::cart())->sum('qty');
    }

    public static function subtotal(): float
    {
        return (float) collect(self::cart())
            ->sum(fn (array $item) => ((float) $item['price']) * ((int) $item['qty']));
    }
}
