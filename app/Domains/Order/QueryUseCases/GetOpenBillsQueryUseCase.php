<?php

namespace App\Domains\Order\QueryUseCases;

use App\Domains\Order\Repositories\OrderRepositoryInterface;
use App\Domains\Order\Services\OrderBillingService;
use App\Models\Order;
use Illuminate\Support\Collection;

/**
 * The cashier's open-bill worklist.
 *
 * The repository filters on status, but whether money is genuinely still owed
 * depends on Payment records — so the final filter runs over the summarised
 * bills rather than in SQL.
 */
class GetOpenBillsQueryUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly OrderBillingService $billing,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function handle(string $search = ''): Collection
    {
        return $this->orders->openBills($search)
            ->map(fn (Order $order) => $this->billing->summarize($order))
            ->filter(fn (array $bill) => $bill['outstanding'] > 0.0)
            ->values();
    }
}
