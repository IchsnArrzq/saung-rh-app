<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\Route;

class SidebarNavigation
{
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

        $priority = ['superadmin', 'admin', 'cashier', 'customer'];

        foreach ($priority as $role) {
            if ($user->hasRole($role)) {
                return $role;
            }
        }

        return 'admin';
    }

    private function resolveGroup(array $group): array
    {
        $items = collect($group['items'] ?? [])
            ->map(fn (array $item) => $this->resolveItem($item))
            ->filter(fn (array $item) => filled($item['url']))
            ->values()
            ->all();

        $group['items'] = $items;
        $group['is_open'] = (bool) ($group['open'] ?? false) || collect($items)->contains(fn (array $item) => $item['is_active']);

        return $group;
    }

    private function resolveItem(array $item): array
    {
        $routeName = $item['route'] ?? null;
        $patterns = collect($item['active'] ?? [$routeName])
            ->filter(fn ($pattern) => is_string($pattern) && $pattern !== '')
            ->values();

        $item['url'] = $routeName && Route::has($routeName) ? route($routeName) : null;
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
            ->whereIn('status', ['draft', 'confirmed', 'preparing', 'ready', 'served'])
            ->count();

        return $count > 0 ? (string) $count : null;
    }
}
