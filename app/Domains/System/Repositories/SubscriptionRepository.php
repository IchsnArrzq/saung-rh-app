<?php

namespace App\Domains\System\Repositories;

use App\Models\Subscription;

class SubscriptionRepository
{
    /** The licence in force — the one expiring furthest out. */
    public function current(): ?Subscription
    {
        return Subscription::query()->latest('expires_at')->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateOrCreate(?string $id, array $attributes): Subscription
    {
        return Subscription::query()->updateOrCreate(['id' => $id], $attributes);
    }
}
