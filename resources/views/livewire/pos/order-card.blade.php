<div class="space-y-4">
    <div class="flex flex-wrap items-center gap-2">
        <button type="button" wire:click="setCategory"
            class="btn btn-sm rounded-full {{ is_null($activeCategoryId) ? 'btn-primary' : 'btn-ghost border border-base-300' }}">
            All
            <span class="badge badge-sm">{{ $totalAvailableMenus }}</span>
        </button>

        @foreach ($categories as $category)
            <button type="button" wire:click="setCategory({{ $category->id }})"
                class="btn btn-sm rounded-full {{ $activeCategoryId === $category->id ? 'btn-primary' : 'btn-ghost border border-base-300' }}">
                {{ $category->name }}
                <span class="badge badge-sm">{{ $category->menus_count }}</span>
            </button>
        @endforeach
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        @forelse ($menus as $menu)
            <article class="overflow-hidden rounded-2xl border border-base-300 bg-base-100 shadow-sm">
                <div class="aspect-[4/3] bg-base-200">
                    @if ($menu->image_url)
                        <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-base-content/60">
                            <i class="ri-image-line text-4xl"></i>
                        </div>
                    @endif
                </div>
                <div class="space-y-3 p-4">
                    <div>
                        <p class="line-clamp-1 text-base font-semibold">{{ $menu->name }}</p>
                        <p class="text-xs text-base-content/70">{{ $menu->category?->name ?? 'Uncategorized' }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-lg font-semibold">Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</p>
                        <button type="button" class="btn btn-sm btn-neutral btn-square" aria-label="Tambah ke order">
                            <i class="ri-add-line text-lg"></i>
                        </button>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-base-300 bg-base-100 p-8 text-center">
                <p class="text-base-content/70">Belum ada menu tersedia pada kategori ini.</p>
            </div>
        @endforelse
    </div>
</div>
