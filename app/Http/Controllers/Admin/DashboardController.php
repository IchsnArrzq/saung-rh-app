<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardServiceInterface;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardServiceInterface $dashboard): View
    {
        return view('dashboard', [
            'summary' => $dashboard->summary(),
        ]);
    }
}
