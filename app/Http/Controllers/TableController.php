<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TableController extends Controller
{
    private const STATUS_OPTIONS = ['available', 'occupied', 'order_in', 'cleaning'];

    public function index(): View
    {
        $tables = Table::query()->latest()->paginate(12);

        return view('admin.tables.index', [
            'tables' => $tables,
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function create(): View
    {
        return view('admin.tables.create', [
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', Rule::unique('tables', 'code')],
            'name' => ['nullable', 'string', 'max:120'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(self::STATUS_OPTIONS)],
            'notes' => ['nullable', 'string'],
        ]);

        Table::create($validated);

        return redirect()->route('tables.index')->with('success', 'Meja berhasil ditambahkan.');
    }

    public function edit(Table $table): View
    {
        return view('admin.tables.edit', [
            'table' => $table,
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function update(Request $request, Table $table): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', Rule::unique('tables', 'code')->ignore($table->id)],
            'name' => ['nullable', 'string', 'max:120'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(self::STATUS_OPTIONS)],
            'notes' => ['nullable', 'string'],
        ]);

        $table->update($validated);

        return redirect()->route('tables.index')->with('success', 'Meja berhasil diperbarui.');
    }

    public function destroy(Table $table): RedirectResponse
    {
        $table->delete();

        return redirect()->route('tables.index')->with('success', 'Meja berhasil dihapus.');
    }
}
