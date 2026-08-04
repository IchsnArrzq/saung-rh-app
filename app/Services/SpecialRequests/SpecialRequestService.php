<?php

namespace App\Services\SpecialRequests;

use App\Models\Shift;
use App\Models\SpecialRequest;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SpecialRequestService
{
    /**
     * Customer submits a special request from their table session.
     */
    public function submit(TableSession $session, string $category, string $description, bool $isPaid = false, ?float $price = null): SpecialRequest
    {
        return SpecialRequest::query()->create([
            'table_session_id' => $session->id,
            'table_id' => $session->table_id,
            'table_code' => $session->table?->code,
            'requested_by' => $session->customer_name,
            'category' => array_key_exists($category, SpecialRequest::CATEGORIES) ? $category : 'other',
            'description' => trim($description),
            'is_paid' => $isPaid,
            'price' => $isPaid ? $price : null,
            'status' => 'pending',
        ]);
    }

    /**
     * Manager approves a pending request, then it is auto-matched to a waiter.
     */
    public function approve(SpecialRequest $request, User $manager): SpecialRequest
    {
        return DB::transaction(function () use ($request, $manager): SpecialRequest {
            $request->update([
                'status' => 'approved',
                'approved_by' => $manager->id,
            ]);

            $this->autoMatch($request);

            return $request->refresh();
        });
    }

    public function reject(SpecialRequest $request, User $manager): SpecialRequest
    {
        $request->update([
            'status' => 'rejected',
            'approved_by' => $manager->id,
            'handled_at' => now(),
        ]);

        return $request;
    }

    /**
     * Matchmaking: route the request to the most available waiter — on-shift
     * today first, then whoever currently carries the lightest active load.
     */
    public function autoMatch(SpecialRequest $request): ?User
    {
        $waiter = $this->bestWaiter();

        if (! $waiter) {
            return null;
        }

        $this->assign($request, $waiter);

        return $waiter;
    }

    public function assign(SpecialRequest $request, User $waiter): SpecialRequest
    {
        $request->update([
            'status' => 'assigned',
            'assigned_to' => $waiter->id,
        ]);

        return $request;
    }

    public function complete(SpecialRequest $request): SpecialRequest
    {
        $request->update([
            'status' => 'done',
            'handled_at' => now(),
        ]);

        return $request;
    }

    /**
     * Pick the best-fit waiter: prefer those scheduled today, break ties by the
     * fewest active (assigned) requests already on their plate.
     */
    private function bestWaiter(): ?User
    {
        $waiters = User::query()->role('waiter')->where('is_active', true)->get();

        if ($waiters->isEmpty()) {
            return null;
        }

        $onShiftIds = Shift::query()
            ->forDate(today())
            ->where('status', 'scheduled')
            ->whereIn('user_id', $waiters->pluck('id'))
            ->pluck('user_id')
            ->all();

        $loads = SpecialRequest::query()
            ->where('status', 'assigned')
            ->whereIn('assigned_to', $waiters->pluck('id'))
            ->selectRaw('assigned_to, count(*) as c')
            ->groupBy('assigned_to')
            ->pluck('c', 'assigned_to');

        return $waiters
            ->sortBy(fn (User $w): array => [
                in_array($w->id, $onShiftIds, true) ? 0 : 1,
                (int) ($loads[$w->id] ?? 0),
            ])
            ->first();
    }
}
