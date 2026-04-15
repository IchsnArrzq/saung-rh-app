<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Services\Admin\TableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TableController extends Controller
{
    public function __construct(private readonly TableService $tableService) {}

    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $tables = $this->tableService->paginate(search: $search);

        return view('admin.tables.index', [
            'tables' => $tables,
            'statusOptions' => $this->tableService->boardStatuses(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.tables.create', [
            'statusOptions' => $this->tableService->statusOptions(),
            'categoryOptions' => $this->tableService->categoryOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->tableService->create($request);

        return redirect()->route('tables.index')->with('success', 'Meja berhasil ditambahkan.');
    }

    public function edit(Table $table): View
    {
        return view('admin.tables.edit', [
            'table' => $table,
            'statusOptions' => $this->tableService->statusOptions($table),
            'categoryOptions' => $this->tableService->categoryOptions($table),
        ]);
    }

    public function update(Request $request, Table $table): RedirectResponse
    {
        $this->tableService->update($request, $table);

        return redirect()->route('tables.index')->with('success', 'Meja berhasil diperbarui.');
    }

    public function destroy(Table $table): RedirectResponse
    {
        $this->tableService->delete($table);

        return redirect()->route('tables.index')->with('success', 'Meja berhasil dihapus.');
    }

    public function updateStatus(Request $request, Table $table): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'table_status_id' => ['required', 'exists:table_statuses,id'],
        ]);

        $this->tableService->updateStatus($table, $validated['table_status_id']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Status meja berhasil diperbarui.',
            ]);
        }

        return redirect()->route('tables.index')->with('success', 'Status meja berhasil diperbarui.');
    }
}
