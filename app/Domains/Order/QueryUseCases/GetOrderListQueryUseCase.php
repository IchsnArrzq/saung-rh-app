<?php

namespace App\Domains\Order\QueryUseCases;

use App\Domains\Order\Repositories\OrderRepository;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Read-only listing for the admin order table. No Action, no transaction —
 * pure read flows skip the write chain entirely (AGENTS.md § Read Flow).
 */
class GetOrderListQueryUseCase
{
    public function __construct(private readonly OrderRepository $orders) {}

    public function handle(string $search = '', int $perPage = 12): LengthAwarePaginator
    {
        return $this->orders->paginateForAdmin($perPage, $search);
    }

    /** One order with everything the receipt modal prints. */
    public function detail(string $id): ?Order
    {
        return $this->orders->findForDetail($id);
    }
}
