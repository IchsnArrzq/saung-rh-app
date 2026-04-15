<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TableStatus;
use App\Services\Admin\TableStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TableStatusController extends Controller
{
    public function __construct(private readonly TableStatusService $tableStatusService) {}

    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());

        return view('admin.table-statuses.index', [
            'tableStatuses' => $this->tableStatusService->paginate(search: $search),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.table-statuses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->tableStatusService->create($request);

        return redirect()->route('table-statuses.index')->with('success', 'Status meja berhasil ditambahkan.');
    }

    public function edit(TableStatus $tableStatus): View
    {
        return view('admin.table-statuses.edit', [
            'tableStatus' => $tableStatus,
        ]);
    }

    public function update(Request $request, TableStatus $tableStatus): RedirectResponse
    {
        $this->tableStatusService->update($request, $tableStatus);

        return redirect()->route('table-statuses.index')->with('success', 'Status meja berhasil diperbarui.');
    }

    public function destroy(TableStatus $tableStatus): RedirectResponse
    {
        $this->tableStatusService->delete($tableStatus);

        return redirect()->route('table-statuses.index')->with('success', 'Status meja berhasil dihapus.');
    }
}
