<?php

namespace App\Domains\System\UseCases;

use App\Domains\System\Repositories\SubscriptionRepository;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class SaveLicenseUseCase
{
    public function __construct(private readonly SubscriptionRepository $subscriptions) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(?string $subscriptionId, array $attributes): Subscription
    {
        // A brand-new licence starts counting from now; an edit must not reset
        // the original start date.
        if (! $subscriptionId) {
            $attributes['started_at'] = now();
        }

        return DB::transaction(fn (): Subscription => $this->subscriptions->updateOrCreate($subscriptionId, $attributes));
    }
}
