<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Customers\Form as CustomerForm;
use App\Livewire\Admin\Customers\Table as CustomerTable;
use App\Livewire\Admin\Suppliers\Form as SupplierForm;
use App\Livewire\Admin\Suppliers\Table as SupplierTable;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactsTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_create_edit_delete(): void
    {
        Livewire::test(SupplierForm::class)
            ->set('name', 'CV Sumber Pangan')
            ->set('code', 'SUP-001')
            ->set('phone', '0811')
            ->call('save');

        $this->assertDatabaseHas('suppliers', ['name' => 'CV Sumber Pangan', 'code' => 'SUP-001']);
        $supplier = Supplier::firstOrFail();

        Livewire::test(SupplierForm::class, ['supplier' => $supplier])
            ->set('name', 'CV Sumber Pangan Jaya')
            ->call('save');

        $this->assertSame('CV Sumber Pangan Jaya', $supplier->fresh()->name);
        $this->assertSame(1, Supplier::count());

        Livewire::test(SupplierTable::class)->call('delete', $supplier->id);
        $this->assertSame(0, Supplier::count());
    }

    public function test_supplier_code_must_be_unique(): void
    {
        Supplier::create(['name' => 'A', 'code' => 'SUP-001']);

        Livewire::test(SupplierForm::class)
            ->set('name', 'B')
            ->set('code', 'SUP-001')
            ->call('save')
            ->assertHasErrors(['code']);
    }

    public function test_customer_create_edit_delete(): void
    {
        Livewire::test(CustomerForm::class)
            ->set('name', 'Budi')
            ->set('phone', '0822')
            ->call('save');

        $this->assertDatabaseHas('customers', ['name' => 'Budi']);
        $customer = Customer::firstOrFail();

        Livewire::test(CustomerForm::class, ['customer' => $customer])
            ->set('name', 'Budi Santoso')
            ->call('save');

        $this->assertSame('Budi Santoso', $customer->fresh()->name);
        $this->assertSame(1, Customer::count());

        Livewire::test(CustomerTable::class)->call('delete', $customer->id);
        $this->assertSame(0, Customer::count());
    }
}
