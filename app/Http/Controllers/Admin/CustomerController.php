<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        return view('admin.customers.index');
    }

    public function create(): View
    {
        return view('admin.customers.create');
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.edit', [
            'customer' => $customer,
        ]);
    }
}
