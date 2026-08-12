<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Reporting\QueryUseCases\GetAdminDashboardQueryUseCase;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(GetAdminDashboardQueryUseCase $dashboard): View
    {
        return view('dashboard', [
            'summary' => $dashboard->handle(),
        ]);
    }
}
