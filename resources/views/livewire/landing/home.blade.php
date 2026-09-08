<div>
    @if (session('success'))
        <x-alert type="success" class="mb-6">
            <div class="flex w-full flex-wrap items-center justify-between gap-3">
                <span>{{ session('success') }}</span>
                <a href="{{ route('public.cart.index') }}" wire:navigate class="font-semibold underline">Lihat keranjang</a>
            </div>
        </x-alert>
    @endif

    @error('cart')
        <x-alert type="error" class="mb-6">{{ $message }}</x-alert>
    @enderror

    <section class="relative mb-16 overflow-hidden rounded-xl border border-primary/20 bg-primary/5">
        <div class="relative z-10 mx-auto flex max-w-7xl flex-col items-center gap-12 px-6 py-16 sm:py-24 lg:flex-row lg:px-12">

            <div class="w-full text-center lg:w-1/2 lg:text-left">
                <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                    <span class="uppercase">{{ $business->name() }}</span><span class="text-primary">.</span><br>
                    <span class="mt-3 block text-3xl font-bold leading-tight text-base-content/80 sm:text-4xl">
                        {{ $business->tagline() }}
                    </span>
                </h1>
                <p class="mx-auto mt-6 max-w-xl text-lg text-secondary lg:mx-0">
                    Pesan langsung dari meja dengan memindai QR, atau pesan meja lebih dulu sebelum datang.
                </p>
                <div class="mt-10 flex flex-wrap justify-center gap-4 lg:justify-start">
                    <x-button variant="primary" size="lg" :href="route('public.menu')" wire:navigate>
                        Lihat menu
                    </x-button>
                    @if (Route::has('customer.bookings.create'))
                        <x-button variant="outline" size="lg" :href="route('customer.bookings.create')" wire:navigate>
                            Pesan meja
                        </x-button>
                    @endif
                </div>
            </div>

            <div class="w-full lg:w-1/2">
                <div class="relative aspect-[4/3] overflow-hidden rounded-xl bg-base-200">
                    <img src="{{ asset('assets/media/stock/900x600/12.jpg') }}" alt="Suasana restoran"
                        class="h-full w-full object-cover">
                </div>
            </div>
        </div>
    </section>

    <section class="mb-20">
        <div class="grid gap-6 md:grid-cols-3">
            @foreach ([
                ['ri-qr-code-line', 'Pesan lewat QR di meja', 'Pindai QR di meja untuk melihat daftar menu dan mengirim pesanan tanpa menunggu.'],
                ['ri-calendar-check-line', 'Reservasi tempat', 'Pesan meja lebih dulu supaya tempatnya sudah siap saat kamu datang.'],
                ['ri-shopping-bag-3-line', 'Pesan lebih awal', 'Pilih menu dari rumah, lalu nikmati begitu tiba di lokasi.'],
            ] as [$icon, $title, $description])
                <x-card padding="lg">
                    <div class="mb-4 flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10">
                            <i class="{{ $icon }} text-2xl text-primary" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-semibold">{{ $title }}</h3>
                    </div>
                    <p class="text-sm leading-relaxed text-secondary">{{ $description }}</p>
                </x-card>
            @endforeach
        </div>
    </section>

    <section class="mb-20">
        {{-- Bukan "Rekomendasi": daftar ini hanya menu tersedia pertama menurut abjad
             (MenuRepository::featured), tidak ada kolom unggulan atau data terlaris. --}}
        <div class="mb-12 text-center">
            <h2 class="text-3xl font-extrabold">Menu kami</h2>
            <p class="mt-3 text-secondary">Beberapa menu yang tersedia hari ini.</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($menus as $menu)
                <article class="flex flex-col overflow-hidden rounded-xl border border-base-300 bg-base-100 p-2">
                    <div class="aspect-[4/3] w-full overflow-hidden rounded-xl bg-base-200">
                        <img src="{{ $menu->image_url ?: asset('assets/media/stock/900x600/12.jpg') }}"
                            alt="{{ $menu->name }}" class="h-full w-full object-cover">
                    </div>

                    <div class="flex flex-1 flex-col p-4">
                        <p class="text-xs text-base-content/60">{{ $menu->category->name ?? 'Menu' }}</p>
                        <h3 class="mt-1 text-lg font-semibold leading-tight">{{ $menu->name }}</h3>

                        @if ($menu->description)
                            <p class="mt-2 line-clamp-2 text-sm text-secondary">{{ $menu->description }}</p>
                        @endif

                        {{-- mt-auto: harga dan aksi tetap sejajar walau judulnya dua baris. --}}
                        <div class="mt-auto flex items-center justify-between gap-2 border-t border-base-300 pt-4">
                            <p class="text-lg font-bold tabular-nums">Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</p>
                            <div class="flex items-center gap-1">
                                <x-button variant="ghost" size="sm" :href="route('public.menu.show', $menu)" wire:navigate>
                                    Detail
                                </x-button>
                                <x-button variant="accent" size="sm" shape="square" icon="ri-add-line"
                                    label="Tambah {{ $menu->name }} ke keranjang"
                                    wire:click="quickAdd('{{ $menu->id }}')" loading="quickAdd" />
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full">
                    <x-empty-state icon="ri-restaurant-line" title="Menu belum tersedia"
                        description="Menu yang ditandai tersedia akan muncul di sini." />
                </div>
            @endforelse
        </div>

        <div class="mt-12 text-center">
            <x-button variant="outline" size="lg" iconRight="ri-arrow-right-line" :href="route('public.menu')"
                wire:navigate>
                Lihat semua menu
            </x-button>
        </div>
    </section>
</div>
