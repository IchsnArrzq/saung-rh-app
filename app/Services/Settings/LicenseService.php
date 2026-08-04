<?php

namespace App\Services\Settings;

use App\Models\Subscription;

class LicenseService
{
    /**
     * The active licence record (latest by expiry), if any.
     */
    public function current(): ?Subscription
    {
        return Subscription::query()->latest('expires_at')->first();
    }

    public function isValid(): bool
    {
        return (bool) $this->current()?->isValid();
    }

    /**
     * A lightweight status summary for banners / dashboards.
     *
     * @return array{state:string, label:string, days:?int, plan:?string}
     */
    public function summary(): array
    {
        $sub = $this->current();

        if (! $sub) {
            return ['state' => 'none', 'label' => 'Belum ada lisensi', 'days' => null, 'plan' => null];
        }

        $days = $sub->daysRemaining();

        $state = match (true) {
            ! $sub->isValid() => 'expired',
            $days !== null && $days <= 7 => 'expiring',
            default => 'active',
        };

        $label = match ($state) {
            'expired' => 'Lisensi kedaluwarsa',
            'expiring' => "Lisensi berakhir dalam {$days} hari",
            default => 'Lisensi aktif',
        };

        return ['state' => $state, 'label' => $label, 'days' => $days, 'plan' => $sub->plan];
    }
}
