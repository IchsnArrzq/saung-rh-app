<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\StockOpnames\Form;
use App\Models\Ingredient;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Services\Admin\StockOpnameServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_draft_snapshots_active_ingredients(): void
    {
        Ingredient::create(['name' => 'Garam', 'unit' => 'gram', 'stock' => 1000, 'min_stock' => 100, 'is_active' => true]);
        Ingredient::create(['name' => 'Gula', 'unit' => 'gram', 'stock' => 500, 'min_stock' => 50, 'is_active' => true]);
        Ingredient::create(['name' => 'Nonaktif', 'unit' => 'gram', 'stock' => 5, 'min_stock' => 1, 'is_active' => false]);

        $opname = app(StockOpnameServiceInterface::class)->createDraft(Carbon::parse('2026-07-05'));

        $this->assertSame('draft', $opname->status);
        $this->assertSame(2, $opname->items()->count()); // only active
        $this->assertEqualsWithDelta(1000, (float) $opname->items()->whereRelation('ingredient', 'name', 'Garam')->first()->system_qty, 0.001);
    }

    public function test_posting_adjusts_stock_and_records_movement(): void
    {
        $garam = Ingredient::create(['name' => 'Garam', 'unit' => 'gram', 'stock' => 1000, 'min_stock' => 100, 'is_active' => true]);

        $opname = app(StockOpnameServiceInterface::class)->createDraft(Carbon::parse('2026-07-05'));
        $item = $opname->items()->first();

        // Physically counted 950 (shortage of 50).
        Livewire::test(Form::class, ['opname' => $opname])
            ->set('rows.0.physical_qty', '950')
            ->set('rows.0.notes', 'susut')
            ->call('post');

        $opname->refresh();
        $this->assertSame('posted', $opname->status);
        $this->assertEqualsWithDelta(950, (float) $garam->fresh()->stock, 0.001);

        $this->assertEqualsWithDelta(-50, (float) $item->fresh()->difference, 0.001);

        $movement = StockMovement::where('ingredient_id', $garam->id)->latest()->first();
        $this->assertNotNull($movement);
        $this->assertSame('adjustment', $movement->type);
        $this->assertEqualsWithDelta(950, (float) $movement->qty_after, 0.001);
        $this->assertSame(StockOpname::class, $movement->reference_type);
        $this->assertSame($opname->id, $movement->reference_id);
    }

    public function test_posted_opname_cannot_be_reposted(): void
    {
        Ingredient::create(['name' => 'Garam', 'unit' => 'gram', 'stock' => 1000, 'min_stock' => 100, 'is_active' => true]);
        $opname = app(StockOpnameServiceInterface::class)->createDraft(Carbon::parse('2026-07-05'));

        Livewire::test(Form::class, ['opname' => $opname])
            ->set('rows.0.physical_qty', '900')
            ->call('post');

        $garam = Ingredient::first();
        $this->assertEqualsWithDelta(900, (float) $garam->fresh()->stock, 0.001);

        // Re-posting is a no-op (service guards against it); stock unchanged.
        app(StockOpnameServiceInterface::class)->post($opname->fresh());
        $this->assertEqualsWithDelta(900, (float) $garam->fresh()->stock, 0.001);
        $this->assertSame(1, StockMovement::where('ingredient_id', $garam->id)->count());
    }
}
