<aside class="sticky top-0 hidden h-screen w-72 shrink-0 flex-col bg-base-200 px-3 py-4 md:flex">
    <a href="{{ route('public.home') }}" class="flex items-center gap-3 rounded-box px-3 py-2">
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-box bg-primary text-lg font-bold text-primary-content">
            SR
        </span>
        <span>
            <span class="block text-lg font-bold text-base-content">SaungRH</span>
            <span class="block text-xs font-medium text-secondary">Customer Portal</span>
        </span>
    </a>

    <nav class="mt-6 grow">
        <ul class="menu w-full gap-1 p-0">
            <li>
                <a href="{{ route('customer.dashboard') }}"
                    class="{{ request()->routeIs('customer.dashboard') ? 'active bg-primary text-primary-content' : 'text-stone-700 hover:bg-base-300' }}">
                    <i class="ri-dashboard-line text-lg"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('customer.menus.tables') }}"
                    class="{{ request()->routeIs('customer.menus.tables') ? 'active bg-primary text-primary-content' : 'text-stone-700 hover:bg-base-300' }}">
                    <i class="ri-layout-grid-line text-lg"></i>
                    Pilih Meja
                </a>
            </li>
            <li>
                <a href="{{ route('customer.menus.index') }}"
                    class="{{ request()->routeIs('customer.menus.index') ? 'active bg-primary text-primary-content' : 'text-stone-700 hover:bg-base-300' }}">
                    <i class="ri-bowl-line text-lg"></i>
                    Katalog Menu
                </a>
            </li>
            <li>
                <a href="{{ route('customer.menus.cart.index') }}"
                    class="{{ request()->routeIs('customer.menus.cart.*') ? 'active bg-primary text-primary-content' : 'text-stone-700 hover:bg-base-300' }}">
                    <i class="ri-shopping-basket-2-line text-lg"></i>
                    Cart
                </a>
            </li>
            <li>
                <a href="{{ route('customer.bookings.create') }}"
                    class="{{ request()->routeIs('customer.bookings.*') ? 'active bg-primary text-primary-content' : 'text-stone-700 hover:bg-base-300' }}">
                    <i class="ri-calendar-check-line text-lg"></i>
                    Booking
                </a>
            </li>
        </ul>
    </nav>

    <div class="border-t border-base-300 pt-3">
        <a href="{{ route('profile') }}"
            class="flex items-center gap-3 rounded-box px-3 py-2 text-sm font-semibold text-stone-700 hover:bg-base-300">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-box bg-base-100 text-sm font-bold text-primary">
                {{ strtoupper(substr(auth()->user()->name ?? 'C', 0, 1)) }}
            </span>
            <span class="min-w-0">
                <span class="block truncate">{{ auth()->user()->name ?? 'Customer' }}</span>
                <span class="block truncate text-xs font-medium text-secondary">{{ auth()->user()->email ?? '-' }}</span>
            </span>
        </a>
    </div>
</aside>
