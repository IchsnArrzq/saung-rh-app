<?php

namespace App\Livewire\Admin\System;

use App\Domains\System\Enums\SubscriptionStatus;
use App\Domains\System\QueryUseCases\GetLicenseStatusQueryUseCase;
use App\Domains\System\UseCases\SaveLicenseUseCase;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class LicenseManager extends Component
{
    public ?string $subscriptionId = null;

    public string $plan = 'starter';

    public string $license_key = '';

    public string $status = 'active';

    public string $seats = '';

    public string $expires_at = '';

    public string $notes = '';

    public function mount(GetLicenseStatusQueryUseCase $license): void
    {
        $sub = $license->current();

        if ($sub) {
            $this->subscriptionId = $sub->id;
            $this->plan = $sub->plan;
            $this->license_key = $sub->license_key;
            $this->status = $sub->status->value;
            $this->seats = (string) ($sub->seats ?? '');
            $this->expires_at = $sub->expires_at?->format('Y-m-d') ?? '';
            $this->notes = (string) $sub->notes;
        }
    }

    public function save(SaveLicenseUseCase $saveLicense): void
    {
        $data = $this->validate([
            'plan' => ['required', 'string', 'max:60'],
            'license_key' => ['required', 'string', 'max:120', Rule::unique('subscriptions', 'license_key')->ignore($this->subscriptionId)],
            'status' => ['required', Rule::in(SubscriptionStatus::values())],
            'seats' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $sub = $saveLicense->handle($this->subscriptionId, [
            'plan' => $data['plan'],
            'license_key' => $data['license_key'],
            'status' => $data['status'],
            'seats' => $data['seats'] !== '' ? (int) $data['seats'] : null,
            'expires_at' => $data['expires_at'] ?: null,
            'notes' => $data['notes'] ?: null,
        ]);

        $this->subscriptionId = $sub->id;

        session()->flash('success', 'Lisensi diperbarui.');
    }

    public function render(GetLicenseStatusQueryUseCase $license): View
    {
        return view('livewire.admin.system.license-manager', [
            'summary' => $license->summary(),
            'statuses' => SubscriptionStatus::cases(),
        ]);
    }
}
