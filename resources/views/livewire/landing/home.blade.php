<div>
    @if (session('success'))
        <div class="mb-6 flex items-center justify-between rounded-xl border border-success/20 bg-success/10 px-4 py-3 text-sm font-medium text-success shadow-sm">
            <div class="flex items-center gap-2">
                <i class="ri-checkbox-circle-fill text-lg"></i>
                {{ session('success') }}
            </div>
            <a href="{{ route('public.cart.index') }}" class="font-bold underline hover:opacity-80">Lihat Keranjang</a>
        </div>
    @endif

    @error('cart')
        <div class="mb-6 flex items-center gap-2 rounded-xl border border-error/20 bg-error/10 px-4 py-3 text-sm text-error shadow-sm">
            <i class="ri-error-warning-fill text-lg"></i>
            {{ $message }}
        </div>
    @enderror

    <section class="relative overflow-hidden rounded-[2rem] bg-primary/5 border border-primary/20 mb-16">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+PHBhdGggZD0iTTQwIDBoLTQwdjQwaDQwVjB6IiBmaWxsPSJub25lIi8+PHBhdGggZD0iTTAgMGg0MHY0MGgtNDB6IiBmaWxsPSJub25lIiBzdHJva2U9IiNmZjRmNTUiIHN0cm9rZS1vcGFjaXR5PSIwLjA1IiBzdHJva2Utd2lkdGg9IjEiLz48L3N2Zz4=')] opacity-60"></div>
        
        <div class="relative z-10 mx-auto max-w-7xl px-6 py-16 sm:py-24 lg:px-12 flex flex-col lg:flex-row items-center gap-12">
            
            <div class="w-full lg:w-1/2 text-center lg:text-left">
                <h1 class="text-4xl font-extrabold tracking-tight text-base-content sm:text-5xl lg:text-6xl">
                    SAUNG<span class="text-primary">RH.</span><br>
                    <span class="text-3xl sm:text-4xl font-bold text-base-content/80 mt-3 block leading-tight">Cita Rasa Autentik <br>& Hangat.</span>
                </h1>
                <p class="mx-auto lg:mx-0 mt-6 max-w-xl text-lg text-secondary">
                    Nikmati hidangan khas kami dengan kemudahan memesan langsung dari meja menggunakan QR code, atau lakukan reservasi tempat sebelum kedatangan.
                </p>
                <div class="mt-10 flex flex-wrap justify-center lg:justify-start gap-4">
                    <a href="{{ route('public.menu') }}" class="btn rounded-lg bg-primary border-none text-primary-content hover:brightness-90 px-8 py-3 shadow-lg shadow-primary/30 transition-all font-semibold">
                        Lihat Menu
                    </a>
                    @if (Route::has('customer.bookings.create'))
                        <a href="{{ route('customer.bookings.create') }}" class="btn rounded-lg bg-base-100 border border-base-300 text-base-content hover:bg-base-200 px-8 py-3 font-semibold transition-all shadow-sm">
                            Pesan Meja
                        </a>
                    @endif
                </div>
            </div>

            <div class="w-full lg:w-1/2">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl border-4 border-base-100 aspect-[4/3] bg-base-200 group">
                    <img src="{{ asset('assets/media/stock/900x600/12.jpg') }}" alt="Suasana Restoran" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-tr from-primary/10 to-transparent"></div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-20">
        <div class="grid gap-6 md:grid-cols-3">
            <div class="rounded-2xl border border-base-300 bg-base-100 p-6 shadow-sm transition-shadow hover:shadow-md">
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary/10">
                        <i class="ri-qr-code-line text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-sm font-bold text-base-content uppercase tracking-wide">Scan & Dine-In</h3>
                </div>
                <p class="text-sm text-secondary leading-relaxed">Pindai QR code di meja untuk melihat daftar menu dan memesan makanan langsung tanpa harus menunggu lama.</p>
            </div>
            
            <div class="rounded-2xl border border-base-300 bg-base-100 p-6 shadow-sm transition-shadow hover:shadow-md">
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary/10">
                        <i class="ri-calendar-check-line text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-sm font-bold text-base-content uppercase tracking-wide">Reservasi Tempat</h3>
                </div>
                <p class="text-sm text-secondary leading-relaxed">Pesan meja pilihan Anda secara online untuk memastikan tempat tersedia saat Anda datang bersama keluarga.</p>
            </div>
            
            <div class="rounded-2xl border border-base-300 bg-base-100 p-6 shadow-sm transition-shadow hover:shadow-md">
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary/10">
                        <i class="ri-shopping-bag-3-line text-2xl text-primary"></i>
                    </div>
                    <h3 class="text-sm font-bold text-base-content uppercase tracking-wide">Pre-Order Menu</h3>
                </div>
                <p class="text-sm text-secondary leading-relaxed">Pilih dan pesan menu favorit dari rumah, lalu ambil atau nikmati langsung saat Anda tiba di lokasi.</p>
            </div>
        </div>
    </section>

    <section class="mb-20">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-extrabold text-base-content">Rekomendasi Menu</h2>
            <p class="mt-3 text-secondary">Beberapa hidangan pilihan khas dari dapur kami.</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($menus as $menu)
                <article class="flex flex-col overflow-hidden rounded-2xl border border-base-300 bg-base-100 p-2 transition-all hover:shadow-xl hover:border-success/50 hover:-translate-y-1">
                    <div class="aspect-[4/3] w-full overflow-hidden rounded-xl bg-base-200 relative group">
                        <img src="{{ $menu->image_url ?: asset('assets/media/stock/900x600/12.jpg') }}"
                            alt="{{ $menu->name }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                        
                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 backdrop-blur-[2px]">
                            <a href="{{ route('public.menu.show', $menu) }}" class="btn btn-sm rounded-lg bg-base-100 border-none text-base-content hover:bg-base-200 shadow-lg font-medium">
                                <i class="ri-eye-line"></i> Detail
                            </a>
                            <button type="button" wire:click="quickAdd('{{ $menu->id }}')" class="btn btn-sm rounded-lg bg-success border-none text-success-content hover:brightness-90 shadow-lg font-medium">
                                <i class="ri-shopping-cart-2-line"></i> Tambah
                            </button>
                        </div>
                    </div>

                    <div class="p-4 flex flex-col flex-1">
                        <div class="flex items-center gap-1 mb-1">
                            <i class="ri-restaurant-fill text-success text-xs"></i>
                            <p class="text-xs font-bold uppercase tracking-wider text-success">{{ $menu->category->name ?? 'Menu' }}</p>
                        </div>
                        <h3 class="text-lg font-bold text-base-content leading-tight">{{ $menu->name }}</h3>
                        <p class="mt-2 text-sm text-secondary line-clamp-2 flex-1">{{ $menu->description ?: 'Hidangan khas yang disiapkan segar dengan bahan pilihan.' }}</p>
                        
                        <div class="mt-5 pt-4 border-t border-base-200 flex items-center justify-between gap-2">
                            <p class="text-lg font-extrabold text-success">Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</p>
                            <a href="{{ route('public.menu.show', $menu) }}" class="btn btn-xs btn-ghost">
                                Detail
                            </a>
                            <button type="button" wire:click="quickAdd('{{ $menu->id }}')" class="flex h-8 w-8 items-center justify-center rounded-full bg-success/10 text-success hover:bg-success hover:text-success-content transition-colors lg:hidden">
                                <i class="ri-add-line text-lg font-bold"></i>
                            </button>
                        </div>
                    </div>
                </article>
            @empty
                <p class="col-span-full rounded-2xl border-2 border-dashed border-base-300 bg-base-200 p-10 text-center text-secondary">
                    Menu belum tersedia saat ini.
                </p>
            @endforelse
        </div>
        
        <div class="mt-12 text-center">
            <a href="{{ route('public.menu') }}" class="inline-flex items-center justify-center rounded-lg border-2 border-base-300 px-8 py-3 font-semibold text-base-content transition-colors hover:border-primary hover:bg-primary/10 hover:text-primary">
                Lihat Semua Menu <i class="ri-arrow-right-line ml-2"></i>
            </a>
        </div>
    </section>
</div>
