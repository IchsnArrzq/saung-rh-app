<?php

use App\Support\SidebarNavigation;
use Livewire\Volt\Component;

new class extends Component {
    public string $dashboardUrl = '#';
    public string $navigationMenuPreference = 'sidebar';

    /** @var array<int, array<string, mixed>> */
    public array $groups = [];

    public function mount(SidebarNavigation $navigation): void
    {
        $this->dashboardUrl = Route::has('dashboard') ? route('dashboard') : '#';
        $this->groups = $navigation->forCurrentUser();

        $preference = (string) (auth()->user()?->navigation_menu_preference ?? 'sidebar');
        $this->navigationMenuPreference = in_array($preference, ['sidebar', 'navbar'], true) ? $preference : 'sidebar';
    }
}; ?>
@if ($navigationMenuPreference === 'sidebar')
    <div class="drawer-side h-[calc(100vh-0.1rem)]">
        <label for="admin-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
        <aside
            class="flex min-h-full max-w-[85vw] flex-col overflow-y-auto overflow-x-auto  bg-base-200 py-5 is-drawer-close:w-16 is-drawer-open:w-72">
            <nav class="grow py-16">
                <ul class="menu w-full gap-1 rounded-2xl p-2">
                    @foreach ($groups as $group)
                        <li>
                            <details @if ($group['is_open']) open @endif>
                                <summary data-tip="{{ $group['label'] }}"
                                    class="is-drawer-close:tooltip {{ $group['is_open'] ? 'bg-base-300 text-primary font-semibold' : 'text-stone-700 hover:bg-base-300' }}">
                                    <i class="{{ $group['icon'] }} text-lg"></i>
                                    <span class="is-drawer-close:hidden">{{ $group['label'] }}</span>
                                </summary>
                                <ul class="ms-2  is-drawer-close:hidden">
                                    @foreach ($group['items'] as $item)
                                        <li>
                                            <a href="{{ $item['url'] }}"
                                                class="{{ $item['is_active'] ? 'text-primary font-semibold' : 'text-stone-700' }}">
                                                <i class="{{ $item['icon'] }}"></i>
                                                {{ $item['label'] }}
                                                @if (!empty($item['badge_value']))
                                                    <span
                                                        class="badge badge-sm badge-primary">{{ $item['badge_value'] }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </aside>
    </div>
@endif
