<?php

namespace App\Domains\System\QueryUseCases;

use App\Domains\System\Repositories\SubscriptionRepository;
use App\Models\Subscription;

/**
 * Licence state for banners and the settings screen.
 *
 * Replaces App\Services\Settings\LicenseService — all three of its methods were
 * reads, so a QueryUseCase over a repository says the same thing with one class
 * fewer.
 */
class GetLicenseStatusQueryUseCase
{
    /** How close to expiry the banner starts warning. */
    private const EXPIRY_WARNING_DAYS = 7;

    public function __construct(private readonly SubscriptionRepository $subscriptions) {}

    public function current(): ?Subscription
    {
        return $this->subscriptions->current();
    }

    public function isValid(): bool
    {
        return (bool) $this->current()?->isValid();
    }

    /**
     * @return array{state:string, label:string, days:?int, plan:?string}
     */
    public function summary(): array
    {
        $subscription = $this->current();

        if (! $subscription) {
            return ['state' => 'none', 'label' => 'Belum ada lisensi', 'days' => null, 'plan' => null];
        }

        $days = $subscription->daysRemaining();

        $state = match (true) {
            ! $subscription->isValid() => 'expired',
            $days !== null && $days <= self::EXPIRY_WARNING_DAYS => 'expiring',
            default => 'active',
        };

        $label = match ($state) {
            'expired' => 'Lisensi kedaluwarsa',
            'expiring' => "Lisensi berakhir dalam {$days} hari",
            default => 'Lisensi aktif',
        };

        return ['state' => $state, 'label' => $label, 'days' => $days, 'plan' => $subscription->plan];
    }
}
