<?php

namespace App\Domains\Order\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
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

    /** Kitchen queue: plated and waiting to be served. */
    public function kitchenReady(): Collection;

    /** Kitchen queue: finished today, newest first. */
    public function kitchenCompletedToday(int $limit = 50): Collection;

    /** Orders that are neither paid nor cancelled and still carry a balance. */
    public function openBills(string $search = ''): Collection;

    /** Open (unsettled) orders sitting on one table — a party may run several rounds. */
    public function openOrdersForTable(string $tableId): Collection;

    public function find(string $id): ?Order;

    public function findWithItems(string $id): ?Order;

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

    public function findItem(string $orderId, string $itemId): ?OrderItem;

    public function deleteItem(OrderItem $item): void;
}
