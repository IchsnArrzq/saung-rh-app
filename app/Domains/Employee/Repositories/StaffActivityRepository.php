<?php

namespace App\Domains\Employee\Repositories;

use App\Models\ServiceLog;
use App\Models\SpecialRequest;
use App\Models\Tip;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class StaffActivityRepository implements StaffActivityRepositoryInterface
{
    public function createTip(array $attributes): Tip
    {
        return Tip::query()->create($attributes);
    }

    public function createServiceLog(array $attributes): ServiceLog
    {
        return ServiceLog::query()->create($attributes);
    }

    public function tipsTotalForDay(string $waiterId, CarbonInterface $day): float
    {
        return (float) Tip::query()
            ->where('waiter_id', $waiterId)
            ->whereDate('received_at', $day)
            ->sum('amount');
    }

    public function tipsCountForDay(string $waiterId, CarbonInterface $day): int
    {
        return Tip::query()
            ->where('waiter_id', $waiterId)
            ->whereDate('received_at', $day)
            ->count();
    }

    public function recentTips(string $waiterId, int $limit = 8): Collection
    {
        return Tip::query()
            ->where('waiter_id', $waiterId)
            ->with('table')
            ->latest('received_at')
            ->limit($limit)
            ->get();
    }

    public function recentServiceLogs(string $waiterId, int $limit = 8): Collection
    {
        return ServiceLog::query()
            ->where('waiter_id', $waiterId)
            ->with('table')
            ->latest('served_at')
            ->limit($limit)
            ->get();
    }

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

    public function serviceCountsByWaiterSince(CarbonInterface $since): SupportCollection
    {
        return ServiceLog::query()
            ->where('served_at', '>=', $since)
            ->selectRaw('waiter_id, count(*) as cnt')
            ->groupBy('waiter_id')
            ->pluck('cnt', 'waiter_id')
            ->map(fn ($count) => (int) $count);
    }

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
