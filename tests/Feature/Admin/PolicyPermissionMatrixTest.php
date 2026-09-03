<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Support\PolicyPermissions;
use Database\Seeders\PolicyPermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Matriks default role → permission adalah keputusan produk, bukan detail
 * teknis: kalau meleset, seseorang diam-diam kehilangan akses atau mendapat
 * akses yang tidak seharusnya. Jadi bentuknya dikunci di sini.
 */
class PolicyPermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PolicyPermissionSeeder::class);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function role(string $name): Role
    {
        return Role::query()->where('name', $name)->firstOrFail();
    }

    public function test_semua_permission_policy_dibuat(): void
    {
        $this->assertSame(
            count(PolicyPermissions::names()),
            Permission::query()->whereIn('name', PolicyPermissions::names())->count()
        );
    }

    public function test_superadmin_memegang_seluruh_permission(): void
    {
        $this->assertTrue(
            $this->role('superadmin')->hasAllPermissions(PolicyPermissions::names())
        );
    }

    public function test_kasir_boleh_mengurus_order_tapi_tidak_mengubah_menu(): void
    {
        $cashier = $this->role('cashier');

        $this->assertTrue($cashier->hasPermissionTo('order.update'));
        $this->assertTrue($cashier->hasPermissionTo('payment.create'));

        $this->assertTrue($cashier->hasPermissionTo('menu.viewAny'));
        $this->assertFalse($cashier->hasPermissionTo('menu.update'));
        $this->assertFalse($cashier->hasPermissionTo('menu.delete'));
    }

    public function test_chef_hanya_menggerakkan_order_bukan_membuat_atau_menghapus(): void
    {
        $chef = $this->role('chef');

        $this->assertTrue($chef->hasPermissionTo('order.update'));
        $this->assertFalse($chef->hasPermissionTo('order.create'));
        $this->assertFalse($chef->hasPermissionTo('order.delete'));
    }

    public function test_tidak_ada_role_selain_superadmin_yang_bisa_menghapus_permanen(): void
    {
        $destructive = ['restore', 'forceDelete'];

        foreach (Role::query()->where('name', '!=', 'superadmin')->get() as $role) {
            foreach ($role->permissions->pluck('name') as $name) {
                $ability = substr($name, strrpos($name, '.') + 1);

                $this->assertNotContains(
                    $ability,
                    $destructive,
                    "Role {$role->name} tidak boleh memegang {$name}."
                );
            }
        }
    }

    public function test_permission_fitur_lama_tidak_ikut_terhapus(): void
    {
        // PolicyPermissionSeeder bersifat menambah, bukan menyinkron. Kalau
        // suatu saat diganti syncPermissions, test ini yang jatuh lebih dulu.
        $admin = $this->role('admin');
        $admin->givePermissionTo(
            Permission::query()->firstOrCreate(['name' => 'legacy.feature', 'guard_name' => 'web'])
        );

        $this->seed(PolicyPermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertTrue($this->role('admin')->hasPermissionTo('legacy.feature'));
    }
}
