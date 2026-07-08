<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(): View
    {
        return view('admin.sales.index');
    }

    public function create(): View
    {
        return view('admin.sales.create');
    }

    public function edit(Sale $sale): View
    {
        return view('admin.sales.edit', [
            'sale' => $sale,
        ]);
    }
}
