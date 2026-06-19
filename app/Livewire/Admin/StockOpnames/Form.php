<?php

namespace App\Livewire\Admin\StockOpnames;

use App\Models\Ingredient;
use App\Services\Admin\InventoryService;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    public string $ingredient_id = '';

    public string $type = 'in';

    public string $qty = '';

    public string $notes = '';

    public function save(InventoryService $inventoryService)
    {
        $this->validate([
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'type' => ['required', 'in:in,adjustment'],
            'qty' => ['required', 'numeric', 'min:0.001'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $ingredient = Ingredient::query()->findOrFail($this->ingredient_id);

        if ($this->type === 'in') {
            $inventoryService->addStock($ingredient, (float) $this->qty, $this->notes);
        } else {
            $inventoryService->adjustStock($ingredient, (float) $this->qty, $this->notes);
        }

        session()->flash('success', 'Stok berhasil diperbarui.');

        return $this->redirectRoute('stock-opnames.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.stock-opnames.form', [
            'ingredients' => Ingredient::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
