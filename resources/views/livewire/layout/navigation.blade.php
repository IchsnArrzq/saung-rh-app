<?php

use App\Livewire\Actions\Logout;
use App\Support\SidebarNavigation;
use Livewire\Volt\Component;

new class extends Component {
    public $initial;
    public $profileUrl;
    public string $settingsUrl = '#';
    public string $navigationMenuPreference = 'sidebar';

    /** @var array<int, array<string, mixed>> */
    public array $groups = [];

    public function mount(SidebarNavigation $navigation): void
    {
        $this->initial = strtoupper(substr(auth()->user()->name ?? 'A', 0, 1));
        $this->profileUrl = route('profile');
        $this->settingsUrl = Route::has('settings.navigation') ? route('settings.navigation') : '#';
        $this->groups = $navigation->forCurrentUser();

        $preference = (string) (auth()->user()?->navigation_menu_preference ?? 'sidebar');
        $this->navigationMenuPreference = in_array($preference, ['sidebar', 'navbar'], true)
            ? $preference
            : 'sidebar';
    }
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>
<header class="border-b border-base-300 bg-base-100 px-4 py-4 md:px-6">
    <div class="flex items-center gap-3">

        @if ($navigationMenuPreference === 'sidebar')
            <label for="admin-drawer" aria-label="open sidebar" class="btn btn-square btn-ghost">
                <!-- Sidebar toggle icon -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linejoin="round" stroke-linecap="round"
                    stroke-width="2" fill="none" stroke="currentColor" class="my-1.5 inline-block size-4">
                    <path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"></path>
                    <path d="M9 4v16"></path>
                    <path d="M14 10l2 2l-2 2"></path>
                </svg>
            </label>
        @endif

        <div class="mr-auto flex items-center gap-3">
            <img src="{{ asset('assets/logo-cr-mark.png') }}" alt="CR Cafe & Resto logo mark"
                class="h-10 w-10 rounded-lg border border-base-300 bg-base-100 p-1 object-contain md:h-11 md:w-11">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Control Room</p>
            </div>
        </div>

        <a href="/"
            class="hidden items-center gap-2 rounded-xl border border-base-300 bg-base-100 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-secondary hover:text-secondary md:inline-flex">
            <i class="ri-external-link-line text-base"></i>
            <span>Public Site</span>
        </a>

        <details class="dropdown dropdown-end">
            <summary
                class="flex cursor-pointer list-none items-center gap-2 rounded-xl border border-base-300 bg-base-100 px-2 py-1 pr-3">
                <span
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-sm font-bold text-primary-content">
                    {{ $initial }}
                </span>
                <span class="hidden text-left md:block">
                    <span
                        class="block text-sm font-semibold text-stone-800">{{ auth()->user()->name ?? 'Admin' }}</span>
                    <span class="block text-xs text-stone-500">{{ auth()->user()->email ?? '-' }}</span>
                </span>
                <i class="ri-arrow-down-s-line text-xl text-stone-500"></i>
            </summary>
            <ul class="menu dropdown-content mt-2 w-56 rounded-2xl border border-base-300 bg-base-100 p-2 shadow-lg">
                <li>
                    <a href="{{ $profileUrl }}" class="font-medium text-stone-700">
                        <i class="ri-user-3-line"></i>
                        Profile
                    </a>
                </li>
                <li>
                    <a href="{{ $settingsUrl }}" class="font-medium text-stone-700">
                        <i class="ri-settings-3-line"></i>
                        Settings
                    </a>
                </li>
                <li>
                    <button type="button" wire:click="logout"
                        class="flex w-full items-center gap-2 text-left font-medium text-stone-700">
                        <i class="ri-logout-box-r-line"></i>
                        Logout
                    </button>
                </li>
            </ul>
        </details>
    </div>

    @if ($navigationMenuPreference === 'navbar' && count($groups) > 0)
        <div class="mt-3">
            <nav class="overflow-visible">
                <ul class="menu menu-horizontal flex-wrap gap-1 rounded-xl border border-base-300 bg-base-100 p-1">
                    @foreach ($groups as $group)
                        @if (count($group['items']) === 1 && $group['label'] === 'Dashboard')
                            @php($item = $group['items'][0])
                            <li>
                                <a href="{{ $item['url'] }}"
                                    class="{{ $item['is_active'] ? 'bg-primary text-primary-content' : 'text-stone-700 hover:bg-base-300' }}">
                                    <i class="{{ $item['icon'] }}"></i>
                                    {{ $item['label'] }}
                                    @if (!empty($item['badge_value']))
                                        <span class="badge badge-sm badge-primary">{{ $item['badge_value'] }}</span>
                                    @endif
                                </a>
                            </li>
                            @continue
                        @endif

                        <li>
                            <details @if ($group['is_open']) open @endif>
                                <summary
                                    class="{{ $group['is_open'] ? 'bg-base-300 text-primary font-semibold' : 'text-stone-700 hover:bg-base-300' }}">
                                    <i class="{{ $group['icon'] }}"></i>
                                    {{ $group['label'] }}
                                </summary>
                                <ul class="menu z-20 mt-1 w-72 rounded-xl border border-base-300 bg-base-100 p-2 shadow-xl">
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
        </div>
    @endif
</header>
