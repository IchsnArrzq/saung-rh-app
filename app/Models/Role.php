<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasUuids;

    /**
     * Tak tersentuh: selalu memegang semua akses lewat Gate::before
     * (AuthServiceProvider), jadi tidak diatur, diganti namanya, atau dihapus.
     */
    public const LOCKED = 'superadmin';

    /**
     * Peran yang namanya dipakai kode — gerbang rute `role_or_permission:…`,
     * redirect login (PortalHome), dan navigasi. Hak aksesnya boleh diatur,
     * tapi namanya tidak boleh diganti dan perannya tidak boleh dihapus.
     */
    public const SYSTEM = ['superadmin', 'admin', 'manager', 'receptionist', 'cashier', 'waiter', 'chef', 'ob', 'customer'];

    /** Label Indonesia untuk peran bawaan; peran buatan sendiri tampil apa adanya. */
    private const LABELS = [
        'superadmin' => 'Superadmin',
        'admin' => 'Admin',
        'manager' => 'Manajer',
        'receptionist' => 'Resepsionis',
        'cashier' => 'Kasir',
        'waiter' => 'Pelayan',
        'chef' => 'Koki',
        'ob' => 'Kebersihan (OB)',
        'customer' => 'Pelanggan',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public static function labelFor(string $name): string
    {
        return self::LABELS[$name] ?? $name;
    }

    public function label(): string
    {
        return self::labelFor((string) $this->name);
    }

    public function isSystem(): bool
    {
        return in_array($this->name, self::SYSTEM, true);
    }

    public function isLocked(): bool
    {
        return $this->name === self::LOCKED;
    }
}
