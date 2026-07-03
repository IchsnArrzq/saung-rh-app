<?php

namespace App\Services\Admin;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface OrderServiceInterface
{
    public const STATUS_OPTIONS = ['draft', 'confirmed', 'preparing', 'ready', 'served', 'paid', 'cancelled'];

    public function paginate(int $perPage = 12, string $search = ''): LengthAwarePaginator;

    /**
     * @return array<int, string>
     */
    public function statusOptions(): array;

    public function tables(): Collection;

    public function availableMenus(): Collection;

    public function withItems(Order $order): Order;

    public function create(Request $request): void;

    public function update(Request $request, Order $order): void;

    public function delete(Order $order): void;
}
