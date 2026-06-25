<?php

namespace App\Repositories\Admin;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function paidRevenueBetween(Carbon $start, Carbon $end): float
    {
        return (float) Payment::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');
    }

    public function paidOrdersBetween(Carbon $start, Carbon $end): int
    {
        return Payment::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->distinct('order_id')
            ->count('order_id');
    }

    public function activeOrders(): int
    {
        return Order::query()
            ->whereIn('status', ['confirmed', 'preparing', 'ready', 'served'])
            ->count();
    }

    public function tablesByStatus(string $status): int
    {
        return Table::query()
            ->whereHas('tableStatus', fn ($q) => $q->where('key', $status))
            ->count();
    }

    public function availableMenus(): int
    {
        return Menu::query()
            ->available()
            ->count();
    }

    public function totalMenus(): int
    {
        return Menu::query()->count();
    }

    public function ordersByStatus(string $status): int
    {
        return Order::query()
            ->where('status', $status)
            ->count();
    }

    public function todayReservations(Carbon $today): int
    {
        return Reservation::query()
            ->whereDate('reservation_at', $today)
            ->count();
    }

    public function topMenus(Carbon $today): Collection
    {
        return OrderItem::query()
            ->selectRaw('menu_name_snapshot, SUM(qty) as total_qty, SUM(line_total) as total_revenue')
            ->whereHas('order', function ($query) use ($today): void {
                $query->whereDate('ordered_at', $today)
                    ->whereIn('status', ['served', 'paid']);
            })
            ->groupBy('menu_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();
    }

    public function recentOrders(): Collection
    {
        return Order::query()
            ->with(['table:id,code,name', 'cashier:id,name'])
            ->latest('ordered_at')
            ->limit(6)
            ->get();
    }

    public function reservationList(Carbon $today): Collection
    {
        return Reservation::query()
            ->with('table:id,code,name')
            ->whereDate('reservation_at', $today)
            ->orderBy('reservation_at')
            ->limit(6)
            ->get();
    }

    public function paymentMethods(Carbon $today): Collection
    {
        return Payment::query()
            ->selectRaw('method, COUNT(*) as total_count, SUM(amount) as total_amount')
            ->where('status', 'paid')
            ->whereDate('paid_at', $today)
            ->groupBy('method')
            ->orderByDesc('total_amount')
            ->get();
    }

    public function staleOrders(int $minutes): int
    {
        return Order::query()
            ->whereIn('status', ['confirmed', 'preparing'])
            ->where('ordered_at', '<=', now()->subMinutes($minutes))
            ->count();
    }

    public function unavailableMenus(): int
    {
        return Menu::query()
            ->unavailable()
            ->count();
    }
}
