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

    <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        @forelse ($menus as $menu)
            <article class="overflow-hidden rounded-2xl border border-base-200 bg-base-100 shadow-sm">
                <div class="aspect-[4/3]  relative">
                    <button type="button" wire:click="showMenuDetail('{{ $menu->id }}')"
                        class="absolute inset-0 z-10 flex items-center justify-center bg-black/50 text-white opacity-0 transition-opacity hover:opacity-50 cursor-pointer">
                        <i class="ri-expand-diagonal-2-line text-2xl"></i>
                    </button>
                    @if ($menu->image_url)
                        <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}"
                            class="h-full w-full object-cover rounded-2xl p-1">
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

    <x-modal name="menu-detail-modal" maxWidth="lg">
        @if ($selectedMenu)
            @php
                $statusBadgeClass = match ($selectedMenu['status_color']) {
                    'success' => 'badge-success',
                    'error' => 'badge-error',
                    'warning' => 'badge-warning',
                    'info' => 'badge-info',
                    default => 'badge-neutral',
                };
            @endphp

            <div class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-xl font-semibold">{{ $selectedMenu['name'] }}</h3>
                        <p class="text-sm text-base-content/70">{{ $selectedMenu['category_name'] }}</p>
                    </div>
                    <button type="button" wire:click="closeMenuDetail" class="btn btn-sm btn-ghost btn-circle"
                        aria-label="Tutup">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>

                <div class="aspect-[16/10] overflow-hidden rounded-xl bg-base-200">
                    @if ($selectedMenu['image_url'] !== '')
                        <img src="{{ $selectedMenu['image_url'] }}" alt="{{ $selectedMenu['name'] }}"
                            class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-base-content/60">
                            <i class="ri-image-line text-5xl"></i>
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge {{ $statusBadgeClass }}">{{ $selectedMenu['status_name'] }}</span>
                    <span class="badge badge-outline">SKU: {{ $selectedMenu['sku'] }}</span>
                    <span class="badge {{ $selectedMenu['is_available'] ? 'badge-success' : 'badge-error' }}">
                        {{ $selectedMenu['is_available'] ? 'Aktif Dijual' : 'Tidak Dijual' }}
                    </span>
                </div>

                <div>
                    <p class="text-2xl font-bold">Rp {{ number_format((float) $selectedMenu['price'], 0, ',', '.') }}
                    </p>
                    <p class="mt-2 text-sm leading-relaxed text-base-content/80">
                        {{ $selectedMenu['description'] !== '' ? $selectedMenu['description'] : 'Belum ada deskripsi menu.' }}
                    </p>
                </div>
            </div>
        @endif
    </x-modal>
</div>
