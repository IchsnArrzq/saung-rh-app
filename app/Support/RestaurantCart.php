<?php

namespace App\Support;

use App\Models\Menu;
use App\Models\Table;
use Illuminate\Http\Request;

class RestaurantCart
{
    public const SESSION_CART_KEY = 'restaurant.cart.items';

    public const SESSION_CONTEXT_KEY = 'restaurant.cart.context';

    public const MODE_ONLINE = 'online';

    public const MODE_OFFLINE = 'offline';

    /**
     * @return array{mode:string,table_id:?string}
     */
    public static function context(): array
    {
        return session(self::SESSION_CONTEXT_KEY, [
            'mode' => self::MODE_ONLINE,
            'table_id' => null,
        ]);
    }

    /**
     * @return array{mode:string,table_id:?string}
     */
    public static function syncContextFromRequest(Request $request): array
    {
        $context = self::context();

        $mode = $request->query('mode');
        if (in_array($mode, [self::MODE_ONLINE, self::MODE_OFFLINE], true)) {
            $context['mode'] = $mode;

            if ($mode === self::MODE_ONLINE) {
                $context['table_id'] = null;
            }
        }

        if ($request->filled('table_id')) {
            $table = Table::query()->find($request->string('table_id')->toString());
            $context['table_id'] = $table?->id;

            if ($table) {
                $context['mode'] = self::MODE_OFFLINE;
            }
        }

        session([self::SESSION_CONTEXT_KEY => $context]);

        return $context;
    }

    /**
     * @return array{mode:string,table_id:?string}
     */
    public static function setMode(string $mode): array
    {
        $context = self::context();

        if (! in_array($mode, [self::MODE_ONLINE, self::MODE_OFFLINE], true)) {
            return $context;
        }

        $context['mode'] = $mode;

        if ($mode === self::MODE_ONLINE) {
            $context['table_id'] = null;
        }

        session([self::SESSION_CONTEXT_KEY => $context]);

        return $context;
    }

    /**
     * @return array{mode:string,table_id:?string}
     */
    public static function setTableId(?string $tableId): array
    {
        $context = self::context();
        $context['table_id'] = $tableId;

        if ($tableId) {
            $context['mode'] = self::MODE_OFFLINE;
        }

        session([self::SESSION_CONTEXT_KEY => $context]);

        return $context;
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
            'image_url' => $menu->image_url,
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
