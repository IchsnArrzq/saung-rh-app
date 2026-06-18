<x-customer-layout>
    <section class="rounded-2xl border border-stone-200 bg-white p-5 md:p-6">
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-stone-900">Katalog Menu</h1>
                <p class="mt-1 text-sm text-stone-600">Pilih menu untuk meja {{ $table->code }}.</p>
            </div>

            <div class="ml-auto flex flex-wrap items-center gap-2">
                <a href="{{ route('customer.menus.tables') }}" class="btn btn-sm btn-ghost">Ganti Meja</a>
                <a href="{{ route('customer.menus.cart.index', ['table_id' => $table->id]) }}"
                    class="btn btn-sm bg-amber-300 text-stone-900 hover:bg-amber-400">
                    Cart ({{ $cartCount }})
                </a>
            </div>
        </div>

        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-stone-700">
            <p class="font-semibold">Meja Aktif: {{ $table->code }}{{ $table->name ? ' - '.$table->name : '' }}</p>
            <p>Kapasitas {{ $table->capacity }} orang • Subtotal cart Rp {{ number_format($cartSubtotal, 0, ',', '.') }}</p>
        </div>

        <form method="GET" class="mt-4 flex flex-wrap items-center gap-2">
            <input type="hidden" name="table_id" value="{{ $table->id }}">
            <div class="relative w-full max-w-md">
                <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                <input type="text" class="input input-bordered w-full pl-10" name="search" value="{{ $search }}"
                    placeholder="Cari nama menu, kategori, atau SKU...">
            </div>
            <button type="submit" class="btn btn-sm bg-stone-900 text-amber-50 hover:bg-stone-700">Cari</button>
            @if ($search !== '')
                <a href="{{ route('customer.menus.index', ['table_id' => $table->id]) }}" class="btn btn-sm btn-ghost">Reset</a>
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

                    <form action="{{ route('customer.menus.cart.store') }}" method="POST" class="mt-4 space-y-2">
                        @csrf
                        <input type="hidden" name="table_id" value="{{ $table->id }}">
                        <input type="hidden" name="menu_id" value="{{ $menu->id }}">

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend text-xs">Jumlah</legend>
                            <input type="number" name="qty" min="1" max="20" value="1" class="input input-bordered input-sm w-full" required>
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend text-xs">Catatan</legend>
                            <input type="text" name="notes" class="input input-bordered input-sm w-full"
                                placeholder="contoh: kurang pedas / tanpa es">
                        </fieldset>

                        <div class="mt-3 flex items-center gap-2">
                            <span class="badge badge-outline">{{ $menu->sku ?: 'SKU -' }}</span>
                            <a href="{{ route('customer.menus.show', ['menu' => $menu, 'table_id' => $table->id]) }}"
                                class="btn btn-sm btn-outline">
                                Detail
                            </a>
                            <button type="submit" class="ml-auto btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                                Tambah ke Cart
                            </button>
                        </div>
                    </form>
                </div>
            </article>
        @empty
            <p class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-white p-5 text-center text-sm text-stone-500">
                Menu tidak ditemukan.
            </p>
        @endforelse
    </section>

    @if ($menus->hasPages())
        @php
            $start = max(1, $menus->currentPage() - 2);
            $end = min($menus->lastPage(), $menus->currentPage() + 2);
        @endphp
        <nav class="mt-6 flex justify-center">
            <div class="join">
                @if ($menus->onFirstPage())
                    <button class="join-item btn btn-sm btn-disabled">«</button>
                @else
                    <a href="{{ $menus->previousPageUrl() }}" class="join-item btn btn-sm">«</a>
                @endif

                @foreach ($menus->getUrlRange($start, $end) as $page => $url)
                    <a href="{{ $url }}" class="join-item btn btn-sm {{ $page === $menus->currentPage() ? 'btn-active' : '' }}">
                        {{ $page }}
                    </a>
                @endforeach

                @if ($menus->hasMorePages())
                    <a href="{{ $menus->nextPageUrl() }}" class="join-item btn btn-sm">»</a>
                @else
                    <button class="join-item btn btn-sm btn-disabled">»</button>
                @endif
            </div>
        </nav>
    @endif
</x-customer-layout>
