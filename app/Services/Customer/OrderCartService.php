<?php

namespace App\Services\Customer;

use App\Events\OrderCreated;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Table;
use App\Models\TableStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderCartService
{
    private const CART_SESSION_PREFIX = 'customer.order.cart';

    /**
     * @return array{statuses:Collection<int,TableStatus>,tablesByStatus:\Illuminate\Support\Collection<string,Collection<int,Table>>,unassignedTables:Collection<int,Table>}
     */
    public function tableSelectionData(string $search = ''): array
    {
        $search = trim($search);

        $statuses = TableStatus::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $statusIdByKey = $statuses->pluck('id', 'key');

        $tables = Table::query()
            ->with(['tableStatus', 'tableCategory'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('code', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhere('capacity', 'like', '%'.$search.'%')
                        ->orWhereHas('tableStatus', fn ($status) => $status->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('tableCategory', fn ($category) => $category->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('code')
            ->get();

        $tablesByStatus = $tables->groupBy(function (Table $table) use ($statusIdByKey) {
            if ($table->table_status_id) {
                return $table->table_status_id;
            }

            $legacyStatusId = $statusIdByKey->get($table->status);

            return $legacyStatusId ?: '__unassigned__';
        });

        return [
            'statuses' => $statuses,
            'tablesByStatus' => $tablesByStatus,
            'unassignedTables' => $tablesByStatus->get('__unassigned__', collect()),
        ];
    }

    /**
     * @return array{table:Table,menus:LengthAwarePaginator,cartCount:int,cartSubtotal:float}
     */
    public function menuCatalogData(string $tableId, string $search = '', int $perPage = 12): array
    {
        $table = $this->resolveAvailableTable($tableId);

        $menus = $this->paginateMenus($search, $perPage);

        return [
            'table' => $table,
            'menus' => $menus,
            'cartCount' => $this->count($table->id),
            'cartSubtotal' => $this->subtotal($table->id),
        ];
    }

    /**
     * @return array{table:Table,relatedMenus:Collection<int,Menu>,cartCount:int,cartSubtotal:float}
     */
    public function menuDetailData(string $tableId, Menu $menu): array
    {
        $table = $this->resolveAvailableTable($tableId);

        $relatedMenus = Menu::query()
            ->with('category')
            ->where('is_available', true)
            ->whereKeyNot($menu->id)
            ->when($menu->menu_category_id, fn ($query) => $query->where('menu_category_id', $menu->menu_category_id))
            ->orderBy('name')
            ->limit(4)
            ->get();

        return [
            'table' => $table,
            'relatedMenus' => $relatedMenus,
            'cartCount' => $this->count($table->id),
            'cartSubtotal' => $this->subtotal($table->id),
        ];
    }

    /**
     * @return array{table:Table,cartItems:\Illuminate\Support\Collection<int,array{menu_id:string,name:string,image_url:?string,price:float,qty:int,notes:?string}>,subtotal:float,cartCount:int}
     */
    public function cartData(string $tableId): array
    {
        $table = $this->resolveAvailableTable($tableId);

        return [
            'table' => $table,
            'cartItems' => collect($this->cart($table->id))->values(),
            'subtotal' => $this->subtotal($table->id),
            'cartCount' => $this->count($table->id),
        ];
    }

    public function addToCart(Request $request): void
    {
        $validated = $request->validate([
            'table_id' => ['required', 'exists:tables,id'],
            'menu_id' => ['required', 'exists:menus,id'],
            'qty' => ['required', 'integer', 'min:1', 'max:20'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $table = $this->resolveAvailableTable($validated['table_id']);

        $menu = Menu::query()
            ->where('is_available', true)
            ->find($validated['menu_id']);

        if (! $menu) {
            throw ValidationException::withMessages([
                'menu_id' => 'Menu tidak ditemukan atau sedang tidak tersedia.',
            ]);
        }

        $cart = $this->cart($table->id);
        $existingQty = (int) ($cart[$menu->id]['qty'] ?? 0);
        $notes = trim((string) ($validated['notes'] ?? ''));

        $cart[$menu->id] = [
            'menu_id' => $menu->id,
            'name' => $menu->name,
            'image_url' => $menu->image_url,
            'price' => (float) $menu->price,
            'qty' => min($existingQty + ((int) $validated['qty']), 50),
            'notes' => $notes !== '' ? $notes : ($cart[$menu->id]['notes'] ?? null),
        ];

        session([$this->cartKey($table->id) => $cart]);
    }

    public function updateCartItem(Request $request, string $menuId): void
    {
        $validated = $request->validate([
            'table_id' => ['required', 'exists:tables,id'],
            'qty' => ['required', 'integer', 'min:1', 'max:50'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $table = $this->resolveAvailableTable($validated['table_id']);
        $cart = $this->cart($table->id);

        if (! isset($cart[$menuId])) {
            throw ValidationException::withMessages([
                'cart' => 'Item cart tidak ditemukan.',
            ]);
        }

        $notes = trim((string) ($validated['notes'] ?? ''));

        $cart[$menuId]['qty'] = (int) $validated['qty'];
        $cart[$menuId]['notes'] = $notes !== '' ? $notes : null;

        session([$this->cartKey($table->id) => $cart]);
    }

    public function removeCartItem(string $tableId, string $menuId): void
    {
        $table = $this->resolveAvailableTable($tableId);
        $cart = $this->cart($table->id);

        unset($cart[$menuId]);

        session([$this->cartKey($table->id) => $cart]);
    }

    public function checkout(Request $request): Order
    {
        $validated = $request->validate([
            'table_id' => ['required', 'exists:tables,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $table = $this->resolveAvailableTable($validated['table_id']);
        $cart = $this->cart($table->id);

        if ($cart === []) {
            throw ValidationException::withMessages([
                'cart' => 'Cart masih kosong.',
            ]);
        }

        $orderInStatus = TableStatus::query()->where('key', 'order_in')->first();

        $order = DB::transaction(function () use ($table, $cart, $validated, $orderInStatus): Order {
            $subtotal = (float) collect($cart)
                ->sum(fn (array $item) => ((float) $item['price']) * ((int) $item['qty']));

            $order = Order::query()->create([
                'table_id' => $table->id,
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => Auth::user()?->name,
                'status' => 'confirmed',
                'notes' => trim('Sumber: CUSTOMER ORDER | '.($validated['notes'] ?? '')),
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => 0,
                'total' => $subtotal,
                'ordered_at' => now(),
            ]);

            $items = collect($cart)
                ->map(function (array $item): array {
                    $qty = (int) $item['qty'];
                    $price = (float) $item['price'];

                    return [
                        'menu_id' => $item['menu_id'],
                        'menu_name_snapshot' => $item['name'],
                        'qty' => $qty,
                        'price' => $price,
                        'line_total' => $qty * $price,
                        'notes' => $item['notes'] ?? null,
                    ];
                })
                ->values()
                ->all();

            $order->items()->createMany($items);

            $tableStatusKey = $table->tableStatus?->key ?? $table->status;

            if ($tableStatusKey === 'available' && $orderInStatus) {
                $table->update([
                    'table_status_id' => $orderInStatus->id,
                    'status' => $orderInStatus->key,
                ]);
            }

            return $order;
        });

        $this->clearCart($table->id);

        OrderCreated::dispatch($order);

        return $order;
    }

    private function paginateMenus(string $search = '', int $perPage = 12): LengthAwarePaginator
    {
        $search = trim($search);

        return Menu::query()
            ->with('category')
            ->where('is_available', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    private function resolveAvailableTable(string $tableId): Table
    {
        $table = Table::query()->with('tableStatus')->findOrFail($tableId);

        $statusKey = $table->tableStatus?->key ?? $table->status;

        if ($statusKey !== 'available') {
            throw ValidationException::withMessages([
                'table_id' => 'Meja tidak tersedia. Pilih meja lain yang berstatus tersedia.',
            ]);
        }

        return $table;
    }

    /**
     * @return array<string, array{menu_id:string,name:string,image_url:?string,price:float,qty:int,notes:?string}>
     */
    private function cart(string $tableId): array
    {
        return session($this->cartKey($tableId), []);
    }

    private function clearCart(string $tableId): void
    {
        session()->forget($this->cartKey($tableId));
    }

    private function count(string $tableId): int
    {
        return (int) collect($this->cart($tableId))->sum('qty');
    }

    private function subtotal(string $tableId): float
    {
        return (float) collect($this->cart($tableId))
            ->sum(fn (array $item) => ((float) $item['price']) * ((int) $item['qty']));
    }

    private function cartKey(string $tableId): string
    {
        return self::CART_SESSION_PREFIX.'.'.$tableId;
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
