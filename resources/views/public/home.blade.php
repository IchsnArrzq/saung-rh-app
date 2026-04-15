<x-guest-layout>
    <section class="grid gap-4 lg:grid-cols-[1fr_1.2fr_1fr]">
        <article class="relative overflow-hidden rounded-3xl bg-emerald-800 p-6 text-amber-50 md:p-7">
            <span class="inline-flex rounded-full bg-amber-300 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-emerald-900">
                Chef Choice
            </span>
            <h1 class="mt-5 text-4xl leading-tight md:text-5xl" style="font-family: 'Playfair Display', serif;">
                Get your first chef job.
            </h1>
            <p class="mt-4 text-sm text-emerald-100">
                Mulai karier kuliner kamu dengan ritme dapur profesional, menu premium, dan tim terbaik.
            </p>
            <a href="{{ route('public.menu.index') }}"
                class="mt-7 inline-flex items-center rounded-full bg-amber-50 px-5 py-2 text-sm font-semibold text-emerald-900 transition hover:bg-white">
                Lihat Menu
            </a>

            <div class="pointer-events-none absolute -bottom-14 -left-10 h-36 w-36 rounded-full border border-amber-300/30"></div>
            <div class="pointer-events-none absolute -top-8 -right-8 h-28 w-28 rounded-full bg-amber-200/15"></div>
        </article>

        <article class="relative overflow-hidden rounded-3xl bg-stone-200">
            <img src="https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=1200&q=80"
                alt="Chef menyiapkan adonan" class="h-full min-h-[320px] w-full object-cover">
            <div class="absolute inset-x-4 bottom-4 rounded-2xl bg-stone-900/70 p-4 text-amber-50 backdrop-blur">
                <p class="text-xs uppercase tracking-[0.2em] text-amber-200">Open Kitchen</p>
                <p class="mt-1 text-lg font-semibold">Live plating setiap jam 10.00 - 20.00</p>
            </div>
        </article>

        <article class="relative overflow-hidden rounded-3xl bg-amber-300 p-6 text-stone-900 md:p-7">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-stone-700">Food News</p>
            <h2 class="mt-4 text-4xl leading-tight md:text-5xl" style="font-family: 'Playfair Display', serif;">
                Cheese and spice on every slice.
            </h2>
            <p class="mt-4 text-sm text-stone-700">
                Why bother when you can easily mix up a batch at home, lengkap dengan rasa khas Saung RH.
            </p>
            <a href="#highlight"
                class="mt-7 inline-flex items-center rounded-full border border-stone-300 bg-white px-5 py-2 text-sm font-semibold transition hover:border-stone-500">
                Read Recipe
            </a>

            <div class="pointer-events-none absolute -bottom-10 right-4 h-28 w-28 rounded-full border border-stone-900/15"></div>
        </article>
    </section>

    @if (session('success'))
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->has('menu'))
        <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
            {{ $errors->first('menu') }}
        </div>
    @endif

    <section id="menu" class="mt-10 border-y border-stone-200 py-8">
        <div class="mb-6 flex flex-wrap items-center gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-stone-500">Menu Makanan</p>
                <h2 class="mt-2 text-3xl font-semibold text-stone-900 md:text-4xl" style="font-family: 'Playfair Display', serif;">
                    Pilihan Menu Favorit Saung RH
                </h2>
            </div>

            <a href="{{ route('public.cart.index') }}"
                class="ml-auto inline-flex items-center rounded-full bg-amber-300 px-5 py-2 text-sm font-semibold text-stone-900 transition hover:bg-amber-400">
                Cart ({{ $cartCount }})
            </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse ($menus as $menu)
                <article class="overflow-hidden rounded-3xl border border-stone-200 bg-white">
                    <div class="aspect-[4/3] w-full bg-stone-100">
                        <img src="{{ $menu->image_url ?: 'https://picsum.photos/seed/'.urlencode((string) $menu->id).'/800/600' }}"
                            alt="{{ $menu->name }}" class="h-full w-full object-cover">
                    </div>

                    <div class="p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-stone-500">{{ $menu->category->name ?? 'Menu' }}</p>
                        <h3 class="mt-1 text-lg font-semibold text-stone-900">{{ $menu->name }}</h3>
                        <p class="mt-1 text-sm text-stone-600">{{ \Illuminate\Support\Str::limit($menu->description ?: 'Menu favorit restoran.', 72) }}</p>
                        <p class="mt-3 text-lg font-bold text-emerald-800">Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</p>

                        <form method="POST" action="{{ route('public.menu.cart.store', $menu) }}" class="mt-4">
                            @csrf
                            <button type="submit"
                                class="w-full rounded-full bg-emerald-800 px-4 py-2 text-sm font-semibold text-amber-50 transition hover:bg-emerald-700">
                                Masukkan ke Cart
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-white p-5 text-center text-sm text-stone-500">
                    Menu belum tersedia.
                </p>
            @endforelse
        </div>

        <div class="mt-6">
            <a href="{{ route('public.menu.index') }}"
                class="inline-flex items-center rounded-full border border-stone-300 bg-white px-5 py-2 text-sm font-semibold text-stone-800 transition hover:border-emerald-800 hover:text-emerald-800">
                Lihat Semua Menu
            </a>
        </div>
    </section>

    <section class="mt-10 grid gap-6 lg:grid-cols-[1fr_1.1fr]">
        <article class="relative overflow-hidden rounded-3xl bg-amber-300 p-6 md:p-7">
            <div class="flex items-center justify-between">
                <p class="text-2xl font-semibold text-stone-900">Chicken Burgers</p>
                <span class="text-sm font-bold text-stone-700">18</span>
            </div>

            <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=1000&q=80"
                alt="Burger ayam crispy" class="mt-5 h-64 w-full rounded-2xl object-cover md:h-72">

            <p class="mt-4 text-lg font-semibold text-stone-800">
                Crispy potato wedges with extra crunch.
            </p>
            <a href="#"
                class="mt-5 inline-flex h-11 w-11 items-center justify-center rounded-full bg-white text-xl text-stone-900 transition hover:bg-amber-50">
                Go
            </a>

            <div class="pointer-events-none absolute -left-14 bottom-10 h-24 w-24 rounded-full bg-emerald-700/70"></div>
            <div class="pointer-events-none absolute -right-10 top-24 h-24 w-24 rounded-full bg-orange-500/60"></div>
        </article>

        <article class="rounded-3xl border border-stone-200 bg-amber-50 p-6 md:p-8">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">Signature Menu</p>
            <h2 class="mt-4 text-5xl leading-[1.05] text-stone-900 md:text-6xl"
                style="font-family: 'Playfair Display', serif;">
                Snacks crafted for taste, cravings.
            </h2>
            <p class="mt-4 text-sm text-stone-600">
                Beef patty, cheddar cheese, lettuce, tomato, pickles, onion, secret sauce, toasted bun.
            </p>
            <div class="mt-7 flex flex-wrap items-center gap-3">
                <a href="#"
                    class="rounded-full bg-emerald-800 px-6 py-2 text-sm font-semibold text-amber-50 transition hover:bg-emerald-700">
                    Taste the Magic
                </a>
                <span class="rounded-full bg-orange-500 px-3 py-2 text-sm font-semibold text-white">Menu</span>
            </div>

            <div class="mt-8 border-t border-stone-200 pt-6">
                <div class="flex items-center gap-4">
                    <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=300&q=80"
                        alt="Arnold chef" class="h-16 w-16 rounded-full object-cover">
                    <div>
                        <p class="text-xl font-semibold text-stone-900">Arnold Classic</p>
                        <p class="text-sm text-stone-500">Chef</p>
                    </div>
                </div>
                <p class="mt-4 text-sm text-stone-600">
                    Our master chef passionately crafts fresh flavorful dishes to delight every palate.
                </p>
            </div>
        </article>
    </section>

    <section id="highlight" class="mt-10 grid gap-6 lg:grid-cols-[1.1fr_1fr]">
        <article class="rounded-3xl border border-stone-200 bg-amber-50 p-6 md:p-8">
            <h2 class="text-5xl leading-[1.03] text-stone-900 md:text-6xl" style="font-family: 'Playfair Display', serif;">
                Dive into the delicious corn chicken burger!
            </h2>
            <a href="#"
                class="mt-6 inline-flex rounded-full bg-stone-900 px-6 py-2 text-sm font-semibold text-amber-50 transition hover:bg-stone-700">
                Learn the Magic
            </a>
            <p class="mt-7 text-2xl font-semibold text-stone-900">Basil pasta with sun dried tomato touch.</p>
            <p class="mt-2 text-sm text-stone-600">
                Enjoy a homemade stacked packed with juicy flavors for a delicious bite.
            </p>

            <div class="mt-7 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl bg-emerald-800 p-4 text-amber-50">
                    <p class="text-sm uppercase tracking-[0.16em] text-amber-100">Hot Menu</p>
                    <p class="mt-2 text-lg font-semibold">Menu favorit tersedia setiap hari.</p>
                </div>
                <div class="rounded-2xl bg-amber-300 p-4 text-stone-900">
                    <p class="text-sm uppercase tracking-[0.16em] text-stone-700">Chef Table</p>
                    <p class="mt-2 text-lg font-semibold">Experience open kitchen seat</p>
                </div>
            </div>
        </article>

        <article class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
            <div class="overflow-hidden rounded-3xl bg-stone-100">
                <img src="https://images.unsplash.com/photo-1518013431117-eb1465fa5752?auto=format&fit=crop&w=900&q=80"
                    alt="French fries" class="h-48 w-full object-cover">
            </div>
            <div class="overflow-hidden rounded-3xl bg-amber-300 p-4">
                <img src="{{ asset('assets/drink.png') }}" alt="Minuman segar" class="h-40 w-full object-contain">
            </div>
            <div class="overflow-hidden rounded-3xl bg-emerald-800 p-4">
                <img src="https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?auto=format&fit=crop&w=900&q=80"
                    alt="Ayam goreng kriuk" class="h-48 w-full rounded-2xl object-cover">
            </div>
        </article>
    </section>

    <section class="mt-10 grid gap-6 border-y border-stone-200 py-9 lg:grid-cols-[1.1fr_1fr]">
        <article class="grid gap-4 sm:grid-cols-[1fr_1.1fr]">
            <img src="https://images.unsplash.com/photo-1485968579580-b6d095142e6e?auto=format&fit=crop&w=700&q=80"
                alt="Customer menikmati burger" class="h-full min-h-[220px] w-full rounded-3xl object-cover">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">Food Flavours</p>
                <p class="mt-3 text-3xl font-semibold text-stone-900">Crispy potato crunchy wedges.</p>
                <p class="mt-3 text-sm text-stone-600">
                    Potatoes, butter, olive oil, ground paprika.
                </p>
                <a href="{{ route('public.menu.index') }}"
                    class="mt-5 inline-flex rounded-full bg-emerald-800 px-5 py-2 text-sm font-semibold text-amber-50 transition hover:bg-emerald-700">
                    Book Restaurant
                </a>
            </div>
        </article>

        <article>
            <h2 class="text-5xl leading-[1.03] text-stone-900 md:text-6xl" style="font-family: 'Playfair Display', serif;">
                Savour the <span class="text-orange-500">flavour</span>, any time you'll savour.
            </h2>
            <div class="mt-7 inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-stone-700">
                <span class="text-amber-500">5/5</span>
                <span>Rylic Studio</span>
            </div>
            <p class="mt-5 max-w-md text-sm text-stone-600">
                Saung RH menyajikan evaluasi snack dan food dengan fokus rasa, detail bahan, dan konsistensi plating.
            </p>
        </article>
    </section>

    <section class="mt-10 overflow-hidden rounded-3xl bg-emerald-800 px-6 py-10 text-amber-50 md:px-10 md:py-12">
        <p class="text-xs font-bold uppercase tracking-[0.28em] text-emerald-100">Better Taste, Less Hassle</p>
        <h2 class="mt-3 text-5xl leading-[1.02] md:text-6xl" style="font-family: 'Playfair Display', serif;">
            Fuel your <span class="text-amber-300">stomach</span> in every bite
        </h2>
        <p class="mt-4 max-w-2xl text-sm text-emerald-100">
            Dapatkan update menu terbaru dan resep andalan langsung ke email kamu.
        </p>

        <div class="mt-7 flex flex-wrap items-center gap-3">
            <a href="#" class="rounded-full bg-amber-50 px-6 py-3 text-sm font-semibold text-emerald-900 transition hover:bg-white">
                Subscribe Us
            </a>
            <span class="rounded-full bg-orange-500 px-4 py-3 text-sm font-semibold text-white">Discord</span>
        </div>
    </section>
</x-guest-layout>
