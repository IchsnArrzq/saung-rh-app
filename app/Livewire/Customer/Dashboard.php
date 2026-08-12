<?php

namespace App\Livewire\Customer;

use App\Domains\Customer\QueryUseCases\GetCustomerDashboardQueryUseCase;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['portal' => 'customer'])]
class Dashboard extends Component
{
    public function render(GetCustomerDashboardQueryUseCase $dashboard)
    {
        return view('livewire.customer.dashboard', $dashboard->handle());
    }
}
