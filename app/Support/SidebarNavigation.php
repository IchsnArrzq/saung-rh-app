<?php

namespace App\Support;

use App\Domains\Order\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

class SidebarNavigation
{
    /**
     * Peran bawaan punya susunan navigasinya sendiri di config/navigation.php,
     * dicoba berurutan. Peran buatan layar Peran & hak akses tidak punya — ia
     * memakai susunan terlengkap (superadmin), yang lalu disaring per akses.
     */
    private const SECTIONS = ['superadmin', 'admin', 'manager', 'receptionist', 'cashier', 'waiter', 'chef', 'ob', 'customer'];

    public function forCurrentUser(): array
    {
        return $this->for($this->resolveSection());
    }

    public function for(string $section): array
    {
        $groups = config("navigation.{$section}", []);

        return collect($groups)
            ->map(fn (array $group) => $this->resolveGroup($group))
            ->filter(fn (array $group) => count($group['items']) > 0)
            ->values()
            ->all();
    }

    private function resolveSection(): string
    {
        $user = auth()->user();

        if (! $user) {
            return 'admin';
        }

        foreach (self::SECTIONS as $role) {
            if ($user->hasRole($role)) {
                return $role;
            }
        }

        return 'superadmin';
    }

    private function resolveGroup(array $group): array
    {
        $items = collect($group['items'] ?? [])
            ->map(fn (array $item) => $this->resolveItem($item))
            ->filter(fn (array $item) => filled($item['url']))
            ->values()
            ->all();

        $group['items'] = $items;

        // Two distinct questions that used to share one flag: `is_active` means
        // "the current page lives in this group" and drives the highlight;
        // `is_open` means "render the accordion expanded" and additionally
        // covers groups pinned open by config. Conflating them made a pinned
        // group look selected on every page.
        $group['is_active'] = collect($items)->contains(fn (array $item) => $item['is_active']);
        $group['is_open'] = (bool) ($group['open'] ?? false) || $group['is_active'];

        return $group;
    }

    private function resolveItem(array $item): array
    {
        $routeName = $item['route'] ?? null;
        $patterns = collect($item['active'] ?? [$routeName])
            ->filter(fn ($pattern) => is_string($pattern) && $pattern !== '')
            ->values();

        // Tautan yang akan berakhir di 403 tidak dirender: RouteAccess membaca
        // middleware rutenya, jadi navigasi dan gerbang halaman tidak bisa
        // berbeda pendapat.
        $item['url'] = $routeName && Route::has($routeName) && RouteAccess::allows($routeName)
            ? route($routeName)
            : null;
        $item['is_active'] = $patterns->contains(fn ($pattern) => request()->routeIs($pattern));
        $item['badge_value'] = $this->resolveBadgeValue($item['badge'] ?? null);

        return $item;
    }

    private function resolveBadgeValue(?array $badge): ?string
    {
        if (! is_array($badge)) {
            return null;
        }

        if (($badge['type'] ?? null) === 'text') {
            return (string) ($badge['value'] ?? '');
        }

        if (($badge['type'] ?? null) !== 'dynamic') {
            return null;
        }

        return match ($badge['resolver'] ?? null) {
            'active_orders' => $this->activeOrdersBadge(),
            default => null,
        };
    }

    private function activeOrdersBadge(): ?string
    {
        $count = Order::query()
            ->whereIn('status', [OrderStatus::Draft->value, ...OrderStatus::inServiceValues()])
            ->count();

        return $count > 0 ? (string) $count : null;
    }
}
