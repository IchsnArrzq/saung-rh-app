<div>
    @php
        $isOffline = $mode === 'offline';
    @endphp

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @error('cart')
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ $message }}
        </div>
    @enderror

    <section class="rounded-3xl border border-stone-200 bg-white p-5 md:p-6">
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-stone-900">Menu Makanan & Minuman</h1>
                <p class="mt-1 text-sm text-stone-600">
                    {{ $isOffline ? 'Mode Offline QR: pilih menu lalu kirim pesanan langsung ke dapur.' : 'Mode Online Booking: pilih menu, lanjut ke cart, lalu booking meja.' }}
                </p>
            </div>

            <div class="ml-auto flex items-center gap-2">
                <button type="button" wire:click="setMode('online')"
                    class="btn btn-sm {{ ! $isOffline ? 'bg-emerald-800 text-amber-50 hover:bg-emerald-700' : 'btn-ghost' }}">
                    Online
                </button>
                <button type="button" wire:click="setMode('offline')"
                    class="btn btn-sm {{ $isOffline ? 'bg-emerald-800 text-amber-50 hover:bg-emerald-700' : 'btn-ghost' }}">
                    Offline QR
                </button>
            </div>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-[1fr_auto] md:items-center">
            <label class="form-control">
                <span class="label-text">Cari Menu</span>
                <input type="text" wire:model.live.debounce.300ms="search" class="input input-bordered" placeholder="Cari makanan atau minuman...">
            </label>

            @if ($isOffline && $selectedTable)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-stone-700">
                    <p class="font-semibold">Meja Aktif: {{ $selectedTable->code }}</p>
                    <p>Kapasitas {{ $selectedTable->capacity }} orang</p>
                </div>
            @endif
        </div>
    </section>

    <div class="mt-6 grid gap-5 {{ $cartCount > 0 ? 'xl:grid-cols-[minmax(0,1fr)_380px]' : '' }} xl:items-start">
        <div class="min-w-0">
            @if ($detailMenu)
                <section class="mb-5 rounded-3xl border border-emerald-200 bg-emerald-50 p-5">
                    <div class="grid gap-4 md:grid-cols-[180px_1fr]">
                        <img src="{{ $detailMenu->image_url ?: 'https://picsum.photos/seed/'.urlencode((string) $detailMenu->id).'/700/500' }}"
                            alt="{{ $detailMenu->name }}" class="h-40 w-full rounded-xl object-cover">

                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-stone-500">{{ $detailMenu->category->name ?? 'Menu' }}</p>
                                    <h2 class="mt-1 text-xl font-semibold text-stone-900">{{ $detailMenu->name }}</h2>
                                </div>
                                <button type="button" wire:click="closeDetail" class="btn btn-sm btn-ghost">Tutup</button>
                            </div>

                            <p class="mt-2 text-sm text-stone-600">{{ $detailMenu->description ?: 'Deskripsi menu belum tersedia.' }}</p>
                            <p class="mt-2 text-lg font-bold text-emerald-800">Rp {{ number_format((float) $detailMenu->price, 0, ',', '.') }}</p>

                            <div class="mt-3 grid gap-3 md:grid-cols-2">
                                <label class="form-control">
                                    <span class="label-text">Jumlah</span>
                                    <input type="number" wire:model="detailQty" min="1" max="20" class="input input-bordered">
                                </label>

                                <label class="form-control md:col-span-2">
                                    <span class="label-text">Catatan</span>
                                    <textarea wire:model="detailNotes" rows="2" class="textarea textarea-bordered"
                                        placeholder="contoh: kurang gula / ekstra pedas"></textarea>
                                </label>
                            </div>

                            <button type="button" wire:click="addDetailToCart"
                                data-confirm="Tambahkan {{ $detailMenu->name }} ke cart?"
                                data-confirm-title="Konfirmasi Cart"
                                data-confirm-yes="Ya, Tambahkan"
                                data-confirm-no="Batal"
                                class="mt-3 btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                                Tambah ke Cart
                            </button>
                        </div>
                    </div>
                </section>
            @endif

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 {{ $cartCount > 0 ? '' : '2xl:grid-cols-4' }}">
                @forelse ($menus as $menu)
                    <article class="overflow-hidden rounded-3xl border border-stone-200 bg-white">
                        <div class="aspect-[4/3] w-full bg-stone-100">
                            <img src="{{ $menu->image_url ?: 'https://picsum.photos/seed/'.urlencode((string) $menu->id).'/800/600' }}"
                                alt="{{ $menu->name }}" class="h-full w-full object-cover">
                        </div>

                        <div class="p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-stone-500">{{ $menu->category->name ?? 'Menu' }}</p>
                            <h2 class="mt-1 text-lg font-semibold text-stone-900">{{ $menu->name }}</h2>
                            <p class="mt-1 text-sm text-stone-600">{{ \Illuminate\Support\Str::limit($menu->description ?: 'Menu favorit restoran.', 80) }}</p>
                            <p class="mt-3 text-lg font-bold text-emerald-800">Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</p>

                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <a href="{{ route('public.menu.show', ['menu' => $menu, 'mode' => $mode, 'table_id' => $tableId]) }}"
                                    class="btn btn-sm btn-outline">
                                    Detail
                                </a>
                                <button type="button" wire:click="showDetail('{{ $menu->id }}')" class="btn btn-sm btn-ghost">
                                    Quick View
                                </button>
                                <button type="button" wire:click="quickAdd('{{ $menu->id }}')"
                                    data-confirm="Tambahkan {{ $menu->name }} ke cart?"
                                    data-confirm-title="Konfirmasi Cart"
                                    data-confirm-yes="Ya, Tambahkan"
                                    data-confirm-no="Batal"
                                    class="ml-auto btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                                    Tambah
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-white p-5 text-center text-sm text-stone-500">
                        Menu belum tersedia.
                    </p>
                @endforelse
            </section>
        </div>

        @if ($cartCount > 0)
        <aside class="xl:sticky xl:top-5">
            <section class="rounded-3xl border border-stone-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-stone-900">Cart Pesanan</h2>
                        <p class="mt-1 text-sm text-stone-500">
                            {{ $isOffline ? 'Order dari QR meja.' : 'Lanjutkan untuk booking meja.' }}
                        </p>
                    </div>
                    @if ($cartCount > 0)
                        <button type="button" wire:click="clearCart" data-confirm="Kosongkan semua item cart?"
                            class="btn btn-sm btn-ghost text-rose-600">
                            Reset
                        </button>
                    @endif
                </div>

                @if ($isOffline && $selectedTable)
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-stone-700">
                        <p class="font-semibold">Meja {{ $selectedTable->code }}</p>
                        <p>Kapasitas {{ $selectedTable->capacity }} orang</p>
                    </div>
                @endif

                <div class="mt-4 space-y-3">
                    @foreach ($cartItems as $item)
                        <article class="rounded-2xl border border-stone-200 p-3">
                            <div class="flex items-start gap-3">
                                <img src="{{ $item['image_url'] ?: 'https://picsum.photos/seed/'.urlencode((string) $item['menu_id']).'/200/160' }}"
                                    alt="{{ $item['name'] }}" class="h-16 w-20 rounded-xl object-cover">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('public.menu.show', ['menu' => $item['menu_id'], 'mode' => $mode, 'table_id' => $tableId]) }}"
                                        class="line-clamp-1 font-semibold text-stone-900 hover:text-emerald-800 hover:underline">
                                        {{ $item['name'] }}
                                    </a>
                                    <p class="mt-1 text-sm text-stone-500">Rp {{ number_format((float) $item['price'], 0, ',', '.') }}</p>
                                    @if (! empty($item['notes']))
                                        <p class="mt-1 line-clamp-2 text-xs text-stone-500">Catatan: {{ $item['notes'] }}</p>
                                    @endif
                                </div>
                                <button type="button" wire:click="removeItem('{{ $item['menu_id'] }}')"
                                    data-confirm="Hapus item ini dari cart?"
                                    class="btn btn-xs btn-ghost text-rose-600">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>

                            <div class="mt-3 flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-stone-800">
                                    Rp {{ number_format(((float) $item['price']) * ((int) $item['qty']), 0, ',', '.') }}
                                </p>
                                <div class="join">
                                    <button type="button" wire:click="decrementQty('{{ $item['menu_id'] }}')"
                                        class="join-item btn btn-sm btn-outline">-</button>
                                    <span class="join-item flex h-8 min-w-10 items-center justify-center border-y border-stone-300 bg-white px-3 text-sm font-semibold">
                                        {{ $item['qty'] }}
                                    </span>
                                    <button type="button" wire:click="incrementQty('{{ $item['menu_id'] }}')"
                                        class="join-item btn btn-sm btn-outline">+</button>
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

                    <button type="button" wire:click="goToCart" @disabled($cartCount <= 0)
                        class="btn mt-4 w-full bg-emerald-800 text-amber-50 hover:bg-emerald-700 disabled:bg-stone-200 disabled:text-stone-500">
                        Lanjut ke Checkout
                    </button>
                </div>
            </section>
        </aside>
        @endif
    </div>
</div>
