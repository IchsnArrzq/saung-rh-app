<?php

namespace App\Domains\Social\QueryUseCases;

use App\Domains\Social\Repositories\SpecialRequestRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * The three views over special requests: the manager's approval queue, one
 * waiter's worklist, and a guest's own history.
 */
class GetSpecialRequestBoardQueryUseCase
{
    public function __construct(private readonly SpecialRequestRepository $requests) {}

    /**
     * @return array{pending: Collection, recent: Collection}
     */
    public function forManager(int $recentLimit = 10): array
    {
        return [
            'pending' => $this->requests->pending(),
            'recent' => $this->requests->recentlyHandled($recentLimit),
        ];
    }

    /**
     * @return array{assigned: Collection, doneToday: int}
     */
    public function forWaiter(string $waiterId): array
    {
        return [
            'assigned' => $this->requests->openFor($waiterId),
            'doneToday' => $this->requests->countDoneTodayFor($waiterId),
        ];
    }

    public function forSession(?string $sessionId, int $limit = 8): Collection
    {
        return $sessionId ? $this->requests->forSession($sessionId, $limit) : new Collection;
    }
}
