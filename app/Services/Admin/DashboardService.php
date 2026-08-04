<?php

namespace App\Services\Admin;

use App\Repositories\Admin\DashboardRepositoryInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;

class DashboardService
{
    public function __construct(
        private readonly DashboardRepositoryInterface $dashboardRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $today = today();
        $yesterday = today()->subDay();
        $todayRevenue = $this->dashboardRepository->paidRevenueBetween($today->copy()->startOfDay(), $today->copy()->endOfDay());
        $yesterdayRevenue = $this->dashboardRepository->paidRevenueBetween($yesterday->copy()->startOfDay(), $yesterday->copy()->endOfDay());
        $todayPaidOrders = $this->dashboardRepository->paidOrdersBetween($today->copy()->startOfDay(), $today->copy()->endOfDay());
        $averageTransaction = $todayPaidOrders > 0 ? $todayRevenue / $todayPaidOrders : 0;

        return [
            'metrics' => [
                [
                    'label' => 'Penjualan Hari Ini',
                    'value' => $this->rupiah($todayRevenue),
                    'icon' => 'ri-wallet-3-line',
                    'tone' => 'primary',
                    'caption' => $this->trendCaption($todayRevenue, $yesterdayRevenue),
                ],
                [
                    'label' => 'Order Aktif',
                    'value' => (string) $this->dashboardRepository->activeOrders(),
                    'icon' => 'ri-restaurant-line',
                    'tone' => 'warning',
                    'caption' => 'Confirmed sampai served',
                ],
                [
                    'label' => 'Reservasi Hari Ini',
                    'value' => (string) $this->dashboardRepository->todayReservations($today),
                    'icon' => 'ri-calendar-check-line',
                    'tone' => 'info',
                    'caption' => 'Jadwal kedatangan',
                ],
                [
                    'label' => 'Rata-rata Transaksi',
                    'value' => $this->rupiah($averageTransaction),
                    'icon' => 'ri-line-chart-line',
                    'tone' => 'success',
                    'caption' => $todayPaidOrders.' transaksi paid',
                ],
            ],
            'order_statuses' => [
                [
                    'label' => 'Draft',
                    'value' => $this->dashboardRepository->ordersByStatus('draft'),
                    'class' => 'badge-ghost',
                ],
                [
                    'label' => 'Confirmed',
                    'value' => $this->dashboardRepository->ordersByStatus('confirmed'),
                    'class' => 'badge-primary',
                ],
                [
                    'label' => 'Preparing',
                    'value' => $this->dashboardRepository->ordersByStatus('preparing'),
                    'class' => 'badge-warning',
                ],
                [
                    'label' => 'Ready',
                    'value' => $this->dashboardRepository->ordersByStatus('ready'),
                    'class' => 'badge-info',
                ],
                [
                    'label' => 'Served',
                    'value' => $this->dashboardRepository->ordersByStatus('served'),
                    'class' => 'badge-success',
                ],
                [
                    'label' => 'Cancelled',
                    'value' => $this->dashboardRepository->ordersByStatus('cancelled'),
                    'class' => 'badge-error',
                ],
            ],
            'table_statuses' => [
                [
                    'label' => 'Available',
                    'value' => $this->dashboardRepository->tablesByStatus('available'),
                    'icon' => 'ri-layout-grid-line',
                    'tone' => 'success',
                ],
                [
                    'label' => 'Occupied',
                    'value' => $this->dashboardRepository->tablesByStatus('occupied'),
                    'icon' => 'ri-restaurant-2-line',
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Order In',
                    'value' => $this->dashboardRepository->tablesByStatus('order_in'),
                    'icon' => 'ri-shopping-bag-3-line',
                    'tone' => 'primary',
                ],
                [
                    'label' => 'Cleaning',
                    'value' => $this->dashboardRepository->tablesByStatus('cleaning'),
                    'icon' => 'ri-brush-line',
                    'tone' => 'info',
                ],
            ],
            'availability' => [
                [
                    'label' => 'Menu Tersedia',
                    'value' => $this->dashboardRepository->availableMenus(),
                    'total' => $this->dashboardRepository->totalMenus(),
                    'icon' => 'ri-bowl-line',
                ],
            ],
            'sales_chart' => $this->salesChart(),
            'top_menus' => $this->dashboardRepository->topMenus($today),
            'recent_orders' => $this->dashboardRepository->recentOrders(),
            'today_reservations' => $this->dashboardRepository->reservationList($today),
            'payment_methods' => $this->dashboardRepository->paymentMethods($today),
            'alerts' => $this->alerts($today),
            'shortcuts' => $this->shortcuts(),
        ];
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    private function salesChart(): array
    {
        $start = today()->subDays(6);
        $end = today();
        $labels = [];
        $values = [];

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $labels[] = $date->translatedFormat('d M');
            $values[] = $this->dashboardRepository->paidRevenueBetween($date->copy()->startOfDay(), $date->copy()->endOfDay());
        }

        return compact('labels', 'values');
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function alerts(Carbon $today): array
    {
        $alerts = [];
        $pendingReservations = $this->dashboardRepository->todayReservations($today);
        $staleOrders = $this->dashboardRepository->staleOrders(30);
        $unavailableMenus = $this->dashboardRepository->unavailableMenus();

        if ($staleOrders > 0) {
            $alerts[] = [
                'label' => $staleOrders.' order aktif lebih dari 30 menit',
                'icon' => 'ri-time-line',
                'class' => 'alert-warning',
            ];
        }

        if ($pendingReservations > 0) {
            $alerts[] = [
                'label' => $pendingReservations.' reservasi hari ini perlu dipantau',
                'icon' => 'ri-calendar-event-line',
                'class' => 'alert-info',
            ];
        }

        if ($unavailableMenus > 0) {
            $alerts[] = [
                'label' => $unavailableMenus.' menu sedang tidak tersedia',
                'icon' => 'ri-error-warning-line',
                'class' => 'alert-error',
            ];
        }

        return $alerts;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function shortcuts(): array
    {
        return [
            [
                'label' => 'Buka POS',
                'url' => route('pos.order.index'),
                'icon' => 'ri-cash-line',
            ],
            [
                'label' => 'Order Baru',
                'url' => route('orders.create'),
                'icon' => 'ri-add-circle-line',
            ],
            [
                'label' => 'Kelola Menu',
                'url' => route('menus.index'),
                'icon' => 'ri-bowl-line',
            ],
            [
                'label' => 'Laporan',
                'url' => route('reports.index'),
                'icon' => 'ri-bar-chart-box-line',
            ],
        ];
    }

    private function trendCaption(float $todayRevenue, float $yesterdayRevenue): string
    {
        if ($yesterdayRevenue <= 0) {
            return $todayRevenue > 0 ? 'Mulai ada penjualan' : 'Belum ada penjualan';
        }

        $percentage = (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100;
        $prefix = $percentage >= 0 ? '+' : '';

        return $prefix.number_format($percentage, 1, ',', '.').'% dari kemarin';
    }

    private function rupiah(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
