<?php

namespace App\Livewire\Staff\Manager;

use App\Domains\Employee\QueryUseCases\GetTopCustomersQueryUseCase;
use App\Domains\Reporting\Enums\AnalyticsRange;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class TopCustomers extends Component
{
    #[Url(as: 'range', except: 'month')]
    public string $range = 'month';

    public function setRange(string $range): void
    {
        // Ignore anything unknown rather than snapping back to the default —
        // the panel keeps whatever window the manager was already looking at.
        $this->range = AnalyticsRange::tryFrom($range)?->value ?? $this->range;
    }

    public function render(GetTopCustomersQueryUseCase $topCustomers): View
    {
        $customers = $topCustomers->handle(AnalyticsRange::fromRequest($this->range));

        return view('livewire.staff.manager.top-customers', [
            'customers' => $customers,
            'maxSpend' => max(1.0, (float) $customers->max('total_spend')),
        ]);
    }
}
