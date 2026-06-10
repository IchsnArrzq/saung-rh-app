<?php

namespace App\Services\Admin;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Table;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $today = now()->toDateString();

        return [
            'metrics' => [
                [
                    'label' => 'Penjualan Hari Ini',
                    'value' => $this->rupiah($this->todayRevenue($today)),
                    'icon' => 'ri-wallet-3-line',
                    'tone' => 'primary',
                    'caption' => 'Pembayaran berhasil',
                ],
                [
                    'label' => 'Order Aktif',
                    'value' => (string) $this->activeOrders(),
                    'icon' => 'ri-restaurant-line',
                    'tone' => 'warning',
                    'caption' => 'Belum selesai',
                ],
                [
                    'label' => 'Meja Terisi',
                    'value' => (string) $this->occupiedTables(),
                    'icon' => 'ri-layout-grid-line',
                    'tone' => 'success',
                    'caption' => 'Status in use',
                ],
                [
                    'label' => 'Menu Tersedia',
                    'value' => (string) $this->availableMenus(),
                    'icon' => 'ri-bowl-line',
                    'tone' => 'info',
                    'caption' => 'Siap dijual',
                ],
            ],
            'queue' => [
                [
                    'label' => 'Draft',
                    'value' => $this->ordersByStatus('draft'),
                    'class' => 'badge-ghost',
                ],
                [
                    'label' => 'Preparing',
                    'value' => $this->ordersByStatus('preparing'),
                    'class' => 'badge-warning',
                ],
                [
                    'label' => 'Ready',
                    'value' => $this->ordersByStatus('ready'),
                    'class' => 'badge-info',
                ],
                [
                    'label' => 'Served',
                    'value' => $this->ordersByStatus('served'),
                    'class' => 'badge-success',
                ],
            ],
            'today_reservations' => $this->todayReservations($today),
        ];
    }

    private function todayRevenue(string $today): float
    {
        return (float) Payment::query()
            ->where('status', 'paid')
            ->whereDate('paid_at', $today)
            ->sum('amount');
    }

    private function activeOrders(): int
    {
        return Order::query()
            ->whereIn('status', ['draft', 'confirmed', 'preparing', 'ready', 'served'])
            ->count();
    }

    private function occupiedTables(): int
    {
        return Table::query()
            ->whereIn('status', ['occupied', 'order_in'])
            ->count();
    }

    private function availableMenus(): int
    {
        return Menu::query()
            ->where('is_available', true)
            ->count();
    }

    private function ordersByStatus(string $status): int
    {
        return Order::query()
            ->where('status', $status)
            ->count();
    }

    private function todayReservations(string $today): int
    {
        return Reservation::query()
            ->whereDate('reservation_at', $today)
            ->count();
    }

    private function rupiah(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
