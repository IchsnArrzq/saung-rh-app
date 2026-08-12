<?php

namespace App\Livewire\Admin\StockOpnames;

use App\Models\StockOpname;
use App\Domains\Inventory\UseCases\CreateStockOpnameDraftUseCase;
use App\Domains\Inventory\UseCases\PostStockOpnameUseCase;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    public ?StockOpname $opname = null;

    public string $opname_date = '';

    public string $notes = '';

    /**
     * Editable count rows (edit mode only).
     *
     * @var array<int, array{id: string, name: string, unit: string, system_qty: string, physical_qty: string, notes: string}>
     */
    public array $rows = [];

    public function mount(?StockOpname $opname = null): void
    {
        $this->opname = $opname?->exists ? $opname : null;

        if ($this->opname) {
            $this->opname_date = $this->opname->opname_date->toDateString();
            $this->notes = (string) ($this->opname->notes ?? '');

            $this->rows = $this->opname->items()
                ->with('ingredient')
                ->get()
                ->map(fn ($item): array => [
                    'id' => $item->id,
                    'name' => $item->ingredient?->name ?? '-',
                    'unit' => $item->ingredient?->unit ?? '',
                    'system_qty' => (string) $item->system_qty,
                    'physical_qty' => $item->physical_qty !== null ? (string) $item->physical_qty : '',
                    'notes' => (string) ($item->notes ?? ''),
                ])
                ->all();

            return;
        }

        $this->opname_date = now()->toDateString();
    }

    /**
     * Create route: snapshot current stock into a new draft, then continue on
     * the edit screen to enter the physical counts.
     */
    public function startDraft(CreateStockOpnameDraftUseCase $createDraft)
    {
        $this->validate([
            'opname_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $opname = $createDraft->handle(Carbon::parse($this->opname_date), $this->notes);

        session()->flash('success', 'Draft opname dibuat. Silakan isi stok fisik.');

        return $this->redirectRoute('stock-opnames.edit', $opname, navigate: true);
    }

    /**
     * Persist the entered physical counts (edit mode). Returns the fresh opname.
     */
    private function persistCounts(): void
    {
        $this->validate([
            'rows.*.physical_qty' => ['nullable', 'numeric', 'min:0'],
            'rows.*.notes' => ['nullable', 'string', 'max:255'],
        ], [], [
            'rows.*.physical_qty' => 'stok fisik',
        ]);

        foreach ($this->rows as $row) {
            $physical = $row['physical_qty'] === '' ? null : (float) $row['physical_qty'];
            $difference = $physical === null ? 0 : $physical - (float) $row['system_qty'];

            $this->opname->items()->whereKey($row['id'])->update([
                'physical_qty' => $physical,
                'difference' => $difference,
                'notes' => $row['notes'] ?: null,
            ]);
        }
    }

    public function save()
    {
        if (! $this->opname || $this->opname->isPosted()) {
            return;
        }

        $this->persistCounts();

        session()->flash('success', 'Stok fisik berhasil disimpan.');

        return $this->redirectRoute('stock-opnames.edit', $this->opname, navigate: true);
    }

    public function post(PostStockOpnameUseCase $postOpname)
    {
        if (! $this->opname || $this->opname->isPosted()) {
            return;
        }

        $this->persistCounts();
        $postOpname->handle($this->opname);

        session()->flash('success', 'Opname diposting. Stok bahan telah disesuaikan.');

        return $this->redirectRoute('stock-opnames.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.stock-opnames.form');
    }
}
