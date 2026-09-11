<?php

namespace App\Domains\Social\QueryUseCases;

use App\Domains\Social\Repositories\SpecialRequestRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * What a guest sees of their own special requests under the submit form.
 * The staff side reads through GetFloorBoardQueryUseCase.
 */
class GetSpecialRequestBoardQueryUseCase
{
    public function __construct(private readonly SpecialRequestRepository $requests) {}

    public function forSession(?string $sessionId, int $limit = 8): Collection
    {
        return $sessionId ? $this->requests->forSession($sessionId, $limit) : new Collection;
    }
}
