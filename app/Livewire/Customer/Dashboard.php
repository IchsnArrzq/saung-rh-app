<?php

namespace App\Livewire\Customer;

use App\Domains\Customer\QueryUseCases\GetCustomerDashboardQueryUseCase;
use App\Domains\Order\QueryUseCases\GetCustomerOrdersQueryUseCase;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['portal' => 'customer'])]
class Dashboard extends Component
{
    /**
     * Dua QueryUseCase, bukan satu: pesanan dibaca lewat domain Order sendiri
     * supaya portal pelanggan tidak menjangkau OrderRepository dari domain
     * sebelah — persis yang ditandai `@todo Fase D` pada dashboard query.
     */
    public function render(
        GetCustomerDashboardQueryUseCase $dashboard,
        GetCustomerOrdersQueryUseCase $orders,
    ) {
        return view('livewire.customer.dashboard', [
            ...$dashboard->handle(),
            'activeOrders' => $orders->active(),
        ]);
    }
}
