@php
    $variant = $variant ?? 'public';
    $isCustomer = $variant === 'customer';
    $table = $table ?? null;
    $mode = $mode ?? 'online';
    $tableId = $tableId ?? null;
    $cartCount = $cartCount ?? null;
    $cartSubtotal = $cartSubtotal ?? null;
    $seedBase = abs(crc32((string) $menu->id));
    $stockImage = fn (int $offset): string => asset('assets/media/stock/900x600/'.(($seedBase + $offset) % 27 + 1).'.jpg');
    $heroImage = $menu->image_url ?: $stockImage(0);
    $galleryImages = [$heroImage, $stockImage(1), $stockImage(2)];
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
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-semibold">Periksa input berikut:</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="grid gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(360px,0.85fr)] lg:items-start">
        <div class="space-y-3">
            <div class="md:hidden -mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-2">
                @foreach ($galleryImages as $image)
                    <div class="w-[86vw] shrink-0 snap-center overflow-hidden rounded-2xl border border-base-300 bg-base-200">
                        <img src="{{ $image }}" alt="{{ $menu->name }}" class="aspect-[4/3] h-full w-full object-cover">
                    </div>
                @endforeach
                <div class="relative w-[86vw] shrink-0 snap-center overflow-hidden rounded-2xl border border-base-300 bg-base-200">
                    <img src="{{ $heroImage }}" alt="{{ $menu->name }} video preview" class="aspect-[4/3] h-full w-full object-cover brightness-75">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="flex h-14 w-14 items-center justify-center rounded-full bg-base-100/90 text-2xl text-primary shadow-lg">
                            <i class="ri-play-fill"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="hidden grid-cols-[1.4fr_0.8fr] gap-3 md:grid">
                <div class="overflow-hidden rounded-2xl border border-base-300 bg-base-200">
                    <img src="{{ $heroImage }}" alt="{{ $menu->name }}" class="aspect-[4/3] h-full w-full object-cover">
                </div>

                <div class="grid gap-3">
                    <div class="overflow-hidden rounded-2xl border border-base-300 bg-base-200">
                        <img src="{{ $galleryImages[1] }}" alt="{{ $menu->name }}" class="aspect-[4/3] h-full w-full object-cover">
                    </div>
                    <div class="relative overflow-hidden rounded-2xl border border-base-300 bg-base-200">
                        <img src="{{ $galleryImages[2] }}" alt="{{ $menu->name }}" class="aspect-[4/3] h-full w-full object-cover brightness-75">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-base-100/90 text-2xl text-primary shadow-lg">
                                <i class="ri-play-fill"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hidden gap-2 md:flex">
                @foreach ($galleryImages as $image)
                    <div class="h-16 w-20 overflow-hidden rounded-xl border border-base-300 bg-base-200">
                        <img src="{{ $image }}" alt="{{ $menu->name }}" class="h-full w-full object-cover">
                    </div>
                @endforeach
                <div class="flex h-16 w-20 items-center justify-center rounded-xl border border-base-300 bg-base-200 text-xl text-primary">
                    <i class="ri-play-fill"></i>
                </div>
            </div>
        </div>

        <aside class="lg:sticky lg:top-6">
            <div class="rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge badge-outline">{{ $menu->category->name ?? 'Menu' }}</span>
                    <span class="badge {{ $menu->is_available ? 'badge-success' : 'badge-error' }}">
                        {{ $menu->is_available ? 'Tersedia' : 'Habis' }}
                    </span>
                    @if ($menu->sku)
                        <span class="badge badge-ghost">SKU {{ $menu->sku }}</span>
                    @endif
                </div>

                <h1 class="mt-4 text-3xl font-extrabold leading-tight text-base-content md:text-4xl">{{ $menu->name }}</h1>
                <p class="mt-3 text-2xl font-extrabold text-primary">Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</p>

                @if ($cartCount !== null)
                    <a href="{{ $cartUrl }}" class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-primary hover:underline">
                        <i class="ri-shopping-bag-3-line"></i>
                        Cart {{ $cartCount }} item
                        @if ($cartSubtotal)
                            <span>Rp {{ number_format((float) $cartSubtotal, 0, ',', '.') }}</span>
                        @endif
                    </a>
                @endif

                <div class="mt-5 border-t border-base-300 pt-5">
                    <p class="text-sm leading-7 text-secondary">
                        {{ $menu->description ?: 'Hidangan khas restoran yang disiapkan segar saat dipesan dengan bahan pilihan dan rasa yang seimbang.' }}
                    </p>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-base-300 bg-base-200/60 p-3">
                        <p class="text-xs font-bold uppercase tracking-wide text-secondary">Rasa</p>
                        <p class="mt-1 text-sm font-semibold text-base-content">Gurih, segar, seimbang</p>
                    </div>
                    <div class="rounded-xl border border-base-300 bg-base-200/60 p-3">
                        <p class="text-xs font-bold uppercase tracking-wide text-secondary">Estimasi</p>
                        <p class="mt-1 text-sm font-semibold text-base-content">10-15 menit</p>
                    </div>
                </div>

                <form action="{{ $cartAction }}" method="POST" class="mt-5 space-y-3"
                    data-confirm="Tambahkan {{ $menu->name }} ke cart?"
                    data-confirm-title="Konfirmasi Cart"
                    data-confirm-yes="Ya, Tambahkan"
                    data-confirm-no="Batal">
                    @csrf
                    @if ($isCustomer)
                        <input type="hidden" name="table_id" value="{{ $table?->id }}">
                        <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                    @endif
                    <input type="hidden" name="redirect_to" value="{{ $currentUrl }}">

                    <div class="grid gap-3 sm:grid-cols-[110px_1fr]">
                        <label class="form-control">
                            <span class="label-text">Jumlah</span>
                            <input type="number" name="qty" min="1" max="20" value="1" class="input input-bordered" required>
                        </label>

                        <label class="form-control">
                            <span class="label-text">Catatan</span>
                            <input type="text" name="notes" class="input input-bordered" placeholder="contoh: ekstra pedas">
                        </label>
                    </div>

                    <button type="submit" class="btn w-full bg-emerald-800 text-amber-50 hover:bg-emerald-700" @disabled(! $menu->is_available)>
                        <i class="ri-shopping-cart-2-line"></i>
                        Tambah ke Cart
                    </button>
                </form>
            </div>
        </aside>
    </section>

    <section class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
        <div class="rounded-2xl border border-base-300 bg-base-100 p-5">
            <h2 class="text-lg font-bold text-base-content">Detail Hidangan</h2>
            <ul class="mt-4 space-y-3 text-sm text-secondary">
                <li class="flex gap-3">
                    <i class="ri-checkbox-circle-line mt-0.5 text-primary"></i>
                    <span>Disiapkan setelah pesanan masuk agar tekstur dan aroma tetap baik.</span>
                </li>
                <li class="flex gap-3">
                    <i class="ri-checkbox-circle-line mt-0.5 text-primary"></i>
                    <span>Bisa diberi catatan khusus untuk preferensi rasa atau bahan tertentu.</span>
                </li>
                <li class="flex gap-3">
                    <i class="ri-checkbox-circle-line mt-0.5 text-primary"></i>
                    <span>Cocok untuk dine-in dari QR meja maupun pemesanan online.</span>
                </li>
            </ul>
        </div>

        <div class="rounded-2xl border border-base-300 bg-base-100 p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-base-content">Menu Terkait</h2>
                    <p class="mt-1 text-sm text-secondary">Pilihan lain dari kategori yang sama.</p>
                </div>
                <a href="{{ $backUrl }}" class="btn btn-sm btn-ghost">Kembali ke Menu</a>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @forelse ($relatedMenus as $relatedMenu)
                    <a href="{{ $relatedUrl($relatedMenu) }}" class="group flex gap-3 rounded-xl border border-base-300 bg-base-100 p-3 transition hover:border-primary hover:bg-base-200">
                        <img src="{{ $relatedMenu->image_url ?: asset('assets/media/stock/900x600/'.((abs(crc32((string) $relatedMenu->id)) % 27) + 1).'.jpg') }}"
                            alt="{{ $relatedMenu->name }}" class="h-20 w-24 rounded-lg object-cover">
                        <div class="min-w-0">
                            <p class="font-semibold text-base-content group-hover:text-primary">{{ $relatedMenu->name }}</p>
                            <p class="mt-1 text-xs text-secondary">{{ $relatedMenu->category->name ?? 'Menu' }}</p>
                            <p class="mt-2 text-sm font-bold text-primary">Rp {{ number_format((float) $relatedMenu->price, 0, ',', '.') }}</p>
                        </div>
                    </a>
                @empty
                    <p class="rounded-xl border border-dashed border-base-300 p-4 text-sm text-secondary">
                        Belum ada menu terkait.
                    </p>
                @endforelse
            </div>
        </div>
    </section>
</div>
