<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Order::class);

        return view('admin.orders.index');
    }

    public function create(): View
    {
        $this->authorize('create', Order::class);

        return view('admin.orders.create');
    }

    public function edit(Order $order): View
    {
        $this->authorize('update', $order);

        return view('admin.orders.edit', [
            'order' => $order,
        ]);
    }
}
