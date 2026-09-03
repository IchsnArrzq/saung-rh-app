<?php

namespace App\Domains\Reporting\QueryUseCases;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Repositories\OrderRepository;
use App\Domains\Reporting\Services\SalesTrendService;
use Carbon\Carbon;

/**
 * The sales report board: revenue, order count, best sellers, revenue per
 * cashier and the trend chart for a date range.
 *
 * Pure composition — every figure comes from the Order repository, and the
 * only logic here is picking which orders count.
 */
class GetSalesReportQueryUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly SalesTrendService $trend,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Revenue counts settled orders only, but the order count and the best
        // sellers also include tickets still in service — so the board reflects
        // a running day, not just what has already been paid for. Drafts are
        // excluded: nobody has committed to them.
        $countedStatuses = [...OrderStatus::inServiceValues(), OrderStatus::Paid->value];

        $chart = $this->trend->build($this->orders->paidTotalsBetween($start, $end), $start, $end);

        return [
            'totalSales' => $this->orders->sumPaidTotalBetween($start, $end),
            'totalCustomers' => $this->orders->countBetweenWithStatuses($start, $end, $countedStatuses),
            'bestSellingMenus' => $this->orders->topMenuItemsBetween($start, $end, $countedStatuses),
            'revenuePerCashier' => $this->orders->revenueByCashierBetween($start, $end),
            'chartLabels' => $chart['labels'],
            'chartValues' => $chart['values'],
        ];
    }
}
