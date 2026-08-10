<?php

namespace App\Domains\Order\Repositories;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class OrderRepository implements OrderRepositoryInterface
{
    public function paginateForAdmin(int $perPage = 12, string $search = ''): LengthAwarePaginator
    {
        return Order::query()
            ->with('table')
            ->withCount('items')
            ->withSum(['payments as paid_total' => fn (Builder $query) => $query->where('status', PaymentStatus::Paid->value)], 'amount')
            ->tap(fn (Builder $query) => $this->applySearch($query, $search))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function kitchenOngoing(): Collection
    {
        return Order::query()
            ->with(['items.menu', 'table'])
            ->withCount($this->vipItemsCount())
            ->whereIn('status', [OrderStatus::Confirmed->value, OrderStatus::Preparing->value])
            ->orderByDesc('vip_items_count') // VIP track jumps the queue
            ->orderBy('ordered_at')
            ->get();
    }

    public function kitchenQueueIds(): array
    {
        return Order::query()
            ->withCount($this->vipItemsCount())
            ->whereIn('status', [OrderStatus::Confirmed->value, OrderStatus::Preparing->value])
            ->orderByDesc('vip_items_count')
            ->orderBy('ordered_at')
            ->pluck('id')
            ->all();
    }

    public function kitchenReady(): Collection
    {
        return Order::query()
            ->with(['items.menu', 'table'])
            ->withCount($this->vipItemsCount())
            ->where('status', OrderStatus::Ready->value)
            ->orderBy('updated_at')
            ->get();
    }

    public function kitchenCompletedToday(int $limit = 50): Collection
    {
        return Order::query()
            ->with(['items.menu', 'table'])
            ->withCount($this->vipItemsCount())
            ->whereIn('status', [OrderStatus::Served->value, OrderStatus::Paid->value])
            ->whereDate('updated_at', Carbon::today())
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }

    public function openBills(string $search = ''): Collection
    {
        return Order::query()
            ->with(['table', 'items', 'payments'])
            ->tap(fn (Builder $query) => $this->applyOpenBill($query))
            ->tap(fn (Builder $query) => $this->applySearch($query, $search, withStatus: false))
            ->orderBy('ordered_at')
            ->get();
    }

    public function openOrdersForTable(string $tableId): Collection
    {
        return Order::query()
            ->with('payments')
            ->where('table_id', $tableId)
            ->tap(fn (Builder $query) => $this->applyOpenBill($query))
            ->get();
    }

    public function activeForTable(string $tableId): Collection
    {
        return Order::query()
            ->with(['items:id,order_id,menu_name_snapshot,qty,status'])
            ->where('table_id', $tableId)
            ->whereIn('status', [
                OrderStatus::Confirmed->value,
                OrderStatus::Preparing->value,
                OrderStatus::Ready->value,
            ])
            ->whereDate('ordered_at', Carbon::today())
            ->orderByDesc('ordered_at')
            ->get();
    }

    public function inServiceRecent(int $limit = 50): Collection
    {
        return Order::query()
            ->whereIn('status', OrderStatus::inServiceValues())
            ->orderByDesc('ordered_at')
            ->limit($limit)
            ->get(['id', 'order_number', 'customer_name']);
    }

    public function topSpendersSince(CarbonInterface $since, int $limit = 8): Collection
    {
        return Order::query()
            ->whereNotNull('customer_id')
            ->whereIn('status', [OrderStatus::Served->value, OrderStatus::Paid->value])
            ->where('ordered_at', '>=', $since)
            ->selectRaw('customer_id, count(*) as orders_count, sum(total) as total_spend')
            ->groupBy('customer_id')
            ->orderByDesc('total_spend')
            ->limit($limit)
            ->get();
    }

    public function countByStatus(string $status): int
    {
        return Order::query()->where('status', $status)->count();
    }

    public function countInService(): int
    {
        return Order::query()->whereIn('status', OrderStatus::inServiceValues())->count();
    }

    public function countStaleKitchenOrders(int $minutes): int
    {
        return Order::query()
            ->whereIn('status', [OrderStatus::Confirmed->value, OrderStatus::Preparing->value])
            ->where('ordered_at', '<=', now()->subMinutes($minutes))
            ->count();
    }

    public function recent(int $limit = 6): Collection
    {
        return Order::query()
            ->with(['table:id,code,name', 'cashier:id,name'])
            ->latest('ordered_at')
            ->limit($limit)
            ->get();
    }

    public function topMenuItemsForDate(CarbonInterface $date, int $limit = 5): Collection
    {
        return OrderItem::query()
            ->selectRaw('menu_name_snapshot, SUM(qty) as total_qty, SUM(line_total) as total_revenue')
            ->whereHas('order', fn (Builder $query) => $query
                ->whereDate('ordered_at', $date)
                ->whereIn('status', [OrderStatus::Served->value, OrderStatus::Paid->value]))
            ->groupBy('menu_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();
    }

    public function topMenuItemsBetween(CarbonInterface $start, CarbonInterface $end, array $statuses, int $limit = 5): Collection
    {
        return OrderItem::query()
            ->selectRaw('menu_name_snapshot, SUM(qty) as total_qty')
            ->whereHas('order', fn (Builder $query) => $query
                ->whereBetween('ordered_at', [$start, $end])
                ->whereIn('status', $statuses))
            ->groupBy('menu_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();
    }

    public function sumPaidTotalBetween(CarbonInterface $start, CarbonInterface $end): float
    {
        return (float) Order::query()
            ->whereBetween('ordered_at', [$start, $end])
            ->where('status', OrderStatus::Paid->value)
            ->sum('total');
    }

    public function countBetweenWithStatuses(CarbonInterface $start, CarbonInterface $end, array $statuses): int
    {
        return Order::query()
            ->whereBetween('ordered_at', [$start, $end])
            ->whereIn('status', $statuses)
            ->count();
    }

    public function revenueByCashierBetween(CarbonInterface $start, CarbonInterface $end): Collection
    {
        return Order::query()
            ->join('users', 'orders.cashier_id', '=', 'users.id')
            ->whereBetween('orders.ordered_at', [$start, $end])
            ->where('orders.status', OrderStatus::Paid->value)
            ->selectRaw('users.name, SUM(orders.total) as total_revenue, COUNT(orders.id) as total_orders')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_revenue')
            ->get();
    }

    public function paidTotalsBetween(CarbonInterface $start, CarbonInterface $end): Collection
    {
        return Order::query()
            ->whereBetween('ordered_at', [$start, $end])
            ->where('status', OrderStatus::Paid->value)
            ->get(['ordered_at', 'total']);
    }

    public function find(string $id): ?Order
    {
        return Order::query()->find($id);
    }

    public function findWithItems(string $id): ?Order
    {
        return Order::query()->with('items')->find($id);
    }

    public function findForDetail(string $id): ?Order
    {
        return Order::query()
            ->with(['table', 'cashier', 'items.menu', 'payments'])
            ->find($id);
    }

    public function orderNumberExists(string $orderNumber): bool
    {
        return Order::query()->where('order_number', $orderNumber)->exists();
    }

    public function createWithItems(array $attributes, array $items): Order
    {
        $order = Order::query()->create($attributes);
        $order->items()->createMany($items);

        return $order;
    }

    public function updateWithItems(Order $order, array $attributes, array $items): Order
    {
        $order->update($attributes);
        $order->items()->delete();
        $order->items()->createMany($items);

        return $order;
    }

    public function update(Order $order, array $attributes): Order
    {
        $order->update($attributes);

        return $order;
    }

    public function updateItemsStatus(Order $order, string $status): void
    {
        $order->items()->update(['status' => $status]);
    }

    public function delete(Order $order): void
    {
        $order->delete();
    }

    public function findItem(string $orderId, string $itemId): ?OrderItem
    {
        return OrderItem::query()
            ->where('order_id', $orderId)
            ->find($itemId);
    }

    public function deleteItem(OrderItem $item): void
    {
        $item->delete();
    }

    /**
     * Unpaid, uncancelled and actually carrying a balance. Note this is a
     * status-level filter only — whether the balance is truly outstanding
     * depends on Payment records and is decided by the domain service.
     */
    private function applyOpenBill(Builder $query): void
    {
        $query
            ->whereNotIn('status', [OrderStatus::Cancelled->value, OrderStatus::Paid->value])
            ->where('total', '>', 0);
    }

    private function applySearch(Builder $query, string $search, bool $withStatus = true): void
    {
        $search = trim($search);

        if ($search === '') {
            return;
        }

        $query->where(function (Builder $inner) use ($search, $withStatus): void {
            $inner->where('order_number', 'like', '%'.$search.'%')
                ->orWhere('customer_name', 'like', '%'.$search.'%')
                ->orWhereHas('table', fn (Builder $table) => $table->where('code', 'like', '%'.$search.'%'));

            if ($withStatus) {
                $inner->orWhere('status', 'like', '%'.$search.'%');
            }
        });
    }

    /**
     * Aggregates VIP-track items per order so the kitchen board can prioritise them.
     *
     * @return array<string, callable>
     */
    private function vipItemsCount(): array
    {
        return [
            'items as vip_items_count' => fn (Builder $query) => $query->whereHas('menu', fn (Builder $menu) => $menu->where('track', 'vip')),
        ];
    }
}
