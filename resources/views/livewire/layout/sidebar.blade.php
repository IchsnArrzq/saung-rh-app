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
        $this->navigationMenuPreference = in_array($preference, ['sidebar', 'navbar'], true)
            ? $preference
            : 'sidebar';
    }
}; ?>
@if ($navigationMenuPreference === 'sidebar')
    <div class="drawer-side h-[calc(100vh-0.1rem)]">
        <label for="admin-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
        <aside
            class="flex min-h-full max-w-[85vw] flex-col overflow-y-auto overflow-x-auto border-r border-base-300 bg-base-200 py-5 transition-all duration-1000 is-drawer-close:w-16 is-drawer-open:w-72">
            <a href="{{ $dashboardUrl }}" class="inline-flex items-center gap-3 p-2">
                <img src="{{ asset('assets/logo-cr-mark.png') }}" alt="CR Cafe & Resto logo mark"
                    class="h-11 w-11 shrink-0 rounded-xl border border-base-300 bg-base-100 p-1 object-contain transition-all duration-300 is-drawer-open:h-14 is-drawer-open:w-14">
                <span
                    class="overflow-hidden transition-all duration-300 is-drawer-close:max-w-0 is-drawer-close:opacity-0 is-drawer-open:max-w-xs is-drawer-open:opacity-100">
                    <span class="block text-2xl font-semibold text-primary whitespace-nowrap"
                        style="font-family: 'Playfair Display', serif;">Admin Panel</span>
                </span>
            </a>

            <div class="divider"></div>

            <nav class="grow">
                <ul class="menu w-full gap-1 rounded-2xl p-2">
                    @foreach ($groups as $group)
                        @if (count($group['items']) === 1 && $group['label'] === 'Dashboard')
                            @php($item = $group['items'][0])
                            <li>
                                <a href="{{ $item['url'] }}" data-tip="{{ $item['label'] }}"
                                    class="{{ $item['is_active'] ? 'bg-primary text-primary-content hover:bg-neutral' : 'text-stone-700 hover:bg-base-300' }}">
                                    <i class="{{ $item['icon'] }} text-lg"></i>
                                    <span class="is-drawer-close:hidden">{{ $item['label'] }}</span>
                                </a>
                            </li>
                            @continue
                        @endif

                        <li>
                            <details @if ($group['is_open']) open @endif>
                                <summary data-tip="{{ $group['label'] }}"
                                    class="is-drawer-close:tooltip {{ $group['is_open'] ? 'bg-base-300 text-primary font-semibold' : 'text-stone-700 hover:bg-base-300' }}">
                                    <i class="{{ $group['icon'] }} text-lg"></i>
                                    <span class="is-drawer-close:hidden">{{ $group['label'] }}</span>
                                </summary>
                                <ul class="ms-2 border-l border-base-300 is-drawer-close:hidden">
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
