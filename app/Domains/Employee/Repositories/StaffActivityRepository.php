<?php

namespace App\Domains\Employee\Repositories;

use App\Models\ServiceLog;
use App\Models\SpecialRequest;
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
class StaffActivityRepository
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createTip(array $attributes): Tip
    {
        return Tip::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createServiceLog(array $attributes): ServiceLog
    {
        return ServiceLog::query()->create($attributes);
    }

    /** Sum of one waiter's tips for a calendar day. */
    public function tipsTotalForDay(string $waiterId, CarbonInterface $day): float
    {
        return (float) Tip::query()
            ->where('waiter_id', $waiterId)
            ->whereDate('received_at', $day)
            ->sum('amount');
    }

    /** How many tips one waiter received on a calendar day. */
    public function tipsCountForDay(string $waiterId, CarbonInterface $day): int
    {
        return Tip::query()
            ->where('waiter_id', $waiterId)
            ->whereDate('received_at', $day)
            ->count();
    }

    /**
     * @return Collection<int, Tip>
     */
    public function recentTips(string $waiterId, int $limit = 8): Collection
    {
        return Tip::query()
            ->where('waiter_id', $waiterId)
            ->with('table')
            ->latest('received_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, ServiceLog>
     */
    public function recentServiceLogs(string $waiterId, int $limit = 8): Collection
    {
        return ServiceLog::query()
            ->where('waiter_id', $waiterId)
            ->with('table')
            ->latest('served_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Tip totals per waiter since `$since`: waiter_id => ['total' => float, 'count' => int].
     *
     * @return SupportCollection<string, array{total: float, count: int}>
     */
    public function tipTotalsByWaiterSince(CarbonInterface $since): SupportCollection
    {
        return Tip::query()
            ->where('received_at', '>=', $since)
            ->selectRaw('waiter_id, sum(amount) as total, count(*) as cnt')
            ->groupBy('waiter_id')
            ->get()
            ->mapWithKeys(fn ($row) => [
                (string) $row->waiter_id => ['total' => (float) $row->total, 'count' => (int) $row->cnt],
            ]);
    }

    /**
     * Service-log counts per waiter since `$since`: waiter_id => int.
     *
     * @return SupportCollection<string, int>
     */
    public function serviceCountsByWaiterSince(CarbonInterface $since): SupportCollection
    {
        return ServiceLog::query()
            ->where('served_at', '>=', $since)
            ->selectRaw('waiter_id, count(*) as cnt')
            ->groupBy('waiter_id')
            ->pluck('cnt', 'waiter_id')
            ->map(fn ($count) => (int) $count);
    }

    /**
     * Completed special requests per assignee since `$since`: user_id => int.
     *
     * Cross-domain read — special requests belong to the Social domain, which
     * Fase C10 has yet to build. It is here because the staff leaderboard scores
     * them, and the alternative was a raw query inside the QueryUseCase.
     *
     * @return SupportCollection<string, int>
     */
    public function completedRequestCountsByStaffSince(CarbonInterface $since): SupportCollection
    {
        return SpecialRequest::query()
            ->where('status', 'done')
            ->where('handled_at', '>=', $since)
            ->selectRaw('assigned_to, count(*) as cnt')
            ->groupBy('assigned_to')
            ->pluck('cnt', 'assigned_to')
            ->map(fn ($count) => (int) $count);
    }
}
