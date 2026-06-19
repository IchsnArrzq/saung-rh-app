<?php

namespace App\Livewire\Admin\MenuIngredients;

use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuIngredient;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    public Menu $menu;

    /** @var array<int, array{ingredient_id: string, qty: string}> */
    public array $rows = [];

    public function mount(Menu $menu): void
    {
        $this->menu = $menu;

        $this->rows = $menu->menuIngredients()
            ->with('ingredient')
            ->get()
            ->map(fn ($mi) => [
                'id' => $mi->id,
                'ingredient_id' => $mi->ingredient_id,
                'qty' => (string) $mi->qty,
            ])
            ->toArray();
    }

    public function addRow(): void
    {
        $this->rows[] = ['id' => null, 'ingredient_id' => '', 'qty' => ''];
    }

    public function removeRow(int $index): void
    {
        array_splice($this->rows, $index, 1);
    }

    public function save()
    {
        $this->validate([
            'rows.*.ingredient_id' => ['required', 'exists:ingredients,id'],
            'rows.*.qty' => ['required', 'numeric', 'min:0.001'],
        ], [], [
            'rows.*.ingredient_id' => 'bahan',
            'rows.*.qty' => 'jumlah',
        ]);

        // Hapus semua resep lama lalu simpan ulang
        $this->menu->menuIngredients()->delete();

        foreach ($this->rows as $row) {
            MenuIngredient::query()->create([
                'menu_id' => $this->menu->id,
                'ingredient_id' => $row['ingredient_id'],
                'qty' => (float) $row['qty'],
            ]);
        }

        session()->flash('success', 'Resep menu berhasil disimpan.');

        return $this->redirectRoute('menus.ingredients.edit', $this->menu, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.menu-ingredients.form', [
            'ingredients' => Ingredient::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
