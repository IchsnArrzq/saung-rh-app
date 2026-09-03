<?php

namespace App\Domains\Employee\QueryUseCases;

use App\Domains\Order\Repositories\OrderRepository;
use App\Domains\Reporting\Enums\AnalyticsRange;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * The manager's most-valuable-customers panel, by spend on finished orders.
 *
 * Sits in Employee because it is one of the manager's screens; the spend itself
 * comes from the Order repository rather than a query written here.
 *
 * @todo Fase D — read the Order domain through a contract instead of its
 *       repository (ARCHITECTURE.md § Domain Dependencies).
 */
class GetTopCustomersQueryUseCase
{
    public function __construct(private readonly OrderRepository $orders) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function handle(AnalyticsRange $range, int $limit = 8): Collection
    {
        $rows = $this->orders->topSpendersSince($range->startsAt(), $limit);

        if ($rows->isEmpty()) {
            return collect();
        }

        $names = User::query()->whereIn('id', $rows->pluck('customer_id'))->pluck('name', 'id');

        return $rows->map(fn ($row): array => [
            'id' => $row->customer_id,
            'name' => $names[$row->customer_id] ?? 'Tamu',
            'orders_count' => (int) $row->orders_count,
            'total_spend' => (float) $row->total_spend,
        ])->values();
    }
}
