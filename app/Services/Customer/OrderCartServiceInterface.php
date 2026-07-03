<?php

namespace App\Services\Customer;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\Table;
use App\Models\TableStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Backend for the customer dine-in ordering flow. The cart is kept per-table
 * in the session; checkout creates a confirmed Order and notifies the kitchen.
 */
interface OrderCartServiceInterface
{
    /**
     * Statuses where a seated party may place (and keep placing) orders. A
     * fresh pick requires "available", but once seated the table moves to
     * "occupied"/"order_in" and must still accept additional rounds.
     */
    public const ORDERABLE_STATUSES = ['available', 'occupied', 'order_in'];

    /**
     * @return array{statuses:Collection<int,TableStatus>,tablesByStatus:\Illuminate\Support\Collection<string,Collection<int,Table>>,unassignedTables:Collection<int,Table>}
     */
    public function tableSelectionData(string $search = ''): array;

    /**
     * @return array{menus:LengthAwarePaginator,categories:Collection<int,MenuCategory>,totalAvailable:int}
     */
    public function catalog(string $search = '', ?int $categoryId = null, int $perPage = 24): array;

    public function findAvailableTable(string $tableId): ?Table;

    public function findOrderableTable(string $tableId): ?Table;

    public function setActiveTable(string $tableId): void;

    public function activeTableId(): ?string;

    public function forgetActiveTable(): void;

    public function addItem(string $tableId, string $menuId, int $qty = 1, ?string $notes = null): void;

    public function setQty(string $tableId, string $menuId, int $qty): void;

    public function setNotes(string $tableId, string $menuId, ?string $notes): void;

    public function removeItem(string $tableId, string $menuId): void;

    public function emptyCart(string $tableId): void;

    /**
     * Turn the cart into a confirmed order and notify the kitchen.
     */
    public function placeOrder(string $tableId, ?string $notes = null): Order;

    /**
     * @return \Illuminate\Support\Collection<int,array{menu_id:string,name:string,image_url:?string,price:float,qty:int,notes:?string}>
     */
    public function cartItems(string $tableId): \Illuminate\Support\Collection;

    public function cartCount(string $tableId): int;

    public function cartSubtotal(string $tableId): float;
}
