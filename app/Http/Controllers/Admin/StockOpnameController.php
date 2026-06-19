<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
}
