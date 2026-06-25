<?php

namespace App\Services\Manager;

use App\Models\Order;
use App\Models\ServiceLog;
use App\Models\SpecialRequest;
use App\Models\Tip;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ManagerAnalyticsService
{
    public function rangeStart(string $range): CarbonImmutable
    {
        return match ($range) {
            'today' => CarbonImmutable::now()->startOfDay(),
            'month' => CarbonImmutable::now()->startOfMonth(),
            default => CarbonImmutable::now()->startOfWeek(),
        };
    }

    /**
     * Staff KPI leaderboard: tips earned, services logged and special requests
     * completed, combined into a simple composite score.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function topStaff(string $range, int $limit = 8): Collection
    {
        $start = $this->rangeStart($range);

        $tips = Tip::query()
            ->where('received_at', '>=', $start)
            ->selectRaw('waiter_id, sum(amount) as total, count(*) as cnt')
            ->groupBy('waiter_id')
            ->get()
            ->keyBy('waiter_id');

        $services = ServiceLog::query()
            ->where('served_at', '>=', $start)
            ->selectRaw('waiter_id, count(*) as cnt')
            ->groupBy('waiter_id')
            ->pluck('cnt', 'waiter_id');

        $requests = SpecialRequest::query()
            ->where('status', 'done')
            ->where('handled_at', '>=', $start)
            ->selectRaw('assigned_to, count(*) as cnt')
            ->groupBy('assigned_to')
            ->pluck('cnt', 'assigned_to');

        $staffIds = collect($tips->keys())
            ->merge($services->keys())
            ->merge($requests->keys())
            ->filter()
            ->unique();

        if ($staffIds->isEmpty()) {
            return collect();
        }

        $names = User::query()->whereIn('id', $staffIds)->pluck('name', 'id');

        return $staffIds
            ->map(function ($id) use ($tips, $services, $requests, $names): array {
                $tipTotal = (float) ($tips[$id]->total ?? 0);
                $serviceCount = (int) ($services[$id] ?? 0);
                $requestCount = (int) ($requests[$id] ?? 0);

                return [
                    'id' => $id,
                    'name' => $names[$id] ?? '—',
                    'tips_total' => $tipTotal,
                    'tips_count' => (int) ($tips[$id]->cnt ?? 0),
                    'services_count' => $serviceCount,
                    'requests_done' => $requestCount,
                    // Composite KPI: tips weighted lightly, activity weighted.
                    'score' => round(($tipTotal / 10000) + $serviceCount + ($requestCount * 2), 1),
                ];
            })
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    /**
     * Most valuable customers by completed-order spend.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function topCustomers(string $range, int $limit = 8): Collection
    {
        $start = $this->rangeStart($range);

        $rows = Order::query()
            ->whereNotNull('customer_id')
            ->whereIn('status', ['served', 'paid'])
            ->where('ordered_at', '>=', $start)
            ->select('customer_id', DB::raw('count(*) as orders_count'), DB::raw('sum(total) as total_spend'))
            ->groupBy('customer_id')
            ->orderByDesc('total_spend')
            ->limit($limit)
            ->get();

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
