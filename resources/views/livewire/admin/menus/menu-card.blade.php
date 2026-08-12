<div class="space-y-5">
    @include('admin.partials.flash')

    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <x-search-input class="max-w-md" wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama, SKU, kategori..." label="Cari menu" />

                @if ($search !== '')
                    <x-button variant="ghost" size="sm" wire:click="$set('search', '')">Reset</x-button>
                @endif
            </div>

            <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('menus.create')">
                Tambah Menu
            </x-button>
        </div>
    </x-card>

    <section class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        @forelse ($menus as $menu)
            <article class="overflow-hidden rounded-xl border border-base-300 bg-base-100"
                wire:key="menu-card-{{ $menu->id }}">
                <div class="aspect-[4/3] w-full bg-base-200">
                    <img src="{{ $menu->image_url ?: 'https://picsum.photos/seed/' . urlencode((string) $menu->id) . '/800/600' }}"
                        alt="{{ $menu->name }}" class="h-full w-full object-cover">
                </div>

                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-base-content/60">
                            {{ $menu->category->name ?? 'Menu' }}</p>
                        <x-badge size="sm" :color="$menu->is_available ? 'success' : 'error'">
                            {{ $menu->is_available ? 'Tersedia' : 'Habis' }}
                        </x-badge>
                    </div>

                    <h3 class="mt-1 text-lg font-semibold">{{ $menu->name }}</h3>
                    <p class="mt-1 text-xs text-base-content/60">SKU: {{ $menu->sku ?: '-' }}</p>
                    <p class="mt-2 text-sm text-base-content/70">
                        {{ \Illuminate\Support\Str::limit($menu->description ?: 'Menu favorit restoran.', 90) }}
                    </p>
                    <p class="mt-3 text-lg font-bold text-accent">Rp
                        {{ number_format((float) $menu->price, 0, ',', '.') }}</p>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <x-button variant="outline" size="sm" :href="route('public.menu.show', $menu)"
                            target="_blank" rel="noopener">
                            Detail
                        </x-button>
                        <x-button variant="warning" size="sm" :href="route('menus.edit', $menu)">Edit</x-button>
                        <x-button variant="error" size="sm" class="ml-auto text-white"
                            data-confirm="Hapus menu ini?" wire:click="delete('{{ $menu->id }}')"
                            loading="delete('{{ $menu->id }}')">
                            Hapus
                        </x-button>
                    </div>
                </div>
            </article>
        @empty
            <x-empty-state class="col-span-full" icon="ri-restaurant-line" title="Belum ada data menu"
                description="Tambahkan menu pertama untuk mulai berjualan.">
                <x-slot:actions>
                    <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('menus.create')">
                        Tambah Menu
                    </x-button>
                </x-slot:actions>
            </x-empty-state>
        @endforelse
    </section>

    <div>{{ $menus->links() }}</div>
</div>
