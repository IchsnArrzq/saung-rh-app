<x-customer-layout>
    @php
        $currentUrl = request()->getRequestUri();
    @endphp

    <section class="rounded-2xl border border-stone-200 bg-white p-5 md:p-6">
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-stone-900">Katalog Menu</h1>
                <p class="mt-1 text-sm text-stone-600">Pilih menu untuk meja {{ $table->code }}.</p>
            </div>

            <div class="ml-auto flex flex-wrap items-center gap-2">
                <a href="{{ route('customer.menus.tables') }}" class="btn btn-sm btn-ghost">Ganti Meja</a>
            </div>
        </div>

        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-stone-700">
            <p class="font-semibold">Meja Aktif: {{ $table->code }}{{ $table->name ? ' - '.$table->name : '' }}</p>
            <p>Kapasitas {{ $table->capacity }} orang - Subtotal cart Rp {{ number_format($cartSubtotal, 0, ',', '.') }}</p>
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

    <div class="mt-6 grid gap-5 {{ $cartCount > 0 ? 'xl:grid-cols-[minmax(0,1fr)_380px]' : '' }} xl:items-start">
        <div class="min-w-0">
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 {{ $cartCount > 0 ? '' : '2xl:grid-cols-4' }}">
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

                            <form action="{{ route('customer.menus.cart.store') }}" method="POST" class="mt-4 space-y-2"
                                data-confirm="Tambahkan {{ $menu->name }} ke cart?"
                                data-confirm-title="Konfirmasi Cart"
                                data-confirm-yes="Ya, Tambahkan"
                                data-confirm-no="Batal">
                                @csrf
                                <input type="hidden" name="table_id" value="{{ $table->id }}">
                                <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                                <input type="hidden" name="redirect_to" value="{{ $currentUrl }}">

                                <div class="grid gap-2 sm:grid-cols-[90px_1fr]">
                                    <label class="space-y-1">
                                        <span class="block text-xs font-semibold text-stone-600">Jumlah</span>
                                        <input type="number" name="qty" min="1" max="20" value="1" class="input input-bordered input-sm w-full" required>
                                    </label>

                                    <label class="space-y-1">
                                        <span class="block text-xs font-semibold text-stone-600">Catatan</span>
                                        <input type="text" name="notes" class="input input-bordered input-sm w-full"
                                            placeholder="contoh: kurang pedas">
                                    </label>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span class="badge badge-outline">{{ $menu->sku ?: 'SKU -' }}</span>
                                    <a href="{{ route('customer.menus.show', ['menu' => $menu, 'table_id' => $table->id]) }}"
                                        class="btn btn-sm btn-outline">
                                        Detail
                                    </a>
                                    <button type="submit" class="ml-auto btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                                        Tambah
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
                            <button class="join-item btn btn-sm btn-disabled">&laquo;</button>
                        @else
                            <a href="{{ $menus->previousPageUrl() }}" class="join-item btn btn-sm">&laquo;</a>
                        @endif

                        @foreach ($menus->getUrlRange($start, $end) as $page => $url)
                            <a href="{{ $url }}" class="join-item btn btn-sm {{ $page === $menus->currentPage() ? 'btn-active' : '' }}">
                                {{ $page }}
                            </a>
                        @endforeach

                        @if ($menus->hasMorePages())
                            <a href="{{ $menus->nextPageUrl() }}" class="join-item btn btn-sm">&raquo;</a>
                        @else
                            <button class="join-item btn btn-sm btn-disabled">&raquo;</button>
                        @endif
                    </div>
                </nav>
            @endif
        </div>

        @if ($cartCount > 0)
        <aside class="xl:sticky xl:top-5">
            <section class="rounded-3xl border border-stone-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-stone-900">Cart Meja {{ $table->code }}</h2>
                        <p class="mt-1 text-sm text-stone-500">Periksa pesanan sebelum dikirim ke dapur.</p>
                    </div>
                    <a href="{{ route('customer.menus.cart.index', ['table_id' => $table->id]) }}" class="btn btn-sm btn-ghost">
                        Detail
                    </a>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach ($cartItems as $item)
                        <article class="rounded-2xl border border-stone-200 p-3">
                            <div class="flex items-start gap-3">
                                <img src="{{ $item['image_url'] ?: 'https://picsum.photos/seed/'.urlencode((string) $item['menu_id']).'/200/160' }}"
                                    alt="{{ $item['name'] }}" class="h-16 w-20 rounded-xl object-cover">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('customer.menus.show', ['menu' => $item['menu_id'], 'table_id' => $table->id]) }}"
                                        class="line-clamp-1 font-semibold text-stone-900 hover:text-emerald-800 hover:underline">
                                        {{ $item['name'] }}
                                    </a>
                                    <p class="mt-1 text-sm text-stone-500">Rp {{ number_format((float) $item['price'], 0, ',', '.') }}</p>
                                    @if (! empty($item['notes']))
                                        <p class="mt-1 line-clamp-2 text-xs text-stone-500">Catatan: {{ $item['notes'] }}</p>
                                    @endif
                                </div>

                                <form action="{{ route('customer.menus.cart.destroy', $item['menu_id']) }}" method="POST"
                                    data-confirm="Hapus item ini dari cart?">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="table_id" value="{{ $table->id }}">
                                    <input type="hidden" name="redirect_to" value="{{ $currentUrl }}">
                                    <button type="submit" class="btn btn-xs btn-ghost text-rose-600">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </div>

                            <div class="mt-3 flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-stone-800">
                                    Rp {{ number_format(((float) $item['price']) * ((int) $item['qty']), 0, ',', '.') }}
                                </p>

                                <div class="join">
                                    @if ((int) $item['qty'] > 1)
                                        <form action="{{ route('customer.menus.cart.update', $item['menu_id']) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="table_id" value="{{ $table->id }}">
                                            <input type="hidden" name="qty" value="{{ ((int) $item['qty']) - 1 }}">
                                            <input type="hidden" name="notes" value="{{ $item['notes'] ?? '' }}">
                                            <input type="hidden" name="redirect_to" value="{{ $currentUrl }}">
                                            <button type="submit" class="join-item btn btn-sm btn-outline">-</button>
                                        </form>
                                    @else
                                        <form action="{{ route('customer.menus.cart.destroy', $item['menu_id']) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="table_id" value="{{ $table->id }}">
                                            <input type="hidden" name="redirect_to" value="{{ $currentUrl }}">
                                            <button type="submit" class="join-item btn btn-sm btn-outline">-</button>
                                        </form>
                                    @endif

                                    <span class="join-item flex h-8 min-w-10 items-center justify-center border-y border-stone-300 bg-white px-3 text-sm font-semibold">
                                        {{ $item['qty'] }}
                                    </span>

                                    <form action="{{ route('customer.menus.cart.update', $item['menu_id']) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="table_id" value="{{ $table->id }}">
                                        <input type="hidden" name="qty" value="{{ ((int) $item['qty']) + 1 }}">
                                        <input type="hidden" name="notes" value="{{ $item['notes'] ?? '' }}">
                                        <input type="hidden" name="redirect_to" value="{{ $currentUrl }}">
                                        <button type="submit" class="join-item btn btn-sm btn-outline">+</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-4 border-t border-stone-200 pt-4">
                    <div class="flex items-center justify-between text-sm text-stone-600">
                        <span>Total Item</span>
                        <span class="font-semibold text-stone-900">{{ $cartCount }}</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-lg font-bold text-stone-900">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format((float) $cartSubtotal, 0, ',', '.') }}</span>
                    </div>

                    <form action="{{ route('customer.menus.cart.checkout') }}" method="POST"
                        data-confirm="Kirim pesanan ini ke admin?" class="mt-4 space-y-3">
                        @csrf
                        <input type="hidden" name="table_id" value="{{ $table->id }}">
                        <label class="space-y-1.5">
                            <span class="block text-xs font-semibold text-stone-600">Catatan Order</span>
                            <textarea name="notes" class="textarea textarea-bordered w-full" rows="2"
                                placeholder="contoh: utamakan menu tanpa pedas"></textarea>
                        </label>
                        <button type="submit" class="btn w-full bg-emerald-800 text-amber-50 hover:bg-emerald-700" @disabled($cartCount <= 0)>
                            Buat Order
                        </button>
                    </form>
                </div>
            </section>
        </aside>
        @endif
    </div>
</x-customer-layout>
