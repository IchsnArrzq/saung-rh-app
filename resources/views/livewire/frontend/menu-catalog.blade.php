<div>
    @if (session('success'))
        <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
    @endif

    @error('cart')
        <x-alert type="error" class="mb-4">{{ $message }}</x-alert>
    @enderror

    {{-- Header --}}
    <x-page-header title="Menu Makanan & Minuman"
        description="Pilih menu lalu kirim pesanan langsung ke dapur.">
        <x-slot:actions>
            @if ($selectedTable)
                <div class="rounded-xl border border-success/30 bg-success/10 px-3 py-2 text-sm">
                    <p class="font-semibold"><i class="ri-checkbox-circle-line text-success"></i> Meja {{ $selectedTable->code }}</p>
                    <p class="text-xs text-base-content/60">Kapasitas {{ $selectedTable->capacity }} orang</p>
                </div>
            @else
                <x-button variant="ghost" size="sm" icon="ri-calendar-check-line" :href="route('login')">
                    Mau reservasi? Masuk
                </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="mt-4 grid gap-4 xl:grid-cols-12 xl:items-start">

        {{-- Kiri: Kategori + Search + Grid --}}
        <div @class([
            'space-y-4',
            'xl:col-span-7' => $cartCount > 0,
            'xl:col-span-12' => $cartCount <= 0,
        ])>

            {{-- Filter Kategori --}}
            <div class="flex flex-wrap items-center gap-2">
                <x-button size="sm" wire:click="setCategory()"
                    :variant="is_null($activeCategoryId) ? 'primary' : 'ghost'"
                    class="rounded-full {{ is_null($activeCategoryId) ? '' : 'border border-base-300' }}">
                    Semua
                    <span class="badge badge-sm">{{ $totalAvailable }}</span>
                </x-button>

                @foreach ($categories as $category)
                    <x-button size="sm" wire:click="setCategory({{ $category->id }})"
                        :variant="$activeCategoryId === $category->id ? 'primary' : 'ghost'"
                        class="rounded-full {{ $activeCategoryId === $category->id ? '' : 'border border-base-300' }}">
                        {{ $category->name }}
                        <span class="badge badge-sm">{{ $category->menus_count }}</span>
                    </x-button>
                @endforeach
            </div>

            {{-- Search --}}
            <x-search-input wire:model.live.debounce.300ms="search"
                placeholder="Cari menu, deskripsi, atau SKU..." label="Cari menu" />

            {{-- Grid Menu --}}
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

                            <x-button variant="neutral" size="sm" shape="circle" icon="ri-information-line"
                                label="Lihat detail {{ $menu->name }}" class="absolute right-2 top-2 z-10 opacity-90"
                                wire:click="showMenuDetail('{{ $menu->id }}')" />
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
                                <x-button variant="neutral" size="sm" shape="square" icon="ri-add-line text-lg"
                                    label="Tambah {{ $menu->name }} ke keranjang"
                                    wire:click="quickAdd('{{ $menu->id }}')" loading="quickAdd('{{ $menu->id }}')" />
                            </div>
                        </div>
                    </article>
                @empty
                    <x-empty-state class="col-span-full" icon="ri-restaurant-line"
                        title="Belum ada menu tersedia"
                        description="Tidak ada menu pada kategori ini. Coba kategori lain atau ubah kata pencarian." />
                @endforelse
            </div>
        </div>

        {{-- Kanan: Order Details --}}
        @if ($cartCount > 0)
            <aside class="xl:col-span-5 xl:sticky xl:top-4">
                <x-card>
                    <div class="mb-4 flex items-center justify-between gap-2">
                        <h3 class="text-xl font-semibold">Rincian Pesanan</h3>
                        <x-button variant="outline" size="sm" icon="ri-delete-bin-line" wire:click="clearCart"
                            data-confirm="Reset semua item order ini?">
                            Reset Order
                        </x-button>
                    </div>

                    @if ($selectedTable)
                        <div class="mb-3 rounded-xl border border-success/30 bg-success/10 px-3 py-2 text-sm">
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
                                    <x-button variant="error" size="sm" shape="square" icon="ri-delete-bin-line"
                                        label="Hapus {{ $item['name'] }}" class="text-white"
                                        wire:click="removeItem('{{ $item['menu_id'] }}')"
                                        data-confirm="Hapus item ini dari cart?" />
                                </div>

                                <div class="mt-3 flex items-center justify-end gap-2">
                                    <x-button variant="outline" size="sm" shape="square" icon="ri-subtract-line"
                                        label="Kurangi jumlah {{ $item['name'] }}"
                                        wire:click="decrementQty('{{ $item['menu_id'] }}')" />
                                    <span class="min-w-8 text-center text-lg font-semibold">{{ $item['qty'] }}</span>
                                    <x-button variant="outline" size="sm" shape="square" icon="ri-add-line"
                                        label="Tambah jumlah {{ $item['name'] }}"
                                        wire:click="incrementQty('{{ $item['menu_id'] }}')" />
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

                        <x-button variant="primary" :block="true" class="mt-4" icon="ri-shopping-cart-line"
                            wire:click="goToCart" loading="goToCart">
                            Lanjut ke Checkout
                        </x-button>
                    </div>
                </x-card>
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
                    <x-button variant="ghost" size="sm" shape="circle" icon="ri-close-line text-lg"
                        label="Tutup" wire:click="closeMenuDetail" />
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

                <x-button variant="primary" :block="true" icon="ri-add-line"
                    wire:click="quickAdd('{{ $selectedMenu['id'] }}')" x-on:click="show = false">
                    Tambah ke Keranjang
                </x-button>
            </div>
        @endif
    </x-modal>
</div>
