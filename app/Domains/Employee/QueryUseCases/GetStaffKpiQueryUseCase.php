<?php

namespace App\Domains\Employee\QueryUseCases;

use App\Domains\Employee\Repositories\StaffActivityRepository;
use App\Domains\Reporting\Enums\AnalyticsRange;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * The staff leaderboard: tips earned, services logged and special requests
 * completed inside a time window, combined into one comparable score.
 */
class GetStaffKpiQueryUseCase
{
    /**
     * Weights of the composite score. Tips are divided down so a single large
     * tip cannot outrank a shift's worth of work, and a handled special request
     * counts double a routine service because it is the harder job.
     */
    private const TIP_DIVISOR = 10000;

    private const REQUEST_WEIGHT = 2;

    public function __construct(private readonly StaffActivityRepository $activity) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function handle(AnalyticsRange $range, int $limit = 8): Collection
    {
        $since = $range->startsAt();

        $tips = $this->activity->tipTotalsByWaiterSince($since);
        $services = $this->activity->serviceCountsByWaiterSince($since);
        $requests = $this->activity->completedRequestCountsByStaffSince($since);

        $staffIds = $tips->keys()
            ->merge($services->keys())
            ->merge($requests->keys())
            ->filter()
            ->unique();

        if ($staffIds->isEmpty()) {
            return collect();
        }

        $names = User::query()->whereIn('id', $staffIds)->pluck('name', 'id');

        return $staffIds
            ->map(function (string $id) use ($tips, $services, $requests, $names): array {
                $tipTotal = (float) ($tips[$id]['total'] ?? 0);
                $serviceCount = (int) ($services[$id] ?? 0);
                $requestCount = (int) ($requests[$id] ?? 0);

                return [
                    'id' => $id,
                    'name' => $names[$id] ?? '—',
                    'tips_total' => $tipTotal,
                    'tips_count' => (int) ($tips[$id]['count'] ?? 0),
                    'services_count' => $serviceCount,
                    'requests_done' => $requestCount,
                    'score' => round(
                        ($tipTotal / self::TIP_DIVISOR) + $serviceCount + ($requestCount * self::REQUEST_WEIGHT),
                        1,
                    ),
                ];
            })
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }
}
