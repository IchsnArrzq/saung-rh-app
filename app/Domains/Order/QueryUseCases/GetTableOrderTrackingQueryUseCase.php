<?php

namespace App\Domains\Order\QueryUseCases;

use App\Domains\Order\Repositories\OrderRepository;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

/**
 * What a guest sees while waiting: their table's live tickets, each tagged with
 * its place in the kitchen queue.
 *
 * The position is computed from the same ordering the KDS works from, so the
 * number shown to the guest matches the board in the kitchen.
 */
class GetTableOrderTrackingQueryUseCase
{
    public function __construct(private readonly OrderRepository $orders) {}

    /**
     * @return array{orders: Collection<int, Order>, queueTotal: int}
     */
    public function handle(?string $tableId): array
    {
        if (! $tableId) {
            return ['orders' => new Collection, 'queueTotal' => 0];
        }

        $queueIds = $this->orders->kitchenQueueIds();
        $positions = array_flip($queueIds);

        $orders = $this->orders->activeForTable($tableId)
            ->each(function (Order $order) use ($positions): void {
                $index = $positions[$order->id] ?? null;

                // Plated orders have left the queue, so they carry no position.
                $order->setAttribute('queue_position', $index === null ? null : $index + 1);
            });

        return ['orders' => $orders, 'queueTotal' => count($queueIds)];
    }
}
