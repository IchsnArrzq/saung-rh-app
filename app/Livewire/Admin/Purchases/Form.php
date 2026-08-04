<?php

namespace App\Livewire\Admin\Purchases;

use App\Models\Ingredient;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\Admin\PurchaseService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    public ?Purchase $purchase = null;

    public string $supplier_id = '';

    public string $purchase_date = '';

    public string $notes = '';

    /**
     * @var array<int, array{ingredient_id: string, qty: string, unit_cost: string}>
     */
    public array $items = [];

    public function mount(?Purchase $purchase = null): void
    {
        $this->purchase = $purchase?->exists ? $purchase : null;

        if ($this->purchase) {
            $this->supplier_id = (string) ($this->purchase->supplier_id ?? '');
            $this->purchase_date = $this->purchase->purchase_date->toDateString();
            $this->notes = (string) ($this->purchase->notes ?? '');
            $this->items = $this->purchase->items()
                ->get()
                ->map(fn ($item): array => [
                    'ingredient_id' => (string) $item->ingredient_id,
                    'qty' => (string) $item->qty,
                    'unit_cost' => (string) $item->unit_cost,
                ])
                ->all();

            if ($this->items === []) {
                $this->items = [$this->emptyItem()];
            }

            return;
        }

        $this->purchase_date = now()->toDateString();
        $this->items = [$this->emptyItem()];
    }

    public function addRow(): void
    {
        $this->items[] = $this->emptyItem();
    }

    public function removeRow(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if ($this->items === []) {
            $this->items = [$this->emptyItem()];
        }
    }

    public function save()
    {
        if ($this->purchase && $this->purchase->isPosted()) {
            return;
        }

        $this->persist();

        session()->flash('success', 'Draft pembelian berhasil disimpan.');

        return $this->redirectRoute('purchases.index', navigate: true);
    }

    public function post(PurchaseService $service)
    {
        if ($this->purchase && $this->purchase->isPosted()) {
            return;
        }

        $purchase = $this->persist();
        $service->post($purchase);

        session()->flash('success', 'Pembelian diposting. Stok bahan telah ditambahkan.');

        return $this->redirectRoute('purchases.index', navigate: true);
    }

    private function persist(): Purchase
    {
        $validated = $this->validate($this->rules());

        return DB::transaction(function () use ($validated) {
            $total = 0.0;

            if ($this->purchase) {
                $purchase = $this->purchase;
                $purchase->update([
                    'supplier_id' => $validated['supplier_id'] ?: null,
                    'purchase_date' => $validated['purchase_date'],
                    'notes' => $validated['notes'] ?: null,
                ]);
                $purchase->items()->delete();
            } else {
                $purchase = Purchase::query()->create([
                    'code' => $this->generateCode(Carbon::parse($validated['purchase_date'])),
                    'supplier_id' => $validated['supplier_id'] ?: null,
                    'purchase_date' => $validated['purchase_date'],
                    'status' => 'draft',
                    'notes' => $validated['notes'] ?: null,
                    'user_id' => auth()->id(),
                ]);
            }

            foreach ($validated['items'] as $row) {
                $qty = (float) $row['qty'];
                $unitCost = (float) $row['unit_cost'];
                $subtotal = $qty * $unitCost;
                $total += $subtotal;

                $purchase->items()->create([
                    'ingredient_id' => $row['ingredient_id'],
                    'qty' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);
            }

            $purchase->update(['total' => $total]);

            return $purchase;
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'exists:ingredients,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array{ingredient_id: string, qty: string, unit_cost: string}
     */
    private function emptyItem(): array
    {
        return ['ingredient_id' => '', 'qty' => '', 'unit_cost' => ''];
    }

    private function generateCode(Carbon $date): string
    {
        $prefix = 'PB-'.$date->format('Ymd');

        $count = Purchase::query()->where('code', 'like', $prefix.'%')->count();

        return $prefix.'-'.str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }

    public function render(): View
    {
        return view('livewire.admin.purchases.form', [
            'ingredients' => Ingredient::query()->where('is_active', true)->orderBy('name')->get(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
