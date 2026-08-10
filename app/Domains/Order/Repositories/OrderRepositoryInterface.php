<?php

namespace App\Domains\Order\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * All Order persistence and querying. No business rules live here.
 *
 * Kept behind an interface (unlike most Services) because swapping the data
 * source and mocking in tests are real, recurring needs — see AGENTS.md
 * § Repository.
 */
interface OrderRepositoryInterface
{
    /** Admin order listing with search, item counts and paid totals. */
    public function paginateForAdmin(int $perPage = 12, string $search = ''): LengthAwarePaginator;

    /** Kitchen queue: confirmed + preparing, VIP-track orders first. */
    public function kitchenOngoing(): Collection;

    /**
     * Ids of the kitchen queue in the order the cooks work through it — enough
     * to tell a guest their position without loading whole orders.
     *
     * @return array<int, string>
     */
    public function kitchenQueueIds(): array;

    /** Kitchen queue: plated and waiting to be served. */
    public function kitchenReady(): Collection;

    /** Kitchen queue: finished today, newest first. */
    public function kitchenCompletedToday(int $limit = 50): Collection;

    /** Orders that are neither paid nor cancelled and still carry a balance. */
    public function openBills(string $search = ''): Collection;

    /** Open (unsettled) orders sitting on one table — a party may run several rounds. */
    public function openOrdersForTable(string $tableId): Collection;

    /** Today's in-progress orders for one table, with their lines — the guest's tracker. */
    public function activeForTable(string $tableId): Collection;

    /** Orders live on the floor right now, newest first — the waiter's picker. */
    public function inServiceRecent(int $limit = 50): Collection;

    /**
     * Customers ranked by spend on finished orders since `$since`. Rows carry
     * `customer_id`, `orders_count` and `total_spend`.
     *
     * @return Collection<int, Order>
     */
    public function topSpendersSince(CarbonInterface $since, int $limit = 8): Collection;

    // --- Reporting reads (dashboard + sales report) ---

    public function countByStatus(string $status): int;

    /** Orders live on the floor right now: confirmed through served. */
    public function countInService(): int;

    /** Kitchen-bound orders that have been waiting longer than `$minutes`. */
    public function countStaleKitchenOrders(int $minutes): int;

    /** Latest orders for the dashboard feed, table and cashier eager-loaded. */
    public function recent(int $limit = 6): Collection;

    /**
     * Best-selling lines on one day. Rows carry `menu_name_snapshot`,
     * `total_qty` and `total_revenue`.
     *
     * @return Collection<int, \App\Models\OrderItem>
     */
    public function topMenuItemsForDate(CarbonInterface $date, int $limit = 5): Collection;

    /**
     * Best-selling lines across a window, counting the given order statuses.
     *
     * @param  array<int, string>  $statuses
     * @return Collection<int, \App\Models\OrderItem>
     */
    public function topMenuItemsBetween(CarbonInterface $start, CarbonInterface $end, array $statuses, int $limit = 5): Collection;

    /** Sum of settled order totals in a window. */
    public function sumPaidTotalBetween(CarbonInterface $start, CarbonInterface $end): float;

    /**
     * How many orders in a window carry any of the given statuses.
     *
     * @param  array<int, string>  $statuses
     */
    public function countBetweenWithStatuses(CarbonInterface $start, CarbonInterface $end, array $statuses): int;

    /**
     * Settled revenue per cashier in a window. Rows carry `name`,
     * `total_revenue` and `total_orders`.
     *
     * @return Collection<int, Order>
     */
    public function revenueByCashierBetween(CarbonInterface $start, CarbonInterface $end): Collection;

    /**
     * Bare `ordered_at` + `total` of settled orders in a window — the raw
     * material the sales trend is bucketed from.
     *
     * @return Collection<int, Order>
     */
    public function paidTotalsBetween(CarbonInterface $start, CarbonInterface $end): Collection;

    public function find(string $id): ?Order;

    public function findWithItems(string $id): ?Order;

    /** Everything the admin receipt modal prints: table, cashier, lines and payments. */
    public function findForDetail(string $id): ?Order;

    public function orderNumberExists(string $orderNumber): bool;

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, array<string, mixed>>  $items
     */
    public function createWithItems(array $attributes, array $items): Order;

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, array<string, mixed>>  $items
     */
    public function updateWithItems(Order $order, array $attributes, array $items): Order;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Order $order, array $attributes): Order;

    /** Cascade a status onto every line of an order (kitchen ticket moves as a whole). */
    public function updateItemsStatus(Order $order, string $status): void;

    public function delete(Order $order): void;

    public function findItem(string $orderId, string $itemId): ?OrderItem;

    public function deleteItem(OrderItem $item): void;
}
