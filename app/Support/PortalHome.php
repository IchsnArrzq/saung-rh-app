<?php

namespace App\Support;

use App\Models\User;

/**
 * Halaman pertama seseorang setelah masuk — dipakai redirect login dan tombol
 * "Dashboard" di situs publik.
 *
 * Peran bawaan tetap mendarat di tempat yang sama seperti dulu (urutan
 * BY_ROLE = urutan `match` di halaman login yang lama). Peran buatan layar Peran
 * & hak akses dulu jatuh ke dashboard pelanggan dan langsung 403; sekarang ia
 * mendarat di halaman pertama dari CANDIDATES yang memang boleh dibukanya.
 */
class PortalHome
{
    private const BY_ROLE = [
        'cashier' => 'pos.order.index',
        'manager' => 'manager.dashboard',
        'receptionist' => 'receptionist.dashboard',
        // Pelayan membawa tablet/ponsel ke lantai: yang pertama dilihatnya adalah
        // kartu meja realtime, bukan beranda berisi tautan.
        'waiter' => 'floor.index',
        'chef' => 'kds.index',
        'ob' => 'ob.dashboard',
        'superadmin' => 'dashboard',
        'admin' => 'dashboard',
        'customer' => 'customer.dashboard',
    ];

    private const CANDIDATES = [
        'dashboard',
        'floor.index',
        'pos.order.index',
        'kds.index',
        'manager.dashboard',
        'receptionist.dashboard',
        'waiter.dashboard',
        'ob.dashboard',
        'songs.queue',
    ];

    public static function routeName(User $user): string
    {
        foreach (self::BY_ROLE as $role => $routeName) {
            if ($user->hasRole($role)) {
                return $routeName;
            }
        }

        foreach (self::CANDIDATES as $routeName) {
            if (RouteAccess::allows($routeName, $user)) {
                return $routeName;
            }
        }

        // Peran tanpa satu halaman pun: profil selalu bisa dibuka, dan dari sana
        // jelas bahwa aksesnya belum diatur.
        return 'profile';
    }

    public static function url(User $user): string
    {
        return route(self::routeName($user), absolute: false);
    }
}
