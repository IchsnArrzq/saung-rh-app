<?php

namespace App\Domains\Employee\Repositories;

use App\Models\ServiceLog;
use App\Models\Tip;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Tips and service logs, treated as one aggregate: a waiter records both from
 * the same screen and is scored on both from the same leaderboard, so splitting
 * them into two repositories would only mean two objects always injected
 * together.
 */
interface StaffActivityRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createTip(array $attributes): Tip;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createServiceLog(array $attributes): ServiceLog;

    /** Sum of one waiter's tips for a calendar day. */
    public function tipsTotalForDay(string $waiterId, CarbonInterface $day): float;

    /** How many tips one waiter received on a calendar day. */
    public function tipsCountForDay(string $waiterId, CarbonInterface $day): int;

    /**
     * @return Collection<int, Tip>
     */
    public function recentTips(string $waiterId, int $limit = 8): Collection;

    /**
     * @return Collection<int, ServiceLog>
     */
    public function recentServiceLogs(string $waiterId, int $limit = 8): Collection;

    /**
     * Tip totals per waiter since `$since`: waiter_id => ['total' => float, 'count' => int].
     *
     * @return SupportCollection<string, array{total: float, count: int}>
     */
    public function tipTotalsByWaiterSince(CarbonInterface $since): SupportCollection;

    /**
     * Service-log counts per waiter since `$since`: waiter_id => int.
     *
     * @return SupportCollection<string, int>
     */
    public function serviceCountsByWaiterSince(CarbonInterface $since): SupportCollection;

    /**
     * Completed special requests per assignee since `$since`: user_id => int.
     *
     * Cross-domain read — special requests belong to the Social domain, which
     * Fase C10 has yet to build. It is here because the staff leaderboard scores
     * them, and the alternative was a raw query inside the QueryUseCase.
     *
     * @return SupportCollection<string, int>
     */
    public function completedRequestCountsByStaffSince(CarbonInterface $since): SupportCollection;
}
