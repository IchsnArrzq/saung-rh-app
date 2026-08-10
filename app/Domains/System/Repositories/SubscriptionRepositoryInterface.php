<?php

namespace App\Domains\System\Repositories;

use App\Models\Subscription;

interface SubscriptionRepositoryInterface
{
    /** The licence in force — the one expiring furthest out. */
    public function current(): ?Subscription;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateOrCreate(?string $id, array $attributes): Subscription;
}
