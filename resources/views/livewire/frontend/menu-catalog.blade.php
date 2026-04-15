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
                <button type="button" wire:click="goToCart" class="btn btn-sm bg-amber-300 text-stone-900 hover:bg-amber-400">
                    Cart ({{ $cartCount }})
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

    @if ($detailMenu)
        <section class="mt-5 rounded-3xl border border-emerald-200 bg-emerald-50 p-5">
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
                        class="mt-3 btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                        Tambah ke Cart
                    </button>
                </div>
            </div>
        </section>
    @endif

    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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

                    <div class="mt-4 flex items-center gap-2">
                        <button type="button" wire:click="showDetail('{{ $menu->id }}')" class="btn btn-sm btn-ghost">
                            Show
                        </button>
                        <button type="button" wire:click="quickAdd('{{ $menu->id }}')"
                            class="ml-auto btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                            Tambah ke Cart
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
