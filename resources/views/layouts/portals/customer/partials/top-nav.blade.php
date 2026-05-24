<header class="border-b border-stone-200 bg-white">
    <div class="mx-auto flex w-full max-w-6xl items-center gap-3 px-4 py-4 md:px-6">
        <a href="{{ route('public.home') }}" class="text-2xl font-semibold text-emerald-800"
            style="font-family: 'Playfair Display', serif;">
            SaungRH<span class="text-orange-500">.</span>
        </a>

        <nav class="ml-auto hidden md:block">
            <ul class="flex items-center gap-2 text-sm font-semibold">
                <li>
                    <a href="{{ route('customer.dashboard') }}"
                        class="rounded-full px-4 py-2 {{ request()->routeIs('customer.dashboard') ? 'bg-emerald-800 text-amber-50' : 'text-stone-700 hover:bg-stone-100' }}">
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('customer.menus.tables') }}"
                        class="rounded-full px-4 py-2 {{ request()->routeIs('customer.menus.*') ? 'bg-emerald-800 text-amber-50' : 'text-stone-700 hover:bg-stone-100' }}">
                        Menu
                    </a>
                </li>
                <li>
                    <a href="{{ route('customer.bookings.create') }}"
                        class="rounded-full px-4 py-2 {{ request()->routeIs('customer.bookings.*') ? 'bg-emerald-800 text-amber-50' : 'text-stone-700 hover:bg-stone-100' }}">
                        Booking
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile') }}" class="rounded-full px-4 py-2 text-stone-700 hover:bg-stone-100">
                        Profil
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</header>
