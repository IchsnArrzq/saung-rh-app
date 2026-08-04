<?php

namespace App\Livewire\Admin\Sales;

use App\Models\Customer;
use App\Models\Ingredient;
use App\Models\Sale;
use App\Services\Admin\SaleService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    public ?Sale $sale = null;

    public string $customer_id = '';

    public string $sale_date = '';

    public string $notes = '';

    /**
     * @var array<int, array{ingredient_id: string, qty: string, unit_price: string}>
     */
    public array $items = [];

    public function mount(?Sale $sale = null): void
    {
        $this->sale = $sale?->exists ? $sale : null;

        if ($this->sale) {
            $this->customer_id = (string) ($this->sale->customer_id ?? '');
            $this->sale_date = $this->sale->sale_date->toDateString();
            $this->notes = (string) ($this->sale->notes ?? '');
            $this->items = $this->sale->items()
                ->get()
                ->map(fn ($item): array => [
                    'ingredient_id' => (string) $item->ingredient_id,
                    'qty' => (string) $item->qty,
                    'unit_price' => (string) $item->unit_price,
                ])
                ->all();

            if ($this->items === []) {
                $this->items = [$this->emptyItem()];
            }

            return;
        }

        $this->sale_date = now()->toDateString();
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
        if ($this->sale && $this->sale->isPosted()) {
            return;
        }

        $this->persist();

        session()->flash('success', 'Draft penjualan berhasil disimpan.');

        return $this->redirectRoute('sales.index', navigate: true);
    }

    public function post(SaleService $service)
    {
        if ($this->sale && $this->sale->isPosted()) {
            return;
        }

        $sale = $this->persist();
        $service->post($sale);

        session()->flash('success', 'Penjualan diposting. Stok bahan telah dikurangi.');

        return $this->redirectRoute('sales.index', navigate: true);
    }

    private function persist(): Sale
    {
        $validated = $this->validate($this->rules());

        return DB::transaction(function () use ($validated) {
            $total = 0.0;

            if ($this->sale) {
                $sale = $this->sale;
                $sale->update([
                    'customer_id' => $validated['customer_id'] ?: null,
                    'sale_date' => $validated['sale_date'],
                    'notes' => $validated['notes'] ?: null,
                ]);
                $sale->items()->delete();
            } else {
                $sale = Sale::query()->create([
                    'code' => $this->generateCode(Carbon::parse($validated['sale_date'])),
                    'customer_id' => $validated['customer_id'] ?: null,
                    'sale_date' => $validated['sale_date'],
                    'status' => 'draft',
                    'notes' => $validated['notes'] ?: null,
                    'user_id' => auth()->id(),
                ]);
            }

            foreach ($validated['items'] as $row) {
                $qty = (float) $row['qty'];
                $unitPrice = (float) $row['unit_price'];
                $subtotal = $qty * $unitPrice;
                $total += $subtotal;

                $sale->items()->create([
                    'ingredient_id' => $row['ingredient_id'],
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }

            $sale->update(['total' => $total]);

            return $sale;
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'sale_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'exists:ingredients,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array{ingredient_id: string, qty: string, unit_price: string}
     */
    private function emptyItem(): array
    {
        return ['ingredient_id' => '', 'qty' => '', 'unit_price' => ''];
    }

    private function generateCode(Carbon $date): string
    {
        $prefix = 'SL-'.$date->format('Ymd');

        $count = Sale::query()->where('code', 'like', $prefix.'%')->count();

        return $prefix.'-'.str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }

    public function render(): View
    {
        return view('livewire.admin.sales.form', [
            'ingredients' => Ingredient::query()->where('is_active', true)->orderBy('name')->get(),
            'customers' => Customer::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
