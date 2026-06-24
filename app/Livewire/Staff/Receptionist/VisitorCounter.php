<?php

namespace App\Livewire\Staff\Receptionist;

use App\Models\Table;
use App\Models\VisitorLog;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class VisitorCounter extends Component
{
    public int $walkInPax = 1;

    public ?string $walkInTableId = null;

    public function addWalkIn(): void
    {
        $validated = $this->validate([
            'walkInPax' => ['required', 'integer', 'min:1', 'max:50'],
            'walkInTableId' => ['nullable', 'exists:tables,id'],
        ]);

        VisitorLog::query()->create([
            'table_id' => $validated['walkInTableId'] ?: null,
            'recorded_by' => auth()->id(),
            'source' => 'walk_in',
            'pax' => $validated['walkInPax'],
            'visited_at' => now(),
        ]);

        $this->reset(['walkInTableId']);
        $this->walkInPax = 1;

        session()->flash('visitor_success', 'Pengunjung walk-in dicatat.');
    }

    public function render(): View
    {
        $today = VisitorLog::query()->whereDate('visited_at', today());
        $weekStart = CarbonImmutable::now()->startOfWeek();

        $weekly = VisitorLog::query()
            ->whereBetween('visited_at', [$weekStart, CarbonImmutable::now()->endOfDay()]);

        // Last 7 days series (date => pax), filling gaps with zero.
        $raw = VisitorLog::query()
            ->selectRaw('date(visited_at) as d, count(*) as entries, sum(pax) as pax')
            ->where('visited_at', '>=', CarbonImmutable::now()->subDays(6)->startOfDay())
            ->groupByRaw('date(visited_at)')
            ->get()
            ->keyBy(fn ($row) => (string) $row->d);

        $series = collect(range(6, 0))->map(function (int $offset) use ($raw): array {
            $date = CarbonImmutable::now()->subDays($offset);
            $key = $date->format('Y-m-d');
            $row = $raw->get($key);

            return [
                'label' => $date->isoFormat('dd'),
                'date' => $date->format('d/m'),
                'pax' => (int) ($row->pax ?? 0),
                'entries' => (int) ($row->entries ?? 0),
            ];
        });

        $maxPax = max(1, (int) $series->max('pax'));

        return view('livewire.staff.receptionist.visitor-counter', [
            'todayEntries' => (clone $today)->count(),
            'todayPax' => (int) (clone $today)->sum('pax'),
            'weekEntries' => (clone $weekly)->count(),
            'weekPax' => (int) (clone $weekly)->sum('pax'),
            'series' => $series,
            'maxPax' => $maxPax,
            'tables' => Table::query()->orderBy('code')->get(['id', 'code', 'name']),
            'bySource' => (clone $today)
                ->select('source', DB::raw('count(*) as c'), DB::raw('sum(pax) as p'))
                ->groupBy('source')
                ->get()
                ->keyBy('source'),
        ]);
    }
}
