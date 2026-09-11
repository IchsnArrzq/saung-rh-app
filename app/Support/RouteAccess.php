<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

/**
 * "Boleh orang ini membuka rute ini?" — dijawab dari middleware rute itu sendiri
 * (`role:`, `permission:`, `role_or_permission:`, `can:`), jadi navigasi dan kartu
 * portal tidak pernah menampilkan tautan yang berakhir di 403, dan peran buatan
 * layar Peran & hak akses otomatis melihat halaman yang memang boleh dibukanya.
 *
 * Satu batas yang disengaja: `can:update,menu` menunjuk MODEL hasil binding
 * parameter rute. Tanpa modelnya ability itu tidak bisa dinilai, jadi dianggap
 * lolos — halaman itu sendiri tetap menolak. Tautan navigasi selalu menunjuk
 * halaman index/create yang memakai nama kelas, bukan parameter.
 */
class RouteAccess
{
    public static function allows(string $routeName, ?Authenticatable $user = null): bool
    {
        $route = Route::getRoutes()->getByName($routeName);

        if (! $route) {
            return false;
        }

        $user ??= auth()->user();

        foreach ($route->gatherMiddleware() as $middleware) {
            if (! is_string($middleware)) {
                continue;
            }

            [$name, $parameters] = array_pad(explode(':', $middleware, 2), 2, '');

            $allowed = match ($name) {
                'auth' => $user !== null,
                'role' => $user !== null && $user->hasAnyRole(self::names($parameters)),
                'permission' => $user !== null && self::holdsAny($user, self::names($parameters)),
                'role_or_permission' => $user !== null && (
                    $user->hasAnyRole(self::names($parameters)) || self::holdsAny($user, self::names($parameters))
                ),
                'can' => self::passesCan($user, $parameters),
                default => true,
            };

            if (! $allowed) {
                return false;
            }
        }

        return true;
    }

    /**
     * Spatie: "a|b|c", opsional diikuti ",guard".
     *
     * @return array<int, string>
     */
    private static function names(string $parameters): array
    {
        return array_values(array_filter(explode('|', explode(',', $parameters)[0])));
    }

    /**
     * @param  array<int, string>  $abilities
     */
    private static function holdsAny(Authenticatable $user, array $abilities): bool
    {
        foreach ($abilities as $ability) {
            if (Gate::forUser($user)->allows($ability)) {
                return true;
            }
        }

        return false;
    }

    private static function passesCan(?Authenticatable $user, string $parameters): bool
    {
        if ($user === null) {
            return false;
        }

        $parts = explode(',', $parameters);
        $ability = (string) array_shift($parts);
        $arguments = [];

        foreach ($parts as $part) {
            if (! class_exists($part)) {
                // Parameter rute (model hasil binding) — lihat catatan kelas.
                return true;
            }

            $arguments[] = $part;
        }

        return Gate::forUser($user)->allows($ability, $arguments);
    }
}
