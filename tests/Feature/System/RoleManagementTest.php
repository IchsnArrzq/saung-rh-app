<?php

namespace Tests\Feature\System;

use App\Domains\System\UseCases\DeleteRoleUseCase;
use App\Domains\System\UseCases\SaveRoleUseCase;
use App\Livewire\Admin\Roles\Form as RoleForm;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\PortalHome;
use App\Support\RouteAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithAuthorization;
use Tests\TestCase;

/**
 * Layar Peran & hak akses: peran bisa ditambah, diubah, dan dihapus — kecuali
 * Superadmin yang terkunci, dan nama peran bawaan yang dipakai kode.
 */
class RoleManagementTest extends TestCase
{
    use InteractsWithAuthorization, RefreshDatabase;

    private function permission(string $name): void
    {
        Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    public function test_peran_superadmin_terkunci(): void
    {
        $actor = $this->actingAsSuperadmin();
        $superadmin = Role::query()->where('name', 'superadmin')->firstOrFail();

        $this->expectException(ValidationException::class);

        app(SaveRoleUseCase::class)->handle($superadmin, 'superadmin', [], $actor);
    }

    public function test_membuat_peran_baru_hanya_menyimpan_hak_akses_yang_dikenal(): void
    {
        $this->actingAsSuperadmin();
        $this->permission('menu.viewAny');
        $this->permission('special_request.viewAny');

        Livewire::test(RoleForm::class)
            ->set('name', 'Barista')
            ->set('permissions', ['menu.viewAny', 'special_request.viewAny', 'bukan.permission'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('settings.roles-permissions'));

        $role = Role::query()->where('name', 'Barista')->firstOrFail();

        $this->assertEqualsCanonicalizing(
            ['menu.viewAny', 'special_request.viewAny'],
            $role->permissions->pluck('name')->all(),
        );
    }

    public function test_nama_peran_bawaan_tidak_bisa_diganti(): void
    {
        $actor = $this->actingAsSuperadmin();
        $waiter = Role::query()->firstOrCreate(['name' => 'waiter', 'guard_name' => 'web']);

        app(SaveRoleUseCase::class)->handle($waiter, 'Pelayan Senior', [], $actor);

        $this->assertSame('waiter', $waiter->fresh()->name, 'Rute dan redirect login memakai nama ini.');
    }

    public function test_bukan_superadmin_tidak_bisa_memberi_hak_akses_yang_tidak_dimilikinya(): void
    {
        $actor = $this->actingAsRole('admin', ['role.update', 'menu.viewAny']);
        $this->permission('user.delete');
        $barista = Role::query()->create(['name' => 'Barista', 'guard_name' => 'web']);

        try {
            app(SaveRoleUseCase::class)->handle($barista, 'Barista', ['menu.viewAny', 'user.delete'], $actor);
            $this->fail('Memberi user.delete tanpa memilikinya harus ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('permissions', $e->errors());
        }

        app(SaveRoleUseCase::class)->handle($barista, 'Barista', ['menu.viewAny'], $actor);

        $this->assertTrue($barista->fresh()->hasPermissionTo('menu.viewAny'));
        $this->assertFalse($barista->fresh()->hasPermissionTo('user.delete'));
    }

    public function test_peran_yang_masih_dipakai_tidak_bisa_dihapus(): void
    {
        $actor = $this->actingAsSuperadmin();
        $barista = Role::query()->create(['name' => 'Barista', 'guard_name' => 'web']);
        User::factory()->create()->assignRole($barista);

        $this->expectException(ValidationException::class);

        app(DeleteRoleUseCase::class)->handle($barista, $actor);
    }

    public function test_peran_buatan_sendiri_hanya_melihat_dan_mendarat_di_halaman_yang_boleh_dibukanya(): void
    {
        $user = $this->actingAsRole('Kapten', ['special_request.viewAny']);

        $this->assertSame('floor.index', PortalHome::routeName($user));
        $this->assertTrue(RouteAccess::allows('floor.index', $user));
        $this->assertFalse(RouteAccess::allows('menus.index', $user), 'Tanpa backoffice.access dan menu.viewAny.');
    }
}
