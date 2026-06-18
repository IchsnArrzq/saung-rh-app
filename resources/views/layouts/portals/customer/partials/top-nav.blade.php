<header class="sticky top-0 z-30 border-b border-base-300 bg-base-100/95 px-4 py-3 backdrop-blur md:px-6">
    <div class="flex items-center gap-3">
        <label for="customer-drawer" class="btn btn-square btn-ghost btn-sm drawer-button" aria-label="Toggle sidebar">
            <i class="ri-menu-line text-xl"></i>
        </label>

        <a href="{{ route('public.home') }}" class="flex items-center gap-2 font-bold text-base-content md:hidden">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-box bg-primary text-sm text-primary-content">
                SR
            </span>
            SaungRH
        </a>

        <div class="mr-auto hidden md:block">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-secondary">Customer Portal</p>
            <p class="text-sm font-semibold text-base-content">Kelola booking dan pesanan restoran Anda.</p>
        </div>

        <a href="{{ route('public.home') }}" class="btn btn-ghost btn-sm hidden md:inline-flex">
            <i class="ri-external-link-line text-base"></i>
            Public Site
        </a>

        <button type="button" data-theme-toggle aria-label="Toggle dark mode" aria-pressed="false"
            class="btn btn-square btn-ghost btn-sm">
            <i data-theme-toggle-icon class="ri-moon-line text-lg"></i>
        </button>

        <details class="dropdown dropdown-end">
            <summary class="flex cursor-pointer list-none items-center gap-2 rounded-box px-1 py-1 hover:bg-base-200">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-box bg-primary text-sm font-bold text-primary-content">
                    {{ strtoupper(substr(auth()->user()->name ?? 'C', 0, 1)) }}
                </span>
                <span class="hidden min-w-0 text-left md:block">
                    <span class="block max-w-40 truncate text-sm font-semibold text-base-content">{{ auth()->user()->name ?? 'Customer' }}</span>
                    <span class="block max-w-40 truncate text-xs text-secondary">{{ auth()->user()->email ?? '-' }}</span>
                </span>
                <i class="ri-arrow-down-s-line hidden text-xl text-secondary md:block"></i>
            </summary>

            <ul class="menu dropdown-content z-40 mt-2 w-60 rounded-box bg-base-100 p-2 shadow-lg">
                <li>
                    <a href="{{ route('profile') }}" class="font-medium text-stone-700">
                        <i class="ri-user-3-line"></i>
                        Profile
                    </a>
                </li>
                <li>
                    <livewire:customer.logout-button variant="menu" />
                </li>
            </ul>
        </details>
    </div>
</header>
