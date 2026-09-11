<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission yang dibutuhkan Panel meja dan gerbang rute berbasis permission,
 * untuk database yang sudah berjalan. Instalasi baru mendapat hal yang sama dari
 * PermissionSeeder dan PolicyPermissionSeeder (keduanya dijalankan setelah
 * migrasi — di sana role belum ada, jadi pemberian di bawah dilewati).
 *
 *  - `backoffice.access`   : gerbang kasar area /admin. Menggantikan
 *                            `role:superadmin|admin|cashier` dengan pemegang
 *                            yang persis sama; role baru bisa diberi dari layar
 *                            Peran & hak akses.
 *  - `table_chat.moderate` : membalas dan membersihkan obrolan meja.
 *  - Permintaan khusus & lagu: alur persetujuan manajer dihapus, jadi staf lantai
 *    (pelayan, resepsionis, kasir) dan admin boleh mengubah statusnya.
 *  - `special_request_category.*`: master kategori permintaan, dikelola admin.
 */
return new class extends Migration
{
    private const VIEW = ['viewAny', 'view'];

    private const MANAGE = ['viewAny', 'view', 'create', 'update', 'delete'];

    private const ALL = ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'];

    public function up(): void
    {
        $features = [
            'backoffice.access' => ['admin', 'cashier'],
            'table_chat.moderate' => ['admin', 'manager', 'receptionist', 'cashier', 'waiter'],
        ];

        $models = [
            'special_request' => ['manage' => ['admin', 'receptionist', 'cashier', 'waiter'], 'view' => ['manager']],
            'song_request' => ['manage' => ['admin', 'receptionist', 'cashier', 'waiter'], 'view' => ['manager']],
            'special_request_category' => ['manage' => ['admin']],
        ];

        $all = array_keys($features);

        foreach (array_keys($models) as $slug) {
            foreach (self::ALL as $ability) {
                $all[] = "{$slug}.{$ability}";
            }
        }

        foreach ($all as $name) {
            Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->grant('superadmin', $all);

        foreach ($features as $permission => $roles) {
            foreach ($roles as $role) {
                $this->grant($role, [$permission]);
            }
        }

        foreach ($models as $slug => $bundles) {
            foreach ($bundles as $bundle => $roles) {
                $abilities = $bundle === 'manage' ? self::MANAGE : self::VIEW;

                foreach ($roles as $role) {
                    $this->grant($role, array_map(fn (string $ability) => "{$slug}.{$ability}", $abilities));
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Pemberian special_request.* / song_request.* ke role yang sudah ada
        // sebelumnya tidak dicabut: keadaan awalnya tidak diketahui di sini.
        $names = ['backoffice.access', 'table_chat.moderate'];

        foreach (self::ALL as $ability) {
            $names[] = "special_request_category.{$ability}";
        }

        Permission::query()->whereIn('name', $names)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function grant(string $roleName, array $permissions): void
    {
        $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();

        $role?->givePermissionTo($permissions);
    }
};
