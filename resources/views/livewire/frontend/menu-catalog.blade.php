<div>
    @php $isOffline = $mode === 'offline'; @endphp

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-success/30 bg-success/10 px-4 py-3 text-sm font-medium text-success">
            {{ session('success') }}
        </div>
    @endif

    @error('cart')
        <div class="mb-4 rounded-xl border border-error/30 bg-error/10 px-4 py-3 text-sm text-error">{{ $message }}</div>
    @enderror

    {{-- Header --}}
    <section class="rounded-2xl border border-base-300 bg-base-100 p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Menu Makanan & Minuman</h1>
                <p class="mt-0.5 text-sm text-base-content/70">
                    {{ $isOffline ? 'Mode Offline: pilih menu lalu kirim pesanan langsung ke dapur.' : 'Mode Online: pilih menu, lanjutkan ke cart, lalu booking meja.' }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="setMode('online')"
                    class="btn btn-sm {{ ! $isOffline ? 'btn-primary' : 'btn-ghost border border-base-300' }}">
                    Online
                </button>
                <button type="button" wire:click="setMode('offline')"
                    class="btn btn-sm {{ $isOffline ? 'btn-primary' : 'btn-ghost border border-base-300' }}">
                    Offline QR
                </button>
            </div>
        </div>

        @if ($isOffline && $selectedTable)
            <div class="mt-3 rounded-xl border border-warning/30 bg-warning/10 px-3 py-2 text-sm">
                <p class="font-semibold">Meja Aktif: {{ $selectedTable->code }}</p>
                <p class="text-base-content/70">Kapasitas {{ $selectedTable->capacity }} orang</p>
            </div>
        @endif
    </section>

    <div class="mt-4 grid gap-4 xl:grid-cols-12 xl:items-start">

        {{-- Kiri: Kategori + Search + Grid --}}
        <div @class([
            'space-y-4',
            'xl:col-span-7' => $cartCount > 0,
            'xl:col-span-12' => $cartCount <= 0,
        ])>

            {{-- Filter Kategori --}}
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" wire:click="setCategory()"
                    class="btn btn-sm rounded-full {{ is_null($activeCategoryId) ? 'btn-primary' : 'btn-ghost border border-base-300' }}">
                    Semua
                    <span class="badge badge-sm">{{ $totalAvailable }}</span>
                </button>
                @foreach ($categories as $category)
                    <button type="button" wire:click="setCategory({{ $category->id }})"
                        class="btn btn-sm rounded-full {{ $activeCategoryId === $category->id ? 'btn-primary' : 'btn-ghost border border-base-300' }}">
                        {{ $category->name }}
                        <span class="badge badge-sm">{{ $category->menus_count }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Search --}}
            <div class="relative">
                <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></i>
                <input type="text" class="input input-bordered w-full pl-10"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari menu, deskripsi, atau SKU...">
            </div>

            {{-- Grid Menu --}}
            <div @class([
                'grid gap-3 sm:grid-cols-2',
                'xl:grid-cols-2 2xl:grid-cols-3' => $cartCount > 0,
                'xl:grid-cols-3 2xl:grid-cols-4' => $cartCount <= 0,
            ])>
                @forelse ($menus as $menu)
                    <article class="overflow-hidden rounded-2xl border border-base-200 bg-base-100 shadow-sm">
                        <div class="relative aspect-[4/3]">
                            <button type="button" wire:click="showMenuDetail('{{ $menu->id }}')"
                                class="absolute inset-0 z-10 flex cursor-pointer items-center justify-center bg-black/50 text-white opacity-0 transition-opacity hover:opacity-50">
                                <i class="ri-expand-diagonal-2-line text-2xl"></i>
                            </button>
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
                                <button type="button" wire:click="quickAdd('{{ $menu->id }}')"
                                    class="btn btn-sm btn-neutral btn-square" aria-label="Tambah ke cart">
                                    <i class="ri-add-line text-lg"></i>
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-base-300 bg-base-100 p-8 text-center">
                        <p class="text-base-content/60">Belum ada menu tersedia pada kategori ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Kanan: Order Details --}}
        @if ($cartCount > 0)
        <aside class="xl:col-span-5 xl:sticky xl:top-4">
            <section class="rounded-2xl border border-base-300 bg-base-100 p-4">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-xl font-semibold">Order Details</h3>
                    <button type="button" wire:click="clearCart"
                        data-confirm="Reset semua item order ini?"
                        class="btn btn-sm btn-outline">
                        <i class="ri-delete-bin-line"></i> Reset Order
                    </button>
                </div>

                @if ($isOffline && $selectedTable)
                    <div class="mb-3 rounded-xl border border-warning/30 bg-warning/10 px-3 py-2 text-sm">
                        <p class="font-semibold">Meja {{ $selectedTable->code }}</p>
                        <p class="text-xs text-base-content/60">Kapasitas {{ $selectedTable->capacity }} orang</p>
                    </div>
                @endif

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
                                <button type="button" wire:click="removeItem('{{ $item['menu_id'] }}')"
                                    data-confirm="Hapus item ini dari cart?"
                                    class="btn btn-sm btn-error btn-square text-white" aria-label="Hapus item">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>

                            <div class="mt-3 flex items-center justify-end gap-2">
                                <button type="button" wire:click="decrementQty('{{ $item['menu_id'] }}')"
                                    class="btn btn-sm btn-outline btn-square">
                                    <i class="ri-subtract-line"></i>
                                </button>
                                <span class="min-w-8 text-center text-lg font-semibold">{{ $item['qty'] }}</span>
                                <button type="button" wire:click="incrementQty('{{ $item['menu_id'] }}')"
                                    class="btn btn-sm btn-outline btn-square">
                                    <i class="ri-add-line"></i>
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- Subtotal --}}
                <div class="mt-4 border-t border-base-300 pt-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-base-content/70">Total Item</span>
                        <span class="font-medium">{{ $cartCount }}</span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-lg font-semibold">
                        <span>Sub Total</span>
                        <span>Rp {{ number_format((float) $cartSubtotal, 0, ',', '.') }}</span>
                    </div>

                    <button type="button" wire:click="goToCart"
                        class="btn btn-primary mt-4 w-full">
                        <i class="ri-shopping-cart-line"></i>
                        Lanjut ke Checkout
                    </button>
                </div>
            </section>
        </aside>
        @endif
    </div>

    {{-- Modal Detail Menu --}}
    <x-modal name="menu-detail-modal" maxWidth="lg">
        @if ($selectedMenu)
            <div class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-xl font-semibold">{{ $selectedMenu['name'] }}</h3>
                        <p class="text-sm text-base-content/60">{{ $selectedMenu['category_name'] }}</p>
                    </div>
                    <button type="button" wire:click="closeMenuDetail"
                        class="btn btn-sm btn-ghost btn-circle" aria-label="Tutup">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>

                <div class="aspect-[16/10] overflow-hidden rounded-xl bg-base-200">
                    @if ($selectedMenu['image_url'] !== '')
                        <img src="{{ $selectedMenu['image_url'] }}" alt="{{ $selectedMenu['name'] }}"
                            class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-base-content/40">
                            <i class="ri-image-line text-5xl"></i>
                        </div>
                    @endif
                </div>

                <div>
                    <p class="text-2xl font-bold">
                        Rp {{ number_format((float) $selectedMenu['price'], 0, ',', '.') }}
                    </p>
                    <p class="mt-2 text-sm leading-relaxed text-base-content/80">
                        {{ $selectedMenu['description'] !== '' ? $selectedMenu['description'] : 'Belum ada deskripsi menu.' }}
                    </p>
                </div>

                <button type="button"
                    wire:click="quickAdd('{{ $selectedMenu['id'] }}')"
                    x-on:click="show = false"
                    class="btn btn-primary w-full">
                    <i class="ri-add-line"></i> Tambah ke Cart
                </button>
            </div>
        @endif
    </x-modal>
</div>
