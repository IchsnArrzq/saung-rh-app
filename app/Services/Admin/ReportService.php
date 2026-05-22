<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getDailyReportData(): array
    {
        $today = Carbon::today(); 
        $validStatuses = ['confirmed', 'preparing', 'ready', 'served', 'paid'];

        // Total Pendapatan Kas Hari Ini
        $totalSales = Order::query()
            ->whereDate('ordered_at', $today)
            ->where('status', 'paid')
            ->sum('total');

        // Jumlah Pelanggan / Pesanan Masuk
        $totalCustomers = Order::query()
            ->whereDate('ordered_at', $today)
            ->whereIn('status', $validStatuses)
            ->count();

        // Menu Terlaris Hari Ini
        $bestSellingMenus = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereDate('orders.ordered_at', $today)
            ->whereIn('orders.status', $validStatuses)
            ->select('order_items.menu_name_snapshot', DB::raw('SUM(order_items.qty) as total_qty'))
            ->groupBy('order_items.menu_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Pendapatan Per Kasir
        $revenuePerCashier = Order::query()
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->whereDate('orders.ordered_at', $today)
            ->where('orders.status', 'paid')
            ->select('users.name', DB::raw('SUM(orders.total) as total_revenue'), DB::raw('COUNT(orders.id) as total_orders'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_revenue')
            ->get();

        return [
            'totalSales' => (float) $totalSales,
            'totalCustomers' => $totalCustomers,
            'bestSellingMenus' => $bestSellingMenus,
            'revenuePerCashier' => $revenuePerCashier,
        ];
    }
}
