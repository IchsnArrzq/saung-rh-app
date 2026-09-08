<?php

namespace App\Livewire\Staff\Receptionist;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Table\Enums\TableStatus;
use App\Models\Table;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class TableMap extends Component
{
    /** Tile columns used by the fallback layout. */
    private const COLUMNS = 5;

    public ?string $selectedTableId = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $selectedTable = null;

    public function selectTable(string $tableId): void
    {
        $table = Table::query()
            ->with('tableCategory')
            ->find($tableId);

        if (! $table) {
            return;
        }

        $session = $table->activeSession();
        $activeOrder = $table->orders()
            ->whereIn('status', OrderStatus::inServiceValues())
            ->latest('ordered_at')
            ->first();

        $this->selectedTableId = $table->id;
        $this->selectedTable = [
            'code' => (string) $table->code,
            'name' => (string) $table->name,
            'capacity' => (int) $table->capacity,
            'category' => (string) ($table->tableCategory?->name ?? '-'),
            'status' => TableStatus::tryFrom((string) $table->status)?->label() ?? '-',
            'status_key' => (string) $table->status,
            'session_pax' => $session?->pax,
            'session_started' => $session?->started_at?->format('H:i'),
            'order_number' => $activeOrder?->order_number,
            'order_status' => $activeOrder?->status->label(),
        ];
    }

    /**
     * Give every table its own cell.
     *
     * Tables carry optional `position_x`/`position_y`; the rest fall back to a
     * sequential grid. The fallback used to be `index % columns`, which happily
     * handed out a cell another table had already claimed through its stored
     * coordinates — the tiles then stacked on top of each other. So: claim the
     * stored cells first, then drop the leftovers into the first free one.
     *
     * @param  Collection<int, Table>  $tables
     * @return Collection<int, array{model: Table, x: int, y: int}>
     */
    private function layout(Collection $tables): Collection
    {
        /** @var array<string, true> $taken */
        $taken = [];
        /** @var array<int, array{model: Table, x: int, y: int}> $placed */
        $placed = [];
        /** @var array<int, Table> $pending */
        $pending = [];

        foreach ($tables as $table) {
            $x = $table->position_x;
            $y = $table->position_y;

            // No coordinates, or a duplicate of a cell that is already claimed.
            if (is_null($x) || is_null($y) || isset($taken[$x.':'.$y])) {
                $pending[] = $table;

                continue;
            }

            $taken[$x.':'.$y] = true;
            $placed[] = ['model' => $table, 'x' => (int) $x, 'y' => (int) $y];
        }

        $cursor = 0;
        foreach ($pending as $table) {
            while (isset($taken[($cursor % self::COLUMNS).':'.intdiv($cursor, self::COLUMNS)])) {
                $cursor++;
            }

            $x = $cursor % self::COLUMNS;
            $y = intdiv($cursor, self::COLUMNS);

            $taken[$x.':'.$y] = true;
            $placed[] = ['model' => $table, 'x' => $x, 'y' => $y];
        }

        return collect($placed)
            ->sortBy([['y', 'asc'], ['x', 'asc']])
            ->values();
    }

    /**
     * Tally per status, already labelled — the backing values ("order_in") must
     * never reach the screen.
     *
     * @param  Collection<int, Table>  $tables
     * @return Collection<int, array{label: string, color: string, count: int}>
     */
    private function summarise(Collection $tables): Collection
    {
        return $tables
            ->groupBy(fn (Table $table) => (string) $table->status)
            ->map(function (Collection $group, string $status): array {
                $case = TableStatus::tryFrom($status);

                return [
                    'label' => $case?->label() ?? 'Status lain',
                    'color' => $case?->color() ?? 'ghost',
                    'count' => $group->count(),
                    'sort' => $case?->sortOrder() ?? 99,
                ];
            })
            ->sortBy('sort')
            ->values();
    }

    public function render(): View
    {
        $tables = Table::query()
            ->orderBy('code')
            ->get();

        $positioned = $this->layout($tables);

        return view('livewire.staff.receptionist.table-map', [
            'positioned' => $positioned,
            'rows' => (int) ($positioned->max('y') ?? 0) + 1,
            'columns' => max(self::COLUMNS, (int) ($positioned->max('x') ?? 0) + 1),
            'summary' => $this->summarise($tables),
        ]);
    }
}
