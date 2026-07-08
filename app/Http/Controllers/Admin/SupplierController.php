<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        return view('admin.suppliers.index');
    }

    public function create(): View
    {
        return view('admin.suppliers.create');
    }

    public function edit(Supplier $supplier): View
    {
        return view('admin.suppliers.edit', [
            'supplier' => $supplier,
        ]);
    }
}
