<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Menus\Form as MenuForm;
use App\Livewire\Admin\Menus\Table as MenuTable;
use App\Models\Menu;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithAuthorization;
use Tests\TestCase;

/**
 * Menu adalah modul referensi untuk otorisasi berbasis policy: route, komponen
 * Livewire dan tombol Blade menanyakan MenuPolicy yang sama.
 *
 * Test ini menjaga tiga hal yang gampang lolos dari mata:
 *  1. tombol yang tidak boleh ditekan memang tidak dirender;
 *  2. aksi Livewire tetap menolak walau tombolnya dilewati (dipanggil langsung);
 *  3. superadmin lolos tanpa memegang permission apa pun (Gate::before).
 */
class MenuAuthorizationTest extends TestCase
{
    use InteractsWithAuthorization, RefreshDatabase;

    private function menu(): Menu
    {
        return Menu::create(['name' => 'Sate Ayam', 'slug' => 'sate-ayam', 'price' => 20000]);
    }

    public function test_pembaca_melihat_daftar_tanpa_tombol_tulis(): void
    {
        $menu = $this->menu();
        $this->actingAsRole('pembaca', ['menu.viewAny', 'menu.view']);

        Livewire::test(MenuTable::class)
            ->assertOk()
            ->assertSee($menu->name)
            ->assertDontSee('Tambah Menu')
            ->assertDontSee('Hapus');
    }

    public function test_pengelola_melihat_tombol_tulis(): void
    {
        $this->menu();
        $this->actingAsRole('pengelola', ['menu.viewAny', 'menu.view', 'menu.create', 'menu.update', 'menu.delete']);

        Livewire::test(MenuTable::class)
            ->assertSee('Tambah Menu')
            ->assertSee('Hapus');
    }

    public function test_hapus_ditolak_walau_aksinya_dipanggil_langsung(): void
    {
        $menu = $this->menu();
        $this->actingAsRole('pembaca', ['menu.viewAny', 'menu.view']);

        // Menyembunyikan tombol bukan pengamanan: aksi Livewire adalah endpoint
        // HTTP yang bisa dipanggil tanpa pernah merender tombolnya.
        Livewire::test(MenuTable::class)
            ->call('delete', $menu->id)
            ->assertForbidden();

        $this->assertModelExists($menu);
    }

    public function test_tanpa_permission_daftar_menu_tertutup(): void
    {
        $this->actingAsRole('tamu');

        // Livewire menangkap AuthorizationException dari mount() dan
        // menerjemahkannya jadi respons 403, bukan melemparkannya keluar.
        Livewire::test(MenuTable::class)->assertForbidden();
    }

    public function test_form_edit_ditolak_untuk_yang_hanya_boleh_membuat(): void
    {
        $menu = $this->menu();
        $this->actingAsRole('pencatat', ['menu.viewAny', 'menu.view', 'menu.create']);

        Livewire::test(MenuForm::class)->assertOk();

        Livewire::test(MenuForm::class, ['menu' => $menu])->assertForbidden();
    }

    /**
     * Nama role-nya harus 'cashier' — bukan nama karangan.
     *
     * Grup di routes/admin.php masih dijaga `role:superadmin|admin|cashier`.
     * Role di luar daftar itu ditolak sebelum `can:` sempat dijalankan, jadi
     * test-nya akan hijau tanpa membuktikan apa pun tentang policy. Dengan role
     * yang lolos gerbang kasar itu tapi tanpa permission, yang menolak sudah
     * pasti `can:`.
     */
    public function test_route_daftar_menu_menolak_tanpa_permission(): void
    {
        $this->actingAsRole('cashier');

        $this->get(route('menus.index'))->assertForbidden();

        $this->actingAsRole('cashier', ['menu.viewAny', 'menu.view']);

        $this->get(route('menus.index'))->assertOk();
    }

    public function test_superadmin_lolos_tanpa_memegang_permission(): void
    {
        $menu = $this->menu();
        $user = $this->actingAsSuperadmin();

        $this->assertSame(0, $user->getAllPermissions()->count(), 'Superadmin sengaja tidak diberi permission di test ini.');

        Livewire::test(MenuTable::class)
            ->assertSee('Tambah Menu')
            ->call('delete', $menu->id)
            ->assertOk();

        $this->assertModelMissing($menu);
    }
}
