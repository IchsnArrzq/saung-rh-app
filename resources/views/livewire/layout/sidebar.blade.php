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
    {{-- `.drawer-side` is the real scroll container: the <aside> inside it is
         `min-h-full`, so it grows to its content height and never scrolls
         itself. The scrollbar treatment has to live here to have any effect.

         `is-drawer-close:overflow-visible` is required by the collapsed rail:
         daisyUI ships `:where(.drawer-side){overflow:hidden}` and only relaxes
         it to `overflow-y:auto`, so `overflow-x` stays hidden and any flyout
         would be clipped at the 64px rail edge. The rail only renders group
         icons, so it never needs to scroll. --}}
    <div id="admin-sidebar" class="drawer-side sidebar-scroll h-[calc(100vh-0.1rem)] is-drawer-close:overflow-visible"
        data-floating-scrollbar>
        <label for="admin-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
        {{-- `overflow-x-hidden` only applies while expanded; collapsed it would
             clip the flyouts along with the drawer-side rule above. --}}
        <aside
            class="flex min-h-full max-w-[85vw] flex-col bg-base-200 is-drawer-close:w-16 is-drawer-open:w-72 is-drawer-open:overflow-x-hidden">
            <a href="{{ route('public.home') }}"
                class="flex items-center gap-3 rounded-box px-3 py-2 is-drawer-close:justify-center is-drawer-close:px-2">
                <span
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-box bg-primary text-lg font-bold text-primary-content">
                    SR
                </span>
                <span class="is-drawer-close:hidden">
                    <span class="block text-lg font-bold text-base-content">SaungRH</span>
                    <span class="block text-xs font-medium text-secondary">Admin Portal</span>
                </span>
            </a>

            {{-- Expanded: accordion. Collapsed: icon rail with hover/focus
                 flyouts. Two trees rather than one restyled tree, because a
                 closed <details> hides its content through the UA shadow slot
                 -- no utility class can force it visible, so the collapsed
                 state could never surface its own submenu. Only one tree is
                 ever rendered (`display:none` keeps the other out of the
                 accessibility tree as well). --}}
            <nav class="grow is-drawer-close:hidden" aria-label="Navigasi utama">
                <ul class="menu w-full gap-1 rounded-2xl p-2">
                    @foreach ($groups as $group)
                        <li>
                            <details @if ($group['is_open']) open @endif>
                                <summary
                                    class="{{ $group['is_active'] ? 'bg-base-300 text-primary font-semibold' : 'text-stone-700 hover:bg-base-300' }}">
                                    <i class="{{ $group['icon'] }} text-lg" aria-hidden="true"></i>
                                    <span>{{ $group['label'] }}</span>
                                </summary>
                                <ul class="ms-2">
                                    @foreach ($group['items'] as $item)
                                        <x-sidebar.nav-link :item="$item" />
                                    @endforeach
                                </ul>
                            </details>
                        </li>
                    @endforeach
                </ul>
            </nav>

            {{-- The rail only ever shows at >=lg: below that breakpoint an
                 unchecked toggle hides the drawer entirely, so hover-driven
                 flyouts never have to work on touch. --}}
            <nav class="grow is-drawer-open:hidden" aria-label="Navigasi utama (ringkas)">
                <ul class="flex flex-col gap-1 p-2">
                    @foreach ($groups as $group)
                        @php
                            $railHref = $group['items'][0]['url'];
                            $railBadges = collect($group['items'])->pluck('badge_value')->filter();
                            // Counts roll up to the group so the rail keeps the
                            // number the expanded tree shows on the leaf. Text
                            // badges ("WS") have nothing to add up, so they fall
                            // back to a plain presence dot.
                            $railCount = (int) $railBadges->filter(fn($value) => is_numeric($value))->sum();
                        @endphp
                        <li class="group/rail relative">
                            {{-- Clicking the icon goes to the group's first
                                 page; hover/focus opens the full list. An icon
                                 that only toggles a popup would be a dead
                                 target for anyone who clicks it. --}}
                            <a wire:navigate href="{{ $railHref }}" aria-haspopup="true"
                                @if ($group['is_active']) aria-current="true" @endif
                                class="relative flex h-11 w-full items-center justify-center rounded-box transition-colors {{ $group['is_active'] ? 'bg-base-300 text-primary' : 'text-stone-700 hover:bg-base-300' }}">
                                {{-- Colour alone is a weak signal on a 64px rail
                                     and disappears for colour-blind users, so
                                     the active group also gets a shape cue. --}}
                                @if ($group['is_active'])
                                    <span class="absolute inset-y-1 left-0 w-1 rounded-full bg-primary"
                                        aria-hidden="true"></span>
                                @endif
                                <i class="{{ $group['icon'] }} text-lg" aria-hidden="true"></i>
                                <span class="sr-only">{{ $group['label'] }}</span>
                                @if ($railCount > 0)
                                    <x-badge color="primary" size="xs"
                                        class="absolute -right-1 -top-1 ring-2 ring-base-200">
                                        {{ $railCount > 9 ? '9+' : $railCount }}
                                    </x-badge>
                                    <span class="sr-only">{{ $railCount }} item perlu perhatian</span>
                                @elseif ($railBadges->isNotEmpty())
                                    <span
                                        class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-primary ring-2 ring-base-200"
                                        aria-hidden="true"></span>
                                @endif
                            </a>

                            {{-- `ps-2` sits inside the panel wrapper so the gap
                                 between rail and panel stays hoverable and the
                                 pointer never crosses a dead zone. --}}
                            <div class="invisible absolute start-full top-0 z-50 ps-2 opacity-0 transition-opacity duration-150 group-focus-within/rail:visible group-focus-within/rail:opacity-100 group-hover/rail:visible group-hover/rail:opacity-100"
                                role="none">
                                <div
                                    class="max-h-[70vh] w-64 overflow-y-auto rounded-box bg-base-100 p-2 shadow-lg ring-1 ring-base-300">
                                    <p class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-secondary">
                                        {{ $group['label'] }}
                                    </p>
                                    <ul class="menu w-full gap-0.5 p-0">
                                        @foreach ($group['items'] as $item)
                                            <x-sidebar.nav-link :item="$item" />
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </aside>
    </div>
@endif
