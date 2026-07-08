<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockOpname;
use Illuminate\View\View;

class StockOpnameController extends Controller
{
    public function index(): View
    {
        return view('admin.stock-opnames.index');
    }

    public function create(): View
    {
        return view('admin.stock-opnames.create');
    }

    public function edit(StockOpname $stockOpname): View
    {
        return view('admin.stock-opnames.edit', [
            'opname' => $stockOpname,
        ]);
    }

    public function movements(): View
    {
        return view('admin.stock-movements.index');
    }
}
