<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Ingredients\Form as IngredientForm;
use App\Livewire\Admin\Menus\Form as MenuForm;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Guards against a Livewire regression where a type-hinted, nullable model
 * mount parameter (e.g. `mount(?Ingredient $ingredient = null)`) is resolved
 * to an *empty* (non-persisted) model instead of null on the create route,
 * causing save() to run update() on a phantom record and silently insert nothing.
 */
class FormCreateEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_ingredient_can_be_created(): void
    {
        Livewire::test(IngredientForm::class)
            ->set('name', 'Bawang Merah')
            ->set('unit', 'gram')
            ->set('stock', '1000')
            ->set('min_stock', '100')
            ->call('save');

        $this->assertDatabaseHas('ingredients', ['name' => 'Bawang Merah']);
    }

    public function test_ingredient_can_be_edited_without_duplicating(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Gula', 'unit' => 'gram', 'stock' => 500, 'min_stock' => 50, 'is_active' => true,
        ]);

        Livewire::test(IngredientForm::class, ['ingredient' => $ingredient])
            ->set('name', 'Gula Pasir')
            ->call('save');

        $this->assertSame(1, Ingredient::count());
        $this->assertSame('Gula Pasir', $ingredient->fresh()->name);
    }

    public function test_menu_can_be_created(): void
    {
        Livewire::test(MenuForm::class)
            ->set('name', 'Nasi Goreng')
            ->set('price', '25000')
            ->call('save');

        $this->assertDatabaseHas('menus', ['name' => 'Nasi Goreng']);
    }
}
