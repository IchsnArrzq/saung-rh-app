<?php

namespace App\Livewire\Customer;

use App\Services\Customer\DashboardService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['portal' => 'customer'])]
class Dashboard extends Component
{
    public function render(DashboardService $service)
    {
        return view('livewire.customer.dashboard', $service->data());
    }
}
