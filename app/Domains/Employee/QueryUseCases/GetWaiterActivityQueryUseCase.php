<?php

namespace App\Domains\Employee\QueryUseCases;

use App\Domains\Employee\Repositories\StaffActivityRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * One waiter's own log screen: today's tip tally plus their latest entries.
 */
class GetWaiterActivityQueryUseCase
{
    public function __construct(private readonly StaffActivityRepository $activity) {}

    /**
     * @return array{
     *     tipsTotal: float,
     *     tipsCount: int,
     *     recentTips: Collection,
     *     recentServices: Collection
     * }
     */
    public function handle(?string $waiterId = null, int $limit = 8): array
    {
        $waiterId ??= (string) Auth::id();
        $today = today();

        return [
            'tipsTotal' => $this->activity->tipsTotalForDay($waiterId, $today),
            'tipsCount' => $this->activity->tipsCountForDay($waiterId, $today),
            'recentTips' => $this->activity->recentTips($waiterId, $limit),
            'recentServices' => $this->activity->recentServiceLogs($waiterId, $limit),
        ];
    }
}
