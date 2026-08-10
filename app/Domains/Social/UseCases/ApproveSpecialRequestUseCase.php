<?php

namespace App\Domains\Social\UseCases;

use App\Domains\Employee\Repositories\ShiftRepositoryInterface;
use App\Domains\Social\Enums\SpecialRequestStatus;
use App\Domains\Social\Repositories\SpecialRequestRepositoryInterface;
use App\Domains\Social\Services\WaiterMatchmaker;
use App\Domains\System\Repositories\UserRepositoryInterface;
use App\Models\SpecialRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * A manager approves a pending request; it is then routed straight to the
 * best-placed waiter so nobody has to hand it out by hand.
 *
 * Approval and matchmaking share one transaction: an approved request with no
 * assignee is a request that quietly falls through the floor.
 *
 * @todo Fase D — ask the Employee domain who is on shift through an event or
 *       read contract instead of its repository (ARCHITECTURE.md § Domain
 *       Dependencies).
 */
class ApproveSpecialRequestUseCase
{
    public function __construct(
        private readonly SpecialRequestRepositoryInterface $requests,
        private readonly UserRepositoryInterface $users,
        private readonly ShiftRepositoryInterface $shifts,
        private readonly WaiterMatchmaker $matchmaker,
    ) {}

    public function handle(string $requestId, User $manager): SpecialRequest
    {
        $request = $this->requests->find($requestId);

        if (! $request) {
            throw ValidationException::withMessages(['request' => 'Permintaan tidak ditemukan.']);
        }

        return DB::transaction(function () use ($request, $manager): SpecialRequest {
            $this->requests->update($request, [
                'status' => SpecialRequestStatus::Approved->value,
                'approved_by' => $manager->id,
            ]);

            $waiter = $this->pickWaiter();

            if ($waiter) {
                $this->requests->update($request, [
                    'status' => SpecialRequestStatus::Assigned->value,
                    'assigned_to' => $waiter->id,
                ]);
            }

            // Nobody available is a legitimate outcome, not a failure: the
            // request stays "approved" and the board shows it unassigned.
            return $request->refresh();
        });
    }

    private function pickWaiter(): ?User
    {
        $waiters = $this->users->activeWithRole('waiter');

        if ($waiters->isEmpty()) {
            return null;
        }

        $waiterIds = $waiters->pluck('id')->all();

        return $this->matchmaker->pick(
            $waiters,
            $this->shifts->onShiftUserIdsForDate($waiterIds, today()),
            $this->requests->activeLoadByAssignee($waiterIds),
        );
    }
}
