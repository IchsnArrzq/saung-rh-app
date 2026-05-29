<div class="space-y-5">
    @include('admin.partials.flash')

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <label class="input input-bordered flex w-full max-w-md items-center gap-2">
                    <i class="ri-search-line text-stone-400"></i>
                    <input type="text" class="grow" wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama, SKU, kategori...">
                </label>
                @if ($search !== '')
                    <button type="button" class="btn btn-sm btn-ghost" wire:click="$set('search', '')">Reset</button>
                @endif
            </div>

            <a href="{{ route('menus.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Menu
            </a>
        </div>
    </section>

    <section class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        @forelse ($menus as $menu)
            <article class="overflow-hidden rounded-3xl border border-stone-200 bg-white"
                wire:key="menu-card-{{ $menu->id }}">
                <div class="aspect-[4/3] w-full bg-stone-100">
                    <img src="{{ $menu->image_url ?: 'https://picsum.photos/seed/' . urlencode((string) $menu->id) . '/800/600' }}"
                        alt="{{ $menu->name }}" class="h-full w-full object-cover">
                </div>

                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-stone-500">
                            {{ $menu->category->name ?? 'Menu' }}</p>
                        <span class="badge badge-sm {{ $menu->is_available ? 'badge-success' : 'badge-error' }}">
                            {{ $menu->is_available ? 'Tersedia' : 'Habis' }}
                        </span>
                    </div>

                    <h3 class="mt-1 text-lg font-semibold text-stone-900">{{ $menu->name }}</h3>
                    <p class="mt-1 text-xs text-stone-500">SKU: {{ $menu->sku ?: '-' }}</p>
                    <p class="mt-2 text-sm text-stone-600">
                        {{ \Illuminate\Support\Str::limit($menu->description ?: 'Menu favorit restoran.', 90) }}
                    </p>
                    <p class="mt-3 text-lg font-bold text-emerald-800">Rp
                        {{ number_format((float) $menu->price, 0, ',', '.') }}</p>

                    <div class="mt-4 gap-2">
                        <a href="{{ route('menus.edit', $menu) }}" class="btn btn-sm btn-warning">Edit</a>
                        <button type="button" class="btn btn-sm btn-error ml-auto text-white"
                            data-confirm="Hapus menu ini?" wire:click="delete('{{ $menu->id }}')">
                            Hapus
                        </button>
                    </div>
                </div>
            </article>
        @empty
            <p
                class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-white p-5 text-center text-sm text-stone-500">
                Belum ada data menu.
            </p>
        @endforelse
    </section>

    <div>{{ $menus->links() }}</div>
</div>

