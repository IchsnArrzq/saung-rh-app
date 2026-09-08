@php
    $variant = $variant ?? 'public';
    $isCustomer = $variant === 'customer';
    $table = $table ?? null;
    $mode = $mode ?? 'online';
    $tableId = $tableId ?? null;
    $cartCount = $cartCount ?? null;
    $cartSubtotal = $cartSubtotal ?? null;

    // Hanya media yang benar-benar dimiliki menu ini. Sebelumnya galeri diisi foto
    // stok acak berdasarkan id menu, sehingga pelanggan melihat hidangan yang bukan
    // hidangan yang dipesan — itu foto karangan, bukan placeholder.
    $menuVideo = $menu->videos->first();
    $heroImage = $menu->display_image_url;

    $galleryImages = $menu->images->pluck('url')->filter()->values()->all();
    if ($heroImage && ! in_array($heroImage, $galleryImages, true)) {
        array_unshift($galleryImages, $heroImage);
    }

    $hasMedia = $galleryImages !== [] || $menuVideo !== null;

    $backUrl = $isCustomer
        ? route('customer.menus.index', ['table_id' => $table?->id])
        : route('public.menu', ['mode' => $mode, 'table_id' => $tableId]);
    $cartUrl = $isCustomer
        ? route('customer.menus.cart.index', ['table_id' => $table?->id])
        : route('public.cart.index');
    $cartAction = $isCustomer
        ? route('customer.menus.cart.store')
        : route('public.menu.cart.store', $menu);
    $currentUrl = request()->getRequestUri();
    $relatedUrl = function ($relatedMenu) use ($isCustomer, $table, $mode, $tableId): string {
        return $isCustomer
            ? route('customer.menus.show', ['menu' => $relatedMenu, 'table_id' => $table?->id])
            : route('public.menu.show', ['menu' => $relatedMenu, 'mode' => $mode, 'table_id' => $tableId]);
    };
@endphp

<div class="space-y-6">
    @if (session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif

    @if ($errors->any())
        <x-alert type="error" title="Periksa input berikut:">
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <section class="grid gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(360px,0.85fr)] lg:items-start">
        <div class="space-y-3">
            @if ($hasMedia)
                {{-- Mobile: geser mendatar --}}
                <div class="-mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-2 md:hidden">
                    @foreach ($galleryImages as $image)
                        <div class="w-[86vw] shrink-0 snap-center overflow-hidden rounded-xl border border-base-300 bg-base-200">
                            <img src="{{ $image }}" alt="{{ $menu->name }}" class="aspect-[4/3] h-full w-full object-cover">
                        </div>
                    @endforeach

                    @if ($menuVideo)
                        <div class="w-[86vw] shrink-0 snap-center overflow-hidden rounded-xl border border-base-300 bg-base-300">
                            <video src="{{ $menuVideo->url }}" controls class="aspect-[4/3] h-full w-full object-cover"></video>
                        </div>
                    @endif
                </div>

                {{-- Desktop: satu foto utama + sisanya di kolom kanan --}}
                <div class="hidden grid-cols-[1.4fr_0.8fr] gap-3 md:grid">
                    <div class="overflow-hidden rounded-xl border border-base-300 bg-base-200">
                        @if ($galleryImages)
                            <img src="{{ $galleryImages[0] }}" alt="{{ $menu->name }}" class="aspect-[4/3] h-full w-full object-cover">
                        @else
                            <video src="{{ $menuVideo->url }}" controls class="aspect-[4/3] h-full w-full object-cover"></video>
                        @endif
                    </div>

                    <div class="grid gap-3">
                        @foreach (array_slice($galleryImages, 1, 2) as $image)
                            <div class="overflow-hidden rounded-xl border border-base-300 bg-base-200">
                                <img src="{{ $image }}" alt="{{ $menu->name }}" class="aspect-[4/3] h-full w-full object-cover">
                            </div>
                        @endforeach

                        @if ($menuVideo && $galleryImages)
                            <div class="overflow-hidden rounded-xl border border-base-300 bg-base-300">
                                <video src="{{ $menuVideo->url }}" controls class="aspect-[4/3] h-full w-full object-cover"></video>
                            </div>
                        @endif
                    </div>
                </div>

                @if (count($galleryImages) > 1)
                    <div class="hidden gap-2 md:flex">
                        @foreach ($galleryImages as $image)
                            <div class="h-16 w-20 overflow-hidden rounded-xl border border-base-300 bg-base-200">
                                <img src="{{ $image }}" alt="{{ $menu->name }}" class="h-full w-full object-cover">
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="flex aspect-[4/3] w-full flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-base-300 bg-base-200 text-base-content/50">
                    <i class="ri-image-line text-4xl" aria-hidden="true"></i>
                    <p class="text-sm">Belum ada foto untuk menu ini</p>
                </div>
            @endif
        </div>

        <aside class="lg:sticky lg:top-6">
            <x-card>
                <div class="flex flex-wrap items-center gap-2">
                    <x-badge color="neutral" outline>{{ $menu->category->name ?? 'Menu' }}</x-badge>
                    <x-badge :color="$menu->is_available ? 'success' : 'error'">
                        {{ $menu->is_available ? 'Tersedia' : 'Habis' }}
                    </x-badge>
                    @if ($menu->sku)
                        <x-badge color="ghost">SKU {{ $menu->sku }}</x-badge>
                    @endif
                </div>

                <h1 class="mt-4 text-3xl font-extrabold leading-tight md:text-4xl">{{ $menu->name }}</h1>
                <p class="mt-3 text-2xl font-extrabold tabular-nums text-primary">
                    Rp {{ number_format((float) $menu->price, 0, ',', '.') }}
                </p>

                @if ($cartCount !== null)
                    <a href="{{ $cartUrl }}" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-primary hover:underline">
                        <i class="ri-shopping-bag-3-line" aria-hidden="true"></i>
                        <span class="tabular-nums">Keranjang {{ $cartCount }} item</span>
                        @if ($cartSubtotal)
                            <span class="tabular-nums">Rp {{ number_format((float) $cartSubtotal, 0, ',', '.') }}</span>
                        @endif
                    </a>
                @endif

                @if ($menu->description)
                    <div class="mt-5 border-t border-base-300 pt-5">
                        <p class="text-sm leading-7 text-secondary">{{ $menu->description }}</p>
                    </div>
                @endif

                {{-- Blok "Rasa" dan "Estimasi 10-15 menit" dihapus: keduanya teks tetap yang
                     sama untuk semua menu, tidak ada kolomnya di database, dan estimasi waktu
                     saji adalah janji yang tidak bisa ditepati sistem (CLAUDE.md § B). --}}

                <form action="{{ $cartAction }}" method="POST" class="mt-5 space-y-3"
                    data-confirm="Tambahkan {{ $menu->name }} ke keranjang?"
                    data-confirm-title="Konfirmasi keranjang"
                    data-confirm-yes="Ya, tambahkan"
                    data-confirm-no="Batal">
                    @csrf
                    @if ($isCustomer)
                        <input type="hidden" name="table_id" value="{{ $table?->id }}">
                        <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                    @endif
                    <input type="hidden" name="redirect_to" value="{{ $currentUrl }}">

                    <div class="grid gap-3 sm:grid-cols-[110px_1fr]">
                        <x-input label="Jumlah" name="qty" type="number" min="1" max="20" value="1" :required="true"
                            inputmode="numeric" />

                        <x-input label="Catatan" name="notes" placeholder="contoh: ekstra pedas" />
                    </div>

                    <x-button type="submit" variant="primary" :block="true" icon="ri-shopping-cart-2-line"
                        :disabled="! $menu->is_available">
                        {{ $menu->is_available ? 'Tambah ke keranjang' : 'Sedang habis' }}
                    </x-button>
                </form>
            </x-card>
        </aside>
    </section>

    <section>
        <x-card>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">Menu terkait</h2>
                    <p class="mt-1 text-sm text-secondary">Pilihan lain dari kategori yang sama.</p>
                </div>
                <x-button variant="ghost" size="sm" :href="$backUrl">Kembali ke menu</x-button>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($relatedMenus as $relatedMenu)
                    <a href="{{ $relatedUrl($relatedMenu) }}"
                        class="group flex gap-3 rounded-xl border border-base-300 bg-base-100 p-3 transition hover:border-primary hover:bg-base-200">
                        @if ($relatedMenu->image_url)
                            <img src="{{ $relatedMenu->image_url }}" alt="{{ $relatedMenu->name }}"
                                class="h-20 w-24 shrink-0 rounded-lg object-cover">
                        @else
                            <span class="flex h-20 w-24 shrink-0 items-center justify-center rounded-lg bg-base-200 text-base-content/40">
                                <i class="ri-image-line text-2xl" aria-hidden="true"></i>
                            </span>
                        @endif
                        <div class="min-w-0">
                            <p class="font-semibold group-hover:text-primary">{{ $relatedMenu->name }}</p>
                            <p class="mt-1 text-xs text-secondary">{{ $relatedMenu->category->name ?? 'Menu' }}</p>
                            <p class="mt-2 text-sm font-bold tabular-nums text-primary">
                                Rp {{ number_format((float) $relatedMenu->price, 0, ',', '.') }}
                            </p>
                        </div>
                    </a>
                @empty
                    <p class="rounded-xl border border-dashed border-base-300 p-4 text-sm text-secondary">
                        Belum ada menu lain di kategori ini.
                    </p>
                @endforelse
            </div>
        </x-card>
    </section>
</div>
