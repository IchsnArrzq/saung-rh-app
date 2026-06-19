<?php

namespace App\Livewire\Admin\Ingredients;

use App\Models\Ingredient;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    public ?Ingredient $ingredient = null;

    public string $name = '';

    public string $unit = '';

    public string $stock = '0';

    public string $min_stock = '0';

    public string $cost_per_unit = '';

    public bool $is_active = true;

    public function mount(?Ingredient $ingredient = null): void
    {
        $this->ingredient = $ingredient;

        if ($this->ingredient) {
            $this->name = (string) $this->ingredient->name;
            $this->unit = (string) $this->ingredient->unit;
            $this->stock = (string) $this->ingredient->stock;
            $this->min_stock = (string) $this->ingredient->min_stock;
            $this->cost_per_unit = $this->ingredient->cost_per_unit ? (string) $this->ingredient->cost_per_unit : '';
            $this->is_active = (bool) $this->ingredient->is_active;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'unit' => ['required', 'string', 'max:30'],
            'stock' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'cost_per_unit' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['cost_per_unit'] = $validated['cost_per_unit'] ?: null;

        if ($this->ingredient) {
            $this->ingredient->update($validated);
            session()->flash('success', 'Bahan berhasil diperbarui.');
        } else {
            Ingredient::query()->create($validated);
            session()->flash('success', 'Bahan berhasil ditambahkan.');
        }

        return $this->redirectRoute('ingredients.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.ingredients.form');
    }
}
