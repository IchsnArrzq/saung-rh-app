<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\DashboardService;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    public function __invoke(): View
    {
        return view('customer.dashboard', $this->dashboardService->data());
    }
}
