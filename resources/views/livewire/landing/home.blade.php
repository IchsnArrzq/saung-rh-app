<div>
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
            <a href="{{ route('public.cart.index') }}" class="ml-2 font-bold underline">Lihat Cart</a>
        </div>
    @endif

    @error('cart')
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ $message }}
        </div>
    @enderror

    <section class="relative overflow-hidden rounded-3xl bg-stone-900 mb-16">
        <div class="absolute inset-0">
            <img src="{{ asset('assets/media/stock/900x600/12.jpg') }}" alt="Suasana Restoran" class="w-full h-full object-cover opacity-40">
            <div class="absolute inset-0 bg-gradient-to-t from-stone-900/90 via-stone-900/30 to-transparent"></div>
        </div>
        
        <div class="relative z-10 mx-auto max-w-4xl px-4 py-24 text-center sm:py-32 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl" style="font-family: 'Playfair Display', serif;">
                SaungRH<span class="text-orange-500">.</span><br>
                <span class="text-3xl sm:text-4xl font-medium text-stone-300">Cita Rasa Autentik & Hangat.</span>
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-stone-300">
                Nikmati hidangan lezat yang disiapkan dengan bahan berkualitas. Pesan langsung dari meja Anda via QR atau reservasi tempat untuk momen spesial.
            </p>
            <div class="mt-10 flex flex-wrap justify-center gap-4">
                <a href="{{ route('public.menu') }}" class="btn rounded-full bg-emerald-800 border-none text-amber-50 hover:bg-emerald-700 px-8">
                    Lihat Katalog Menu
                </a>
                @if (Route::has('customer.bookings.create'))
                    <a href="{{ route('customer.bookings.create') }}" class="btn rounded-full bg-white/10 border border-white/30 text-white hover:bg-white/20 backdrop-blur-sm px-8">
                        Reservasi Meja
                    </a>
                @endif
            </div>
        </div>
    </section>

    <section class="mb-20">
        <div class="text-center mb-10">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">Rekomendasi</p>
            <h2 class="text-3xl font-semibold text-stone-900 mt-2" style="font-family: 'Playfair Display', serif;">Menu Kami</h2>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($menus as $menu)
                <article class="overflow-hidden rounded-3xl border border-stone-200 bg-white transition hover:shadow-lg hover:-translate-y-1 flex flex-col">
                    <div class="aspect-[4/3] w-full bg-stone-100 relative group">
                        <img src="{{ $menu->image_url ?: asset('assets/media/stock/900x600/12.jpg') }}"
                            alt="{{ $menu->name }}" class="h-full w-full object-cover">
                        
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                            <button type="button" wire:click="quickAdd('{{ $menu->id }}')" class="btn btn-sm rounded-full bg-amber-300 border-none text-stone-900 hover:bg-amber-400">
                                <i class="ri-shopping-cart-2-line"></i> Tambah
                            </button>
                        </div>
                    </div>

                    <div class="p-5 flex flex-col flex-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-stone-500">{{ $menu->category->name ?? 'Menu' }}</p>
                        <h3 class="mt-1 text-lg font-semibold text-stone-900">{{ $menu->name }}</h3>
                        <p class="mt-2 text-sm text-stone-600 line-clamp-2 flex-1">{{ $menu->description ?: 'Hidangan lezat spesial dari dapur kami.' }}</p>
                        <div class="mt-4 flex items-center justify-between">
                            <p class="text-lg font-bold text-emerald-800">Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</p>
                            <button type="button" wire:click="quickAdd('{{ $menu->id }}')" class="btn btn-circle btn-sm bg-stone-100 text-stone-600 hover:bg-emerald-800 hover:text-white border-none lg:hidden">
                                <i class="ri-add-line"></i>
                            </button>
                        </div>
                    </div>
                </article>
            @empty
                <p class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-stone-50 p-8 text-center text-stone-500">
                    Menu unggulan belum tersedia.
                </p>
            @endforelse
        </div>
        
        <div class="mt-10 text-center">
            <a href="{{ route('public.menu') }}" class="btn btn-outline rounded-full border-stone-300 text-stone-700 hover:bg-stone-100 hover:border-stone-400 px-8">
                Jelajahi Seluruh Menu
            </a>
        </div>
    </section>

    <section class="mb-10 rounded-3xl bg-emerald-900 px-6 py-16 text-center text-emerald-50 md:px-12 lg:py-20">
        <h2 class="text-3xl font-semibold text-white mb-12" style="font-family: 'Playfair Display', serif;">Layanan Kami</h2>
        
        <div class="grid gap-8 md:grid-cols-3">
            <div class="flex flex-col items-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-800 shadow-inner mb-4">
                    <i class="ri-qr-code-line text-3xl text-amber-300"></i>
                </div>
                <h3 class="text-lg font-semibold text-white">Scan & Dine-In</h3>
                <p class="mt-2 text-sm text-emerald-100/80 max-w-xs">Scan QR Code di meja Anda, pilih menu, dan pesanan akan langsung diproses ke dapur.</p>
            </div>
            
            <div class="flex flex-col items-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-800 shadow-inner mb-4">
                    <i class="ri-calendar-check-line text-3xl text-amber-300"></i>
                </div>
                <h3 class="text-lg font-semibold text-white">Reservasi Instan</h3>
                <p class="mt-2 text-sm text-emerald-100/80 max-w-xs">Pilih meja favorit Anda dan jadwalkan kedatangan secara online tanpa perlu menelepon.</p>
            </div>
            
            <div class="flex flex-col items-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-800 shadow-inner mb-4">
                    <i class="ri-shopping-bag-3-line text-3xl text-amber-300"></i>
                </div>
                <h3 class="text-lg font-semibold text-white">Pre-Order Cepat</h3>
                <p class="mt-2 text-sm text-emerald-100/80 max-w-xs">Pesan dari rumah, tambahkan ke keranjang, dan ambil hidangan saat Anda tiba di lokasi.</p>
            </div>
        </div>
    </section>
</div>
