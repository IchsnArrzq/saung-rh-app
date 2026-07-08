<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(): View
    {
        return view('admin.purchases.index');
    }

    public function create(): View
    {
        return view('admin.purchases.create');
    }

    public function edit(Purchase $purchase): View
    {
        return view('admin.purchases.edit', [
            'purchase' => $purchase,
        ]);
    }
}
