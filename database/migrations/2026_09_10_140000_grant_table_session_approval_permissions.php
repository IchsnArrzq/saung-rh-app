<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\PermissionRegistrar;

/**
 * Siapa yang boleh menyetujui, menolak, dan menonaktifkan sesi QR meja, untuk
 * database yang sudah berjalan. Instalasi baru mendapat hal yang sama dari
 * PolicyPermissionSeeder (dijalankan setelah migrasi — di sana role belum ada,
 * jadi pemberian di bawah dilewati).
 *
 * `table_session.update` = setujui / tolak / nonaktifkan. Kasir dan resepsionis
 * yang berjaga di depan; admin mengawasi riwayatnya.
 */
return new class extends Migration
{
    private const ROLES = ['admin', 'cashier', 'receptionist'];

    private const PERMISSIONS = ['table_session.viewAny', 'table_session.view', 'table_session.update'];

    public function up(): void
    {
        foreach (self::PERMISSIONS as $name) {
            Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        foreach (self::ROLES as $roleName) {
            $this->role($roleName)?->givePermissionTo(self::PERMISSIONS);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Admin dan resepsionis sudah bisa melihat sesi sebelum migrasi ini;
        // yang baru bagi mereka hanya `update`. Kasir sebelumnya tidak punya apa-apa.
        foreach (self::ROLES as $roleName) {
            $this->role($roleName)?->revokePermissionTo('table_session.update');
        }

        $this->role('cashier')?->revokePermissionTo(['table_session.viewAny', 'table_session.view']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function role(string $name): ?Role
    {
        return Role::query()->where('name', $name)->where('guard_name', 'web')->first();
    }
};
