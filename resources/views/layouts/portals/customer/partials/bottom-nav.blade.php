<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-stone-200 bg-white/95 backdrop-blur md:hidden">
    <ul class="mx-auto grid w-full max-w-6xl grid-cols-4 text-xs font-semibold">
        <li>
            <a href="{{ route('customer.dashboard') }}" class="flex flex-col items-center gap-1 py-3 {{ request()->routeIs('customer.dashboard') ? 'text-emerald-700' : 'text-stone-500' }}">
                <i class="ri-home-5-line text-lg"></i>
                <span>Home</span>
            </a>
        </li>
        <li>
            <a href="{{ route('customer.menus.tables') }}" class="flex flex-col items-center gap-1 py-3 {{ request()->routeIs('customer.menus.*') ? 'text-emerald-700' : 'text-stone-500' }}">
                <i class="ri-restaurant-line text-lg"></i>
                <span>Menu</span>
            </a>
        </li>
        <li>
            <a href="{{ route('customer.bookings.create') }}" class="flex flex-col items-center gap-1 py-3 {{ request()->routeIs('customer.bookings.*') ? 'text-emerald-700' : 'text-stone-500' }}">
                <i class="ri-calendar-check-line text-lg"></i>
                <span>Booking</span>
            </a>
        </li>
        <li>
            <a href="{{ route('profile') }}" class="flex flex-col items-center gap-1 py-3 text-stone-500">
                <i class="ri-user-line text-lg"></i>
                <span>Profil</span>
            </a>
        </li>
    </ul>
</nav>
