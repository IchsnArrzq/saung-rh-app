<x-customer-layout>
    <section class="rounded-2xl border border-stone-200 bg-white p-5 md:p-6">
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-stone-900">Katalog Menu</h1>
                <p class="mt-1 text-sm text-stone-600">Lihat daftar menu tersedia sebelum melakukan booking.</p>
            </div>

            <a href="{{ route('customer.bookings.create') }}"
                class="ml-auto btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                Booking Meja
            </a>
        </div>

        <form method="GET" class="mt-4 flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ $search }}" class="input input-bordered w-full max-w-md"
                placeholder="Cari nama menu, kategori, atau SKU...">
            <button type="submit" class="btn btn-sm bg-stone-900 text-amber-50 hover:bg-stone-700">Cari</button>
            @if ($search !== '')
                <a href="{{ route('customer.menus.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            @endif
        </form>
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($menus as $menu)
            <article class="overflow-hidden rounded-3xl border border-stone-200 bg-white">
                <div class="aspect-[4/3] w-full bg-stone-100">
                    <img src="{{ $menu->image_url ?: 'https://picsum.photos/seed/'.urlencode((string) $menu->id).'/700/500' }}"
                        alt="{{ $menu->name }}" class="h-full w-full object-cover">
                </div>

                <div class="p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-stone-500">{{ $menu->category->name ?? 'Menu' }}</p>
                    <h2 class="mt-1 text-lg font-semibold text-stone-900">{{ $menu->name }}</h2>
                    <p class="mt-1 text-sm text-stone-600">{{ \Illuminate\Support\Str::limit($menu->description ?: 'Menu favorit restoran.', 85) }}</p>
                    <p class="mt-3 text-lg font-bold text-emerald-800">Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</p>

                    <div class="mt-4 flex items-center gap-2">
                        <span class="badge badge-outline">{{ $menu->sku ?: 'SKU -' }}</span>
                        <a href="{{ route('customer.bookings.create') }}" class="ml-auto btn btn-xs bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                            Pilih Saat Booking
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <p class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-white p-5 text-center text-sm text-stone-500">
                Menu tidak ditemukan.
            </p>
        @endforelse
    </section>

    <div class="mt-6">{{ $menus->links() }}</div>
</x-customer-layout>
