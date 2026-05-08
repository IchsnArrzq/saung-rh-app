<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TableStatus;
use Illuminate\View\View;

class TableStatusController extends Controller
{
    public function index(): View
    {
        return view('admin.table-statuses.index');
    }

    public function create(): View
    {
        return view('admin.table-statuses.create');
    }

    public function edit(TableStatus $tableStatus): View
    {
        return view('admin.table-statuses.edit', [
            'tableStatus' => $tableStatus,
        ]);
    }
}
