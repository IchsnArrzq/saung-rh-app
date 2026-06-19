<x-customer-layout>
    @php
        $currentUrl = request()->getRequestUri();
    @endphp

    {{-- Header --}}
    <section class="rounded-2xl border border-base-300 bg-base-100 p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Katalog Menu</h1>
                <p class="mt-0.5 text-sm text-base-content/70">
                    Meja <span class="font-semibold text-primary">{{ $table->code }}</span>
                    @if ($table->name) &mdash; {{ $table->name }} @endif
                    &middot; Kapasitas {{ $table->capacity }} orang
                </p>
            </div>
            <a href="{{ route('customer.menus.tables') }}" class="btn btn-sm btn-ghost">
                <i class="ri-refresh-line"></i> Ganti Meja
            </a>
        </div>
    </section>

    <div class="mt-4 grid gap-4 xl:grid-cols-12 xl:items-start">

        {{-- Kiri: Kategori + Search + Menu grid --}}
        <div @class([
            'space-y-4',
            'xl:col-span-7' => $cartCount > 0,
            'xl:col-span-12' => $cartCount <= 0,
        ])>

            {{-- Filter Kategori --}}
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('customer.menus.index', ['table_id' => $table->id, 'search' => $search]) }}"
                    class="btn btn-sm rounded-full {{ is_null($activeCategoryId) ? 'btn-primary' : 'btn-ghost border border-base-300' }}">
                    Semua
                    <span class="badge badge-sm">{{ $totalAvailable }}</span>
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('customer.menus.index', ['table_id' => $table->id, 'search' => $search, 'category_id' => $category->id]) }}"
                        class="btn btn-sm rounded-full {{ $activeCategoryId === $category->id ? 'btn-primary' : 'btn-ghost border border-base-300' }}">
                        {{ $category->name }}
                        <span class="badge badge-sm">{{ $category->menus_count }}</span>
                    </a>
                @endforeach
            </div>

            {{-- Search --}}
            <form method="GET">
                <input type="hidden" name="table_id" value="{{ $table->id }}">
                @if ($activeCategoryId)
                    <input type="hidden" name="category_id" value="{{ $activeCategoryId }}">
                @endif
                <div class="relative">
                    <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></i>
                    <input type="text" name="search" value="{{ $search }}" class="input input-bordered w-full pl-10"
                        placeholder="Cari menu, deskripsi, atau SKU...">
                </div>
            </form>

            {{-- Menu Grid --}}
            <div @class([
                'grid gap-3 sm:grid-cols-2',
                'xl:grid-cols-2 2xl:grid-cols-3' => $cartCount > 0,
                'xl:grid-cols-3 2xl:grid-cols-4' => $cartCount <= 0,
            ])>
                @forelse ($menus as $menu)
                    <article class="overflow-hidden rounded-2xl border border-base-200 bg-base-100 shadow-sm">
                        <div class="relative aspect-[4/3]">
                            @if ($menu->image_url)
                                <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}"
                                    class="h-full w-full rounded-2xl object-cover p-1">
                            @else
                                <div class="flex h-full items-center justify-center rounded-2xl bg-base-200 text-base-content/40">
                                    <i class="ri-image-line text-4xl"></i>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-3 p-4">
                            <div>
                                <p class="line-clamp-1 text-base font-semibold">{{ $menu->name }}</p>
                                <p class="text-xs text-base-content/60">{{ $menu->category?->name ?? 'Uncategorized' }}</p>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-lg font-semibold">
                                    Rp {{ number_format((float) $menu->price, 0, ',', '.') }}
                                </p>
                                <form action="{{ route('customer.menus.cart.store') }}" method="POST"
                                    data-confirm="Tambahkan {{ $menu->name }} ke cart?"
                                    data-confirm-title="Konfirmasi"
                                    data-confirm-yes="Ya, Tambahkan"
                                    data-confirm-no="Batal">
                                    @csrf
                                    <input type="hidden" name="table_id" value="{{ $table->id }}">
                                    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                                    <input type="hidden" name="qty" value="1">
                                    <input type="hidden" name="redirect_to" value="{{ $currentUrl }}">
                                    <button type="submit" class="btn btn-sm btn-neutral btn-square"
                                        aria-label="Tambah ke cart">
                                        <i class="ri-add-line text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-base-300 bg-base-100 p-8 text-center">
                        <p class="text-base-content/60">Menu tidak ditemukan.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($menus->hasPages())
                <nav class="flex justify-center">
                    {{ $menus->links() }}
                </nav>
            @endif
        </div>

        {{-- Kanan: Order Details --}}
        @if ($cartCount > 0)
        <aside class="xl:col-span-5 xl:sticky xl:top-4">
            <section class="rounded-2xl border border-base-300 bg-base-100 p-4">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-xl font-semibold">Order Details</h3>
                    <form action="{{ route('customer.menus.cart.clear') }}" method="POST"
                        data-confirm="Kosongkan semua item cart?">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="table_id" value="{{ $table->id }}">
                        <button type="submit" class="btn btn-sm btn-outline">
                            <i class="ri-delete-bin-line"></i> Reset Order
                        </button>
                    </form>
                </div>

                {{-- Info meja --}}
                <div class="mb-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-stone-700">
                    <p class="font-semibold">Meja {{ $table->code }}{{ $table->name ? ' — '.$table->name : '' }}</p>
                    <p class="text-xs text-stone-500">Kapasitas {{ $table->capacity }} orang</p>
                </div>

                {{-- Cart items --}}
                <div class="space-y-3">
                    @foreach ($cartItems as $item)
                        <article class="rounded-xl border border-base-300 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-base-200">
                                        @if ($item['image_url'])
                                            <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}"
                                                class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full items-center justify-center text-base-content/40">
                                                <i class="ri-image-line text-xl"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-medium leading-tight">{{ $item['name'] }}</p>
                                        <p class="mt-0.5 text-sm text-base-content/60">
                                            Rp {{ number_format((float) $item['price'], 0, ',', '.') }}
                                        </p>
                                        @if (!empty($item['notes']))
                                            <p class="mt-1 line-clamp-1 text-xs text-base-content/50">{{ $item['notes'] }}</p>
                                        @endif
                                    </div>
                                </div>
                                <form action="{{ route('customer.menus.cart.destroy', $item['menu_id']) }}" method="POST"
                                    data-confirm="Hapus item ini dari cart?">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="table_id" value="{{ $table->id }}">
                                    <input type="hidden" name="redirect_to" value="{{ $currentUrl }}">
                                    <button type="submit" class="btn btn-sm btn-error btn-square text-white"
                                        aria-label="Hapus item">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </div>

                            <div class="mt-3 flex items-center justify-end gap-2">
                                {{-- Kurangi --}}
                                <form action="{{ (int) $item['qty'] > 1 ? route('customer.menus.cart.update', $item['menu_id']) : route('customer.menus.cart.destroy', $item['menu_id']) }}"
                                    method="POST">
                                    @csrf
                                    @if ((int) $item['qty'] > 1)
                                        @method('PATCH')
                                        <input type="hidden" name="qty" value="{{ ((int) $item['qty']) - 1 }}">
                                        <input type="hidden" name="notes" value="{{ $item['notes'] ?? '' }}">
                                    @else
                                        @method('DELETE')
                                    @endif
                                    <input type="hidden" name="table_id" value="{{ $table->id }}">
                                    <input type="hidden" name="redirect_to" value="{{ $currentUrl }}">
                                    <button type="submit" class="btn btn-sm btn-outline btn-square">
                                        <i class="ri-subtract-line"></i>
                                    </button>
                                </form>

                                <span class="min-w-8 text-center text-lg font-semibold">{{ $item['qty'] }}</span>

                                {{-- Tambah --}}
                                <form action="{{ route('customer.menus.cart.update', $item['menu_id']) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="table_id" value="{{ $table->id }}">
                                    <input type="hidden" name="qty" value="{{ ((int) $item['qty']) + 1 }}">
                                    <input type="hidden" name="notes" value="{{ $item['notes'] ?? '' }}">
                                    <input type="hidden" name="redirect_to" value="{{ $currentUrl }}">
                                    <button type="submit" class="btn btn-sm btn-outline btn-square">
                                        <i class="ri-add-line"></i>
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- Subtotal + Checkout --}}
                <div class="mt-4 border-t border-base-300 pt-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-base-content/70">Total Item</span>
                        <span class="font-medium">{{ $cartCount }}</span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-lg font-semibold">
                        <span>Sub Total</span>
                        <span>Rp {{ number_format((float) $cartSubtotal, 0, ',', '.') }}</span>
                    </div>

                    <form action="{{ route('customer.menus.cart.checkout') }}" method="POST"
                        data-confirm="Kirim pesanan ini ke dapur?" class="mt-4 space-y-3">
                        @csrf
                        <input type="hidden" name="table_id" value="{{ $table->id }}">
                        <label class="space-y-1">
                            <span class="block text-xs font-semibold uppercase tracking-wide text-base-content/60">Catatan Order</span>
                            <textarea name="notes" class="textarea textarea-bordered w-full" rows="2"
                                placeholder="contoh: utamakan menu tanpa pedas"></textarea>
                        </label>
                        <button type="submit" class="btn btn-primary w-full">
                            <i class="ri-save-2-line"></i>
                            Buat Order
                        </button>
                    </form>
                </div>
            </section>
        </aside>
        @endif
    </div>
</x-customer-layout>
