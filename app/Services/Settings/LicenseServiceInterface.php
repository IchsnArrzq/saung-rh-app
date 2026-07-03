<?php

namespace App\Services\Settings;

use App\Models\Subscription;

interface LicenseServiceInterface
{
    /**
     * The active licence record (latest by expiry), if any.
     */
    public function current(): ?Subscription;

    public function isValid(): bool;

    /**
     * A lightweight status summary for banners / dashboards.
     *
     * @return array{state:string, label:string, days:?int, plan:?string}
     */
    public function summary(): array;
}
