<?php

namespace App\Services\Customer;

use App\Events\OrderCreated;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\Table;
use App\Models\TableStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Backend for the customer dine-in ordering flow. The cart is kept per-table
 * in the session; checkout creates a confirmed Order and notifies the kitchen.
 *
 * All mutating methods take plain parameters so the flow can be driven from a
 * Livewire component without going through an HTTP Request.
 */
class OrderCartService
{
    private const CART_SESSION_PREFIX = 'customer.order.cart';

    private const ACTIVE_TABLE_KEY = 'customer.order.active_table';

    /**
     * Statuses where a seated party may place (and keep placing) orders. A
     * fresh pick requires "available", but once seated the table moves to
     * "occupied"/"order_in" and must still accept additional rounds.
     */
    public const ORDERABLE_STATUSES = ['available', 'occupied', 'order_in'];

    /**
     * Tables grouped by status, for the table-selection screen.
     *
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
     * Catalog data (menus + categories) for the ordering screen. Does not
     * validate the table, so it is safe to call on every Livewire re-render.
     *
     * @return array{menus:LengthAwarePaginator,categories:Collection<int,MenuCategory>,totalAvailable:int}
     */
    public function catalog(string $search = '', ?int $categoryId = null, int $perPage = 24): array
    {
        return [
            'menus' => $this->paginateMenus($search, $categoryId, $perPage),
            'categories' => MenuCategory::query()
                ->where('is_active', true)
                ->withCount(['menus' => fn ($q) => $q->available()])
                ->orderBy('name')
                ->get(),
            'totalAvailable' => Menu::query()->available()->count(),
        ];
    }

    /**
     * Resolve a table that is currently free to order on, or null when the
     * given id is unknown / no longer available.
     */
    public function findAvailableTable(string $tableId): ?Table
    {
        $table = Table::query()->with('tableStatus')->find($tableId);

        if (! $table) {
            return null;
        }

        $statusKey = $table->tableStatus?->key ?? $table->status;

        return $statusKey === 'available' ? $table : null;
    }

    /**
     * Resolve a table a seated party may order on (available, occupied, or
     * order_in), or null when it can no longer take orders.
     */
    public function findOrderableTable(string $tableId): ?Table
    {
        $table = Table::query()->with('tableStatus')->find($tableId);

        if (! $table) {
            return null;
        }

        $statusKey = $table->tableStatus?->key ?? $table->status;

        return in_array($statusKey, self::ORDERABLE_STATUSES, true) ? $table : null;
    }

    /**
     * Remember the table the customer is currently seated at, so they can keep
     * ordering (extra rounds) without re-picking it from the table screen.
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

    public function addItem(string $tableId, string $menuId, int $qty = 1, ?string $notes = null): void
    {
        $table = $this->resolveOrderableTable($tableId);

        $menu = Menu::query()->available()->find($menuId);

        if (! $menu) {
            throw ValidationException::withMessages([
                'cart' => 'Menu tidak ditemukan atau sedang tidak tersedia.',
            ]);
        }

        $cart = $this->cart($table->id);
        $existingQty = (int) ($cart[$menu->id]['qty'] ?? 0);
        $notes = trim((string) $notes);

        $cart[$menu->id] = [
            'menu_id' => $menu->id,
            'name' => $menu->name,
            'image_url' => $menu->image_url,
            'price' => (float) $menu->price,
            'qty' => min($existingQty + max(1, $qty), 50),
            'notes' => $notes !== '' ? $notes : ($cart[$menu->id]['notes'] ?? null),
        ];

        session([$this->cartKey($table->id) => $cart]);
    }

    public function setQty(string $tableId, string $menuId, int $qty): void
    {
        $cart = $this->cart($tableId);

        if (! isset($cart[$menuId])) {
            return;
        }

        $qty = max(1, min($qty, 50));
        $cart[$menuId]['qty'] = $qty;

        session([$this->cartKey($tableId) => $cart]);
    }

    public function setNotes(string $tableId, string $menuId, ?string $notes): void
    {
        $cart = $this->cart($tableId);

        if (! isset($cart[$menuId])) {
            return;
        }

        $notes = trim((string) $notes);
        $cart[$menuId]['notes'] = $notes !== '' ? $notes : null;

        session([$this->cartKey($tableId) => $cart]);
    }

    public function removeItem(string $tableId, string $menuId): void
    {
        $cart = $this->cart($tableId);

        unset($cart[$menuId]);

        session([$this->cartKey($tableId) => $cart]);
    }

    public function emptyCart(string $tableId): void
    {
        session()->forget($this->cartKey($tableId));
    }

    /**
     * Turn the cart into a confirmed order and notify the kitchen.
     */
    public function placeOrder(string $tableId, ?string $notes = null): Order
    {
        $table = $this->resolveOrderableTable($tableId);
        $cart = $this->cart($table->id);

        if ($cart === []) {
            throw ValidationException::withMessages([
                'cart' => 'Cart masih kosong.',
            ]);
        }

        $orderInStatus = TableStatus::query()->where('key', 'order_in')->first();

        $order = DB::transaction(function () use ($table, $cart, $notes, $orderInStatus): Order {
            $subtotal = (float) collect($cart)
                ->sum(fn (array $item) => ((float) $item['price']) * ((int) $item['qty']));

            $order = Order::query()->create([
                'table_id' => $table->id,
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => Auth::user()?->name,
                'customer_id' => Auth::id(),
                'status' => 'confirmed',
                'notes' => trim('Sumber: CUSTOMER ORDER | '.((string) $notes)),
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

            // A QR check-in already flips the table to "occupied"; an incoming
            // order should advance either state to "order_in".
            if ($orderInStatus && in_array($table->tableStatus?->key, ['available', 'occupied'], true)) {
                $table->update(['table_status_id' => $orderInStatus->id]);
            }

            return $order;
        });

        $this->emptyCart($table->id);

        OrderCreated::dispatch($order);

        return $order;
    }

    /**
     * @return \Illuminate\Support\Collection<int,array{menu_id:string,name:string,image_url:?string,price:float,qty:int,notes:?string}>
     */
    public function cartItems(string $tableId): \Illuminate\Support\Collection
    {
        return collect($this->cart($tableId))->values();
    }

    public function cartCount(string $tableId): int
    {
        return (int) collect($this->cart($tableId))->sum('qty');
    }

    public function cartSubtotal(string $tableId): float
    {
        return (float) collect($this->cart($tableId))
            ->sum(fn (array $item) => ((float) $item['price']) * ((int) $item['qty']));
    }

    private function paginateMenus(string $search = '', ?int $categoryId = null, int $perPage = 24): LengthAwarePaginator
    {
        $search = trim($search);

        return Menu::query()
            ->with('category')
            ->available()
            ->when($categoryId, fn ($query) => $query->where('menu_category_id', $categoryId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    private function resolveOrderableTable(string $tableId): Table
    {
        $table = $this->findOrderableTable($tableId);

        if (! $table) {
            throw ValidationException::withMessages([
                'table_id' => 'Meja tidak bisa menerima pesanan saat ini. Pilih meja lain.',
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
