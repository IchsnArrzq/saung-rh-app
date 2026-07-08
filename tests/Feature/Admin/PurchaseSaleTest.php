<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Purchases\Form as PurchaseForm;
use App\Livewire\Admin\Sales\Form as SaleForm;
use App\Models\Customer;
use App\Models\Ingredient;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_posting_purchase_adds_stock_and_updates_cost(): void
    {
        $supplier = Supplier::create(['name' => 'CV Pangan']);
        $ing = Ingredient::create(['name' => 'Tepung', 'unit' => 'kg', 'stock' => 10, 'min_stock' => 2, 'is_active' => true]);

        Livewire::test(PurchaseForm::class)
            ->set('supplier_id', $supplier->id)
            ->set('items', [['ingredient_id' => $ing->id, 'qty' => '5', 'unit_cost' => '12000']])
            ->call('post');

        $purchase = Purchase::firstOrFail();
        $this->assertSame('posted', $purchase->status);
        $this->assertEqualsWithDelta(60000, (float) $purchase->total, 0.01);

        $ing->refresh();
        $this->assertEqualsWithDelta(15, (float) $ing->stock, 0.001);          // 10 + 5
        $this->assertEqualsWithDelta(12000, (float) $ing->cost_per_unit, 0.01); // latest buy price

        $movement = StockMovement::where('ingredient_id', $ing->id)->latest()->first();
        $this->assertSame('in', $movement->type);
        $this->assertSame(Purchase::class, $movement->reference_type);
    }

    public function test_purchase_draft_does_not_touch_stock(): void
    {
        $ing = Ingredient::create(['name' => 'Tepung', 'unit' => 'kg', 'stock' => 10, 'min_stock' => 2, 'is_active' => true]);

        Livewire::test(PurchaseForm::class)
            ->set('items', [['ingredient_id' => $ing->id, 'qty' => '5', 'unit_cost' => '12000']])
            ->call('save');

        $this->assertSame('draft', Purchase::firstOrFail()->status);
        $this->assertEqualsWithDelta(10, (float) $ing->fresh()->stock, 0.001);
        $this->assertSame(0, StockMovement::count());
    }

    public function test_posting_sale_reduces_stock(): void
    {
        $customer = Customer::create(['name' => 'Budi']);
        $ing = Ingredient::create(['name' => 'Beras', 'unit' => 'kg', 'stock' => 20, 'min_stock' => 5, 'is_active' => true]);

        Livewire::test(SaleForm::class)
            ->set('customer_id', $customer->id)
            ->set('items', [['ingredient_id' => $ing->id, 'qty' => '8', 'unit_price' => '15000']])
            ->call('post');

        $sale = Sale::firstOrFail();
        $this->assertSame('posted', $sale->status);
        $this->assertEqualsWithDelta(120000, (float) $sale->total, 0.01);

        $ing->refresh();
        $this->assertEqualsWithDelta(12, (float) $ing->stock, 0.001); // 20 - 8

        $movement = StockMovement::where('ingredient_id', $ing->id)->latest()->first();
        $this->assertSame('out', $movement->type);
        $this->assertSame(Sale::class, $movement->reference_type);
    }

    public function test_sale_validation_requires_items(): void
    {
        Livewire::test(SaleForm::class)
            ->set('items', [['ingredient_id' => '', 'qty' => '', 'unit_price' => '']])
            ->call('save')
            ->assertHasErrors(['items.0.ingredient_id', 'items.0.qty']);

        $this->assertSame(0, Sale::count());
    }
}
