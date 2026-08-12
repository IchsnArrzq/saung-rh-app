<?php

namespace App\Domains\Social\Services;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Picks which waiter should take a special request.
 *
 * The rule, in order: someone rostered today beats someone who is not, and
 * between two equally rostered waiters the one carrying fewer open requests
 * wins. Deliberately query-free — the caller fetches the three inputs, so the
 * rule itself stays testable with plain arrays (AGENTS.md § Service).
 */
class WaiterMatchmaker
{
    /**
     * @param  Collection<int, User>  $waiters  Active waiters to choose between.
     * @param  array<int, string>  $onShiftIds  Which of them are rostered today.
     * @param  Collection<string, int>  $loads  user_id => open requests already assigned.
     */
    public function pick(Collection $waiters, array $onShiftIds, Collection $loads): ?User
    {
        if ($waiters->isEmpty()) {
            return null;
        }

        return $waiters
            ->sortBy(fn (User $waiter): array => [
                in_array($waiter->id, $onShiftIds, true) ? 0 : 1,
                (int) ($loads[$waiter->id] ?? 0),
            ])
            ->first();
    }
}
