<?php

namespace App\Services\Manager;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

interface ManagerAnalyticsServiceInterface
{
    public function rangeStart(string $range): CarbonImmutable;

    /**
     * Staff KPI leaderboard: tips earned, services logged and special requests
     * completed, combined into a simple composite score.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function topStaff(string $range, int $limit = 8): Collection;

    /**
     * Most valuable customers by completed-order spend.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function topCustomers(string $range, int $limit = 8): Collection;
}
