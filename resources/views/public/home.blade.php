<x-guest-layout>
    <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <article class="relative overflow-hidden rounded-3xl bg-primary p-7 text-primary-content md:p-9">
            <p class="inline-flex rounded-full border border-primary-content/25 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]">
                Cafe & Resto
            </p>
            <h1 class="mt-5 max-w-xl text-4xl leading-tight md:text-5xl" style="font-family: 'Playfair Display', serif;">
                Crafted plates, calm room, and service that stays sharp.
            </h1>
            <p class="mt-4 max-w-lg text-sm text-primary-content/80">
                CR Cafe & Resto menyajikan hidangan dengan detail rasa yang presisi, ritme layanan cepat, dan suasana
                bersih yang tetap hangat.
            </p>

            <div class="mt-7 flex flex-wrap items-center gap-3">
                <a href="{{ route('public.menu.index') }}" class="btn btn-sm border-0 bg-base-100 text-base-content hover:bg-base-200">
                    Explore Menu
                </a>
                <a href="{{ route('public.cart.index') }}"
                    class="btn btn-sm btn-outline border-primary-content/40 text-primary-content hover:bg-primary-content/10">
                    Cart ({{ $cartCount }})
                </a>
            </div>

            <div class="mt-9 grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-primary-content/20 bg-primary-content/10 p-3">
                    <p class="text-xs uppercase tracking-[0.16em] text-primary-content/70">Kitchen</p>
                    <p class="mt-2 text-lg font-semibold">Open Daily</p>
                </div>
                <div class="rounded-2xl border border-primary-content/20 bg-primary-content/10 p-3">
                    <p class="text-xs uppercase tracking-[0.16em] text-primary-content/70">Focus</p>
                    <p class="mt-2 text-lg font-semibold">Signature Taste</p>
                </div>
                <div class="rounded-2xl border border-primary-content/20 bg-primary-content/10 p-3">
                    <p class="text-xs uppercase tracking-[0.16em] text-primary-content/70">Service</p>
                    <p class="mt-2 text-lg font-semibold">Fast Response</p>
                </div>
            </div>

            <img src="{{ asset('assets/logo-cr-mark.png') }}" alt="CR logo mark"
                class="pointer-events-none absolute -bottom-20 -right-12 h-72 w-72 rotate-6 opacity-10">
        </article>

        <article class="relative overflow-hidden rounded-3xl border border-base-300 bg-base-200">
            <img src="https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=1300&q=80"
                alt="Chef preparing menu" class="h-full min-h-[360px] w-full object-cover grayscale">
            <div class="absolute inset-0 bg-gradient-to-t from-neutral/90 via-neutral/45 to-transparent"></div>
            <div class="absolute inset-x-5 bottom-5 rounded-2xl border border-base-300/30 bg-neutral/70 p-4 text-neutral-content backdrop-blur">
                <p class="text-xs uppercase tracking-[0.2em] text-neutral-content/75">Open Kitchen</p>
                <p class="mt-2 text-xl font-semibold">Live cooking 10.00 - 20.00</p>
                <p class="mt-2 text-sm text-neutral-content/80">
                    Setiap plating dibuat langsung dari dapur terbuka dengan standar rasa yang konsisten.
                </p>
            </div>
        </article>
    </section>

    @if (session('success'))
        <div class="mt-6 rounded-2xl border border-base-300 bg-base-100 px-4 py-3 text-sm font-medium text-base-content">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->has('menu'))
        <div class="mt-6 rounded-2xl border border-error/30 bg-error/10 px-4 py-3 text-sm font-medium text-error">
            {{ $errors->first('menu') }}
        </div>
    @endif

    <section id="menu" class="mt-10">
        <div class="mb-6 flex flex-wrap items-end gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Featured Menu</p>
                <h2 class="mt-2 text-3xl text-base-content md:text-4xl" style="font-family: 'Playfair Display', serif;">
                    Menu Favorit Minggu Ini
                </h2>
            </div>

            <a href="{{ route('public.cart.index') }}" class="btn btn-sm btn-primary ml-auto">
                Open Cart ({{ $cartCount }})
            </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse ($menus as $menu)
                <article
                    class="group overflow-hidden rounded-3xl border border-base-300 bg-base-100 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="aspect-[4/3] w-full overflow-hidden bg-base-200">
                        <img src="{{ $menu->image_url ?: 'https://picsum.photos/seed/'.urlencode((string) $menu->id).'/800/600' }}"
                            alt="{{ $menu->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105 group-hover:grayscale-0 grayscale">
                    </div>

                    <div class="p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-secondary">
                            {{ $menu->category->name ?? 'Menu' }}
                        </p>
                        <h3 class="mt-1 text-lg font-semibold text-base-content">{{ $menu->name }}</h3>
                        <p class="mt-1 text-sm text-secondary">
                            {{ \Illuminate\Support\Str::limit($menu->description ?: 'Menu favorit restoran.', 82) }}
                        </p>
                        <p class="mt-3 text-lg font-bold text-primary">Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</p>

                        <form method="POST" action="{{ route('public.menu.cart.store', $menu) }}" class="mt-4">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary w-full">
                                Masukkan ke Cart
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="col-span-full rounded-2xl border border-dashed border-base-300 bg-base-100 p-5 text-center text-sm text-secondary">
                    Menu belum tersedia.
                </p>
            @endforelse
        </div>

        <div class="mt-6">
            <a href="{{ route('public.menu.index') }}" class="btn btn-sm btn-outline border-base-300 hover:border-secondary hover:bg-base-200">
                Lihat Semua Menu
            </a>
        </div>
    </section>

    <section id="highlight" class="mt-10 grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <article class="rounded-3xl border border-base-300 bg-base-100 p-6 md:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Highlights</p>
            <h2 class="mt-3 text-4xl leading-tight text-base-content md:text-5xl" style="font-family: 'Playfair Display', serif;">
                Refined comfort food with modern plating.
            </h2>
            <p class="mt-4 max-w-2xl text-sm text-secondary">
                Kami fokus pada keseimbangan rasa asin, manis, asam, dan umami agar tiap menu tetap memorable tanpa
                berlebihan.
            </p>

            <div class="mt-7 grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-base-300 bg-base-200 p-4">
                    <p class="text-xs uppercase tracking-[0.16em] text-secondary">Detail</p>
                    <p class="mt-2 text-lg font-semibold text-base-content">Fresh Prep Each Shift</p>
                </div>
                <div class="rounded-2xl border border-base-300 bg-base-200 p-4">
                    <p class="text-xs uppercase tracking-[0.16em] text-secondary">Service</p>
                    <p class="mt-2 text-lg font-semibold text-base-content">Fast Table Delivery</p>
                </div>
                <div class="rounded-2xl border border-base-300 bg-base-200 p-4">
                    <p class="text-xs uppercase tracking-[0.16em] text-secondary">Texture</p>
                    <p class="mt-2 text-lg font-semibold text-base-content">Crisp, Tender, Balanced</p>
                </div>
                <div class="rounded-2xl border border-base-300 bg-base-200 p-4">
                    <p class="text-xs uppercase tracking-[0.16em] text-secondary">Ambience</p>
                    <p class="mt-2 text-lg font-semibold text-base-content">Calm and Polished Room</p>
                </div>
            </div>
        </article>

        <article class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
            <div class="overflow-hidden rounded-3xl border border-base-300 bg-base-200">
                <img src="https://images.unsplash.com/photo-1518013431117-eb1465fa5752?auto=format&fit=crop&w=900&q=80"
                    alt="French fries" class="h-52 w-full object-cover grayscale">
            </div>
            <div class="overflow-hidden rounded-3xl border border-base-300 bg-base-100 p-4">
                <img src="{{ asset('assets/drink.png') }}" alt="Minuman segar" class="h-44 w-full object-contain grayscale">
            </div>
            <div class="overflow-hidden rounded-3xl border border-base-300 bg-base-200">
                <img src="https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?auto=format&fit=crop&w=900&q=80"
                    alt="Ayam goreng kriuk" class="h-52 w-full object-cover grayscale">
            </div>
        </article>
    </section>

    <section class="mt-10 overflow-hidden rounded-3xl bg-neutral px-6 py-10 text-neutral-content md:px-10 md:py-12">
        <p class="text-xs font-bold uppercase tracking-[0.24em] text-neutral-content/75">Newsletter</p>
        <h2 class="mt-3 max-w-3xl text-4xl leading-tight md:text-5xl" style="font-family: 'Playfair Display', serif;">
            Get menu updates and chef notes in one concise weekly digest.
        </h2>
        <p class="mt-4 max-w-2xl text-sm text-neutral-content/80">
            Tetap terhubung dengan menu seasonal, rekomendasi pairing, dan jadwal live kitchen terbaru.
        </p>

        <div class="mt-7 flex flex-wrap items-center gap-3">
            <a href="#" class="btn btn-sm border-0 bg-base-100 text-base-content hover:bg-base-200">Subscribe</a>
            <a href="{{ route('public.menu.index') }}"
                class="btn btn-sm btn-outline border-neutral-content/35 text-neutral-content hover:bg-neutral-content/10">
                Browse Full Menu
            </a>
        </div>
    </section>
</x-guest-layout>
