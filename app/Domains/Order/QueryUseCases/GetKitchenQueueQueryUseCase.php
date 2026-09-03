<?php

namespace App\Domains\Order\QueryUseCases;

use App\Domains\Order\Repositories\OrderRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * The three buckets the Kitchen Display System renders. Kept as one QueryUseCase
 * rather than three near-identical classes — each method is a thin pass-through
 * to the repository.
 */
class GetKitchenQueueQueryUseCase
{
    public function __construct(private readonly OrderRepository $orders) {}

    /** Confirmed + preparing, VIP-track first. */
    public function ongoing(): Collection
    {
        return $this->orders->kitchenOngoing();
    }

    /** Plated, waiting to be picked up. */
    public function ready(): Collection
    {
        return $this->orders->kitchenReady();
    }

    /** Finished today — served or paid. */
    public function completedToday(int $limit = 50): Collection
    {
        return $this->orders->kitchenCompletedToday($limit);
    }
}
