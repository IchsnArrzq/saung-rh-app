<?php

namespace App\Livewire\Staff\Receptionist;

use App\Models\Table;
use Illuminate\View\View;
use Livewire\Component;

class TableMap extends Component
{
    public ?string $selectedTableId = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $selectedTable = null;

    public function selectTable(string $tableId): void
    {
        $table = Table::query()
            ->with(['tableStatus', 'tableCategory'])
            ->find($tableId);

        if (! $table) {
            return;
        }

        $session = $table->activeSession();
        $activeOrder = $table->orders()
            ->whereIn('status', ['confirmed', 'preparing', 'ready', 'served'])
            ->latest('ordered_at')
            ->first();

        $this->selectedTableId = $table->id;
        $this->selectedTable = [
            'code' => (string) $table->code,
            'name' => (string) $table->name,
            'capacity' => (int) $table->capacity,
            'category' => (string) ($table->tableCategory?->name ?? '-'),
            'status' => (string) ($table->tableStatus?->name ?? '-'),
            'status_key' => (string) ($table->tableStatus?->key ?? ''),
            'session_pax' => $session?->pax,
            'session_started' => $session?->started_at?->format('H:i'),
            'order_number' => $activeOrder?->order_number,
            'order_status' => $activeOrder?->status,
        ];
    }

    public function render(): View
    {
        $tables = Table::query()
            ->with('tableStatus')
            ->orderBy('code')
            ->get();

        // Resolve a grid position for every table; fall back to a sequential
        // 5-column layout for tables that have no stored coordinates.
        $columns = 5;
        $positioned = $tables->values()->map(function (Table $table, int $index) use ($columns): array {
            $x = $table->position_x;
            $y = $table->position_y;

            if (is_null($x) || is_null($y)) {
                $x = $index % $columns;
                $y = intdiv($index, $columns);
            }

            return [
                'model' => $table,
                'x' => (int) $x,
                'y' => (int) $y,
            ];
        });

        $maxRow = $positioned->max('y') ?? 0;

        return view('livewire.staff.receptionist.table-map', [
            'positioned' => $positioned,
            'rows' => $maxRow + 1,
            'summary' => $tables->groupBy(fn (Table $t) => $t->tableStatus?->key ?? 'unknown')
                ->map(fn ($group) => $group->count()),
        ]);
    }
}
