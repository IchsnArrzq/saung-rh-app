<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Purchases\Form;
use App\Livewire\Admin\Stock\Overview;
use App\Livewire\Admin\Suppliers\Table;
use App\Models\Customer;
use App\Models\Ingredient;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Smoke test: every inventory/contacts list component renders without error.
 */
class InventoryRenderSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_components_render(): void
    {
        Ingredient::create(['name' => 'Gula', 'unit' => 'kg', 'stock' => 3, 'min_stock' => 5, 'is_active' => true]);
        Supplier::create(['name' => 'CV Pangan', 'code' => 'S1']);
        Customer::create(['name' => 'Budi', 'code' => 'C1']);

        $components = [
            Table::class,
            \App\Livewire\Admin\Customers\Table::class,
            \App\Livewire\Admin\StockOpnames\Table::class,
            \App\Livewire\Admin\StockMovements\Table::class,
            \App\Livewire\Admin\Purchases\Table::class,
            \App\Livewire\Admin\Sales\Table::class,
            Overview::class,
        ];

        foreach ($components as $component) {
            Livewire::test($component)->assertOk();
        }
    }

    public function test_document_create_forms_render(): void
    {
        Livewire::test(Form::class)->assertOk();
        Livewire::test(\App\Livewire\Admin\Sales\Form::class)->assertOk();
        Livewire::test(\App\Livewire\Admin\StockOpnames\Form::class)->assertOk();
    }
}
