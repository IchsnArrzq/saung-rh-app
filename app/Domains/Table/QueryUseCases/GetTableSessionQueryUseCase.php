<?php

namespace App\Domains\Table\QueryUseCases;

use App\Domains\Table\Enums\TableSessionStatus;
use App\Domains\Table\Repositories\TableSessionRepository;
use App\Models\TableSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Reads of QR table sessions. The session id a guest's browser carries comes
 * from App\Support\TableSessionContext; it is passed in rather than read here,
 * so this stays free of HTTP state.
 */
class GetTableSessionQueryUseCase
{
    public function __construct(private readonly TableSessionRepository $sessions) {}

    public function find(?string $sessionId): ?TableSession
    {
        return $sessionId ? $this->sessions->find($sessionId) : null;
    }

    /** Confirmed by staff and still open — the only state that unlocks the table panel. */
    public function active(?string $sessionId): ?TableSession
    {
        return $sessionId
            ? $this->sessions->findInStatus($sessionId, [TableSessionStatus::Active->value])
            : null;
    }

    /** Still holding the table: waiting for staff or already seated. */
    public function live(?string $sessionId): ?TableSession
    {
        return $sessionId
            ? $this->sessions->findInStatus($sessionId, TableSessionStatus::liveValues())
            : null;
    }

    public function liveForTable(string $tableId): ?TableSession
    {
        return $this->sessions->liveForTable($tableId);
    }

    /**
     * A friend at the table joins with the 4-digit code shown on the first
     * phone. hash_equals, so response time says nothing about a near miss.
     */
    public function joinable(string $tableId, string $code): ?TableSession
    {
        $session = $this->sessions->liveForTable($tableId);

        if (! $session || $session->join_code === null) {
            return null;
        }

        return hash_equals($session->join_code, $code) ? $session : null;
    }

    public function pending(): Collection
    {
        return $this->sessions->pending();
    }

    public function paginate(string $search = '', ?TableSessionStatus $status = null): LengthAwarePaginator
    {
        return $this->sessions->paginateForAdmin($search, $status);
    }
}
