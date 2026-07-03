<?php

namespace App\Services\SpecialRequests;

use App\Models\SpecialRequest;
use App\Models\TableSession;
use App\Models\User;

interface SpecialRequestServiceInterface
{
    /**
     * Customer submits a special request from their table session.
     */
    public function submit(TableSession $session, string $category, string $description, bool $isPaid = false, ?float $price = null): SpecialRequest;

    /**
     * Manager approves a pending request, then it is auto-matched to a waiter.
     */
    public function approve(SpecialRequest $request, User $manager): SpecialRequest;

    public function reject(SpecialRequest $request, User $manager): SpecialRequest;

    /**
     * Matchmaking: route the request to the most available waiter — on-shift
     * today first, then whoever currently carries the lightest active load.
     */
    public function autoMatch(SpecialRequest $request): ?User;

    public function assign(SpecialRequest $request, User $waiter): SpecialRequest;

    public function complete(SpecialRequest $request): SpecialRequest;
}
