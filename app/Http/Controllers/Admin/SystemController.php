<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SystemController extends Controller
{
    public function settings(): View
    {
        return view('admin.system.settings');
    }

    public function paymentAccounts(): View
    {
        return view('admin.system.payment-accounts');
    }

    public function license(): View
    {
        return view('admin.system.license');
    }
}
