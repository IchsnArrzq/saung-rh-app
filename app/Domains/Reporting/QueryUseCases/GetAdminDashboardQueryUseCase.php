<?php

namespace App\Domains\Reporting\QueryUseCases;

use App\Domains\Menu\Repositories\MenuRepositoryInterface;
use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Repositories\OrderRepositoryInterface;
use App\Domains\Payment\Repositories\PaymentRepositoryInterface;
use App\Domains\Reservation\Repositories\ReservationRepositoryInterface;
use App\Domains\Table\Enums\TableStatus;
use App\Domains\Table\Repositories\TableRepositoryInterface;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;

/**
 * Everything the admin landing page shows, assembled from each domain's own
 * repository rather than from queries written here (ARCHITECTURE.md § Reporting
 * is read-only and crosses domains).
 *
 * Replaces App\Services\Admin\DashboardService together with the last legacy
 * repository, App\Repositories\Admin\DashboardRepository.
 *
 * @todo Fase D — read the other domains through contracts rather than their
 *       repositories (ARCHITECTURE.md § Domain Dependencies).
 */
class GetAdminDashboardQueryUseCase
{
    /** How long a kitchen ticket may sit before the dashboard flags it. */
    private const STALE_ORDER_MINUTES = 30;

    /** Days shown in the little revenue sparkline, today included. */
    private const SALES_CHART_DAYS = 7;

    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly PaymentRepositoryInterface $payments,
        private readonly TableRepositoryInterface $tables,
        private readonly MenuRepositoryInterface $menus,
        private readonly ReservationRepositoryInterface $reservations,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        $today = today();
        $yesterday = $today->copy()->subDay();

        $todayRevenue = $this->revenueForDay($today);
        $yesterdayRevenue = $this->revenueForDay($yesterday);
        $todayPaidOrders = $this->payments->countSettledOrdersBetween($today->copy()->startOfDay(), $today->copy()->endOfDay());
        $averageTransaction = $todayPaidOrders > 0 ? $todayRevenue / $todayPaidOrders : 0.0;
        $todayReservations = $this->reservations->countForDate($today->toDateString());

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
                    'value' => (string) $this->orders->countInService(),
                    'icon' => 'ri-restaurant-line',
                    'tone' => 'warning',
                    'caption' => 'Confirmed sampai served',
                ],
                [
                    'label' => 'Reservasi Hari Ini',
                    'value' => (string) $todayReservations,
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
            'order_statuses' => $this->orderStatusChips(),
            'table_statuses' => $this->tableStatusTiles(),
            'availability' => [
                [
                    'label' => 'Menu Tersedia',
                    'value' => $this->menus->countAvailable(),
                    'total' => $this->menus->countAll(),
                    'icon' => 'ri-bowl-line',
                ],
            ],
            'sales_chart' => $this->salesChart(),
            'top_menus' => $this->orders->topMenuItemsForDate($today),
            'recent_orders' => $this->orders->recent(),
            'today_reservations' => $this->reservations->listForDate($today),
            'payment_methods' => $this->payments->methodBreakdownForDate($today),
            'alerts' => $this->alerts($todayReservations),
            'shortcuts' => $this->shortcuts(),
        ];
    }

    /**
     * The order chips. Paid is deliberately absent — today's settled money
     * already has its own metric card at the top.
     *
     * @return array<int, array<string, mixed>>
     */
    private function orderStatusChips(): array
    {
        $chips = [
            [OrderStatus::Draft, 'badge-ghost'],
            [OrderStatus::Confirmed, 'badge-primary'],
            [OrderStatus::Preparing, 'badge-warning'],
            [OrderStatus::Ready, 'badge-info'],
            [OrderStatus::Served, 'badge-success'],
            [OrderStatus::Cancelled, 'badge-error'],
        ];

        return array_map(fn (array $chip): array => [
            'label' => $chip[0]->label(),
            'value' => $this->orders->countByStatus($chip[0]->value),
            'class' => $chip[1],
        ], $chips);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function tableStatusTiles(): array
    {
        $tiles = [
            [TableStatus::Available, 'ri-layout-grid-line', 'success'],
            [TableStatus::Occupied, 'ri-restaurant-2-line', 'warning'],
            [TableStatus::OrderIn, 'ri-shopping-bag-3-line', 'primary'],
            [TableStatus::Cleaning, 'ri-brush-line', 'info'],
        ];

        return array_map(fn (array $tile): array => [
            'label' => $tile[0]->label(),
            'value' => $this->tables->countByStatus($tile[0]->value),
            'icon' => $tile[1],
            'tone' => $tile[2],
        ], $tiles);
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    private function salesChart(): array
    {
        $start = today()->subDays(self::SALES_CHART_DAYS - 1);
        $labels = [];
        $values = [];

        foreach (CarbonPeriod::create($start, today()) as $date) {
            $labels[] = $date->translatedFormat('d M');
            $values[] = $this->revenueForDay($date);
        }

        return compact('labels', 'values');
    }

    private function revenueForDay(CarbonInterface $day): float
    {
        return $this->payments->sumSettledBetween($day->copy()->startOfDay(), $day->copy()->endOfDay());
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function alerts(int $todayReservations): array
    {
        $alerts = [];
        $staleOrders = $this->orders->countStaleKitchenOrders(self::STALE_ORDER_MINUTES);
        $unavailableMenus = $this->menus->countUnavailable();

        if ($staleOrders > 0) {
            $alerts[] = [
                'label' => $staleOrders.' order aktif lebih dari '.self::STALE_ORDER_MINUTES.' menit',
                'icon' => 'ri-time-line',
                'class' => 'alert-warning',
            ];
        }

        if ($todayReservations > 0) {
            $alerts[] = [
                'label' => $todayReservations.' reservasi hari ini perlu dipantau',
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
            ['label' => 'Buka POS', 'url' => route('pos.order.index'), 'icon' => 'ri-cash-line'],
            ['label' => 'Order Baru', 'url' => route('orders.create'), 'icon' => 'ri-add-circle-line'],
            ['label' => 'Kelola Menu', 'url' => route('menus.index'), 'icon' => 'ri-bowl-line'],
            ['label' => 'Laporan', 'url' => route('reports.index'), 'icon' => 'ri-bar-chart-box-line'],
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
