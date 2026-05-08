<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\View\View;

class TableController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Table::class);

        return view('admin.tables.index');
    }

    public function create(): View
    {
        $this->authorize('create', Table::class);

        return view('admin.tables.create');
    }

    public function edit(Table $table): View
    {
        $this->authorize('update', $table);

        return view('admin.tables.edit', [
            'table' => $table,
        ]);
    }
}
