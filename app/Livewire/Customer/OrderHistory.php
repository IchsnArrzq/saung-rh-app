<?php

namespace App\Livewire\Customer;

use App\Domains\Order\QueryUseCases\GetCustomerOrdersQueryUseCase;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['portal' => 'customer'])]
class OrderHistory extends Component
{
    use WithPagination;

    public function render(GetCustomerOrdersQueryUseCase $orders): View
    {
        return view('livewire.customer.order-history', [
            'orders' => $orders->history(),
        ]);
    }
}
