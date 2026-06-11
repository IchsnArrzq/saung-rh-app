<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getReportData(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        $validStatuses = ['confirmed', 'preparing', 'ready', 'served', 'paid'];

        // Total Pendapatan
        $totalSales = Order::query()
            ->whereBetween('ordered_at', [$start, $end])
            ->where('status', 'paid')
            ->sum('total');

        // Jumlah Pesanan
        $totalCustomers = Order::query()
            ->whereBetween('ordered_at', [$start, $end])
            ->whereIn('status', $validStatuses)
            ->count();

        // Menu Terlaris
        $bestSellingMenus = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.ordered_at', [$start, $end])
            ->whereIn('orders.status', $validStatuses)
            ->select('order_items.menu_name_snapshot', DB::raw('SUM(order_items.qty) as total_qty'))
            ->groupBy('order_items.menu_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Pendapatan Per Kasir
        $revenuePerCashier = Order::query()
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->whereBetween('orders.ordered_at', [$start, $end])
            ->where('orders.status', 'paid')
            ->select('users.name', DB::raw('SUM(orders.total) as total_revenue'), DB::raw('COUNT(orders.id) as total_orders'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_revenue')
            ->get();

        // Chart Data Penjualan
        $orders = Order::query()
            ->whereBetween('ordered_at', [$start, $end])
            ->where('status', 'paid')
            ->get(['ordered_at', 'total']);

        $diffInDays = $start->diffInDays($end);
        $trend = collect();

        if ($diffInDays <= 1) {
            $grouped = $orders->groupBy(fn($o) => Carbon::parse($o->ordered_at)->format('H:00'))
                ->map(fn($group) => (float) $group->sum('total'));

            for ($i = 0; $i <= 23; $i++) {
                $hour = str_pad((string) $i, 2, '0', STR_PAD_LEFT) . ':00';
                $trend->put($hour, $grouped->get($hour, 0));
            }
        } elseif ($diffInDays <= 60) {
            $grouped = $orders->groupBy(fn($o) => Carbon::parse($o->ordered_at)->format('Y-m-d'))
                ->map(fn($group) => (float) $group->sum('total'));

            $period = CarbonPeriod::create($start, $end);
            foreach ($period as $date) {
                $key = $date->format('Y-m-d');
                $label = $date->format('d M');
                $trend->put($label, $grouped->get($key, 0));
            }
        } else {
            $grouped = $orders->groupBy(fn($o) => Carbon::parse($o->ordered_at)->format('Y-m'))
                ->map(fn($group) => (float) $group->sum('total'));

            $period = CarbonPeriod::create($start->copy()->startOfMonth(), '1 month', $end->copy()->startOfMonth());
            foreach ($period as $date) {
                $key = $date->format('Y-m');
                $label = $date->format('M Y');
                $trend->put($label, $grouped->get($key, 0));
            }
        }

        return [
            'totalSales' => (float) $totalSales,
            'totalCustomers' => $totalCustomers,
            'bestSellingMenus' => $bestSellingMenus,
            'revenuePerCashier' => $revenuePerCashier,
            'chartLabels' => $trend->keys()->values()->toArray(),
            'chartValues' => $trend->values()->toArray(),
        ];
    }
}
