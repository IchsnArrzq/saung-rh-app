<?php

namespace App\Repositories\Admin;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    public function paidRevenueBetween(Carbon $start, Carbon $end): float;

    public function paidOrdersBetween(Carbon $start, Carbon $end): int;

    public function activeOrders(): int;

    public function tablesByStatus(string $status): int;

    public function availableMenus(): int;

    public function totalMenus(): int;

    public function ordersByStatus(string $status): int;

    public function todayReservations(Carbon $today): int;

    public function topMenus(Carbon $today): Collection;

    public function recentOrders(): Collection;

    public function reservationList(Carbon $today): Collection;

    public function paymentMethods(Carbon $today): Collection;

    public function staleOrders(int $minutes): int;

    public function unavailableMenus(): int;
}
