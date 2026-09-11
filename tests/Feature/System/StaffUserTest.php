<?php

namespace Tests\Feature\System;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\Concerns\InteractsWithAuthorization;
use Tests\TestCase;

/**
 * Layar Karyawan: semua peran staf bisa dipasang (termasuk peran buatan
 * sendiri), Superadmin tidak bisa diubah-ubah, dan akun nonaktif benar-benar
 * tidak bisa masuk.
 */
class StaffUserTest extends TestCase
{
    use InteractsWithAuthorization, RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function form(string $email, string $role): array
    {
        return [
            'name' => 'Karyawan Baru',
            'email' => $email,
            'password' => 'rahasia-panjang',
            'password_confirmation' => 'rahasia-panjang',
            'role' => $role,
            'is_active' => '1',
        ];
    }

    public function test_karyawan_bisa_diberi_peran_buatan_sendiri(): void
    {
        $this->actingAsRole('admin', ['user.viewAny', 'user.create']);
        Role::query()->create(['name' => 'Barista', 'guard_name' => 'web']);

        $this->post(route('admin-users.store'), $this->form('Barista@Example.com', 'Barista'))
            ->assertRedirect(route('admin-users.index'));

        $this->assertTrue(
            User::query()->where('email', 'barista@example.com')->firstOrFail()->hasRole('Barista'),
            'Email disimpan dalam huruf kecil dan perannya terpasang.',
        );
    }

    public function test_peran_superadmin_tidak_bisa_diberikan_lewat_form_karyawan(): void
    {
        $this->actingAsRole('admin', ['user.viewAny', 'user.create']);
        Role::query()->firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

        $this->post(route('admin-users.store'), $this->form('penyusup@example.com', 'superadmin'))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'penyusup@example.com']);
    }

    public function test_admin_tidak_bisa_mengubah_akun_superadmin(): void
    {
        $this->actingAsRole('admin', ['user.viewAny', 'user.update']);

        $owner = User::factory()->create(['name' => 'Pemilik']);
        $owner->assignRole(Role::query()->firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']));

        $this->put(route('admin-users.update', $owner), [
            'name' => 'Diambil alih',
            'email' => $owner->email,
            'is_active' => '1',
        ])->assertSessionHasErrors('user');

        $this->assertSame('Pemilik', $owner->fresh()->name);
    }

    public function test_akun_nonaktif_tidak_bisa_masuk(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasErrors('form.email');

        $this->assertGuest();
    }

    public function test_akun_yang_dinonaktifkan_langsung_keluar_di_request_berikutnya(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
