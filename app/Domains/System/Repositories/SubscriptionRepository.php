<?php

namespace App\Domains\System\Repositories;

use App\Models\Subscription;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function current(): ?Subscription
    {
        return Subscription::query()->latest('expires_at')->first();
    }

    public function updateOrCreate(?string $id, array $attributes): Subscription
    {
        return Subscription::query()->updateOrCreate(['id' => $id], $attributes);
    }
}
