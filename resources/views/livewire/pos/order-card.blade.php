<div class="grid gap-4 xl:grid-cols-12">
    @if (session('success') || $errors->any())
        <div class="col-span-full">
            @include('admin.partials.flash')
        </div>
    @endif

    <div
        @class([
            'space-y-4',
            'xl:col-span-7' => $cartCount > 0,
            'xl:col-span-12' => $cartCount <= 0,
        ])>
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

        <label class="input input-bordered flex items-center gap-2">
            <i class="ri-search-line text-stone-400"></i>
            <input type="text" class="grow" wire:model.live.debounce.300ms="search"
                placeholder="Cari menu, deskripsi, SKU, atau kategori..." />
        </label>

        <div
            @class([
                'grid gap-2 sm:grid-cols-2',
                'xl:grid-cols-2 2xl:grid-cols-3' => $cartCount > 0,
                'xl:grid-cols-3 2xl:grid-cols-4' => $cartCount <= 0,
            ])>
            @forelse ($menus as $menu)
                <article class="overflow-hidden rounded-2xl border border-base-200 bg-base-100 shadow-sm">
                    <div class="relative aspect-[4/3]">
                        <button type="button" wire:click="showMenuDetail('{{ $menu->id }}')"
                            class="absolute inset-0 z-10 flex cursor-pointer items-center justify-center bg-black/50 text-white opacity-0 transition-opacity hover:opacity-50">
                            <i class="ri-expand-diagonal-2-line text-2xl"></i>
                        </button>
                        @if ($menu->image_url)
                            <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}"
                                class="h-full w-full rounded-2xl object-cover p-1">
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
                            <p class="text-lg font-semibold">Rp {{ number_format((float) $menu->price, 0, ',', '.') }}
                            </p>
                            <button type="button" wire:click="addToCart('{{ $menu->id }}')"
                                class="btn btn-sm btn-neutral btn-square" aria-label="Tambah ke order">
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

    @if ($cartCount > 0)
        <aside class="xl:col-span-5">
            <section class="rounded-2xl border border-base-300 bg-base-100 p-4">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-xl font-semibold">Order Details</h3>
                    <button type="button" wire:click="clearCart" data-confirm="Reset semua item order ini?"
                        class="btn btn-sm btn-outline">
                        <i class="ri-delete-bin-line"></i>
                        Reset Order
                    </button>
                </div>

                <div class="space-y-3 rounded-xl border border-base-300 bg-base-100 p-3">
                    <label class="form-control w-full">
                        <div class="label py-1">
                            <span class="label-text text-xs font-semibold uppercase tracking-wide text-base-content/70">Meja
                                (Opsional)</span>
                        </div>
                        <select class="select select-bordered w-full" wire:model.defer="tableId">
                            <option value="">Tanpa meja (take away / online)</option>
                            @foreach ($tables as $table)
                                <option value="{{ $table->id }}">
                                    {{ $table->code }} - {{ $table->name }}
                                    @if ($table->tableStatus?->name)
                                        ({{ $table->tableStatus->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('tableId')
                            <span class="mt-1 text-xs text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="form-control w-full">
                        <div class="label py-1">
                            <span class="label-text text-xs font-semibold uppercase tracking-wide text-base-content/70">Nama
                                Customer</span>
                        </div>
                        <input type="text" class="input input-bordered w-full" wire:model.defer="customerName"
                            placeholder="Contoh: Budi / Walk-in customer">
                        @error('customerName')
                            <span class="mt-1 text-xs text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="form-control w-full">
                        <div class="label py-1">
                            <span class="label-text text-xs font-semibold uppercase tracking-wide text-base-content/70">Catatan
                                Order</span>
                        </div>
                        <textarea class="textarea textarea-bordered w-full" rows="2" wire:model.defer="notes"
                            placeholder="Catatan tambahan untuk dapur (opsional)"></textarea>
                        @error('notes')
                            <span class="mt-1 text-xs text-error">{{ $message }}</span>
                        @enderror
                    </label>
                </div>

                <div class="mt-3 space-y-3">
                    @foreach ($cartItems as $item)
                        <article class="rounded-xl border border-base-300 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="h-16 w-16 overflow-hidden rounded-lg bg-base-200">
                                        @if ($item['image_url'])
                                            <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}"
                                                class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full items-center justify-center text-base-content/60">
                                                <i class="ri-image-line text-xl"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ $item['name'] }}</p>
                                        <p class="text-sm text-base-content/70">Rp
                                            {{ number_format((float) $item['price'], 0, ',', '.') }}</p>
                                    </div>
                                </div>

                                <button type="button" wire:click="removeCartItem('{{ $item['menu_id'] }}')"
                                    data-confirm="Hapus item ini dari order?"
                                    class="btn btn-sm btn-error btn-square text-white" aria-label="Hapus item">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>

                            <div class="mt-3 flex items-center justify-end gap-2">
                                <button type="button" wire:click="decrementQty('{{ $item['menu_id'] }}')"
                                    class="btn btn-sm btn-outline btn-square" aria-label="Kurangi qty">
                                    <i class="ri-subtract-line"></i>
                                </button>
                                <span class="min-w-8 text-center text-lg font-semibold">{{ $item['qty'] }}</span>
                                <button type="button" wire:click="incrementQty('{{ $item['menu_id'] }}')"
                                    class="btn btn-sm btn-outline btn-square" aria-label="Tambah qty">
                                    <i class="ri-add-line"></i>
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-4 border-t border-base-300 pt-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-base-content/70">Total Item</span>
                        <span class="font-medium">{{ $cartCount }}</span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-lg font-semibold">
                        <span>Sub Total</span>
                        <span>Rp {{ number_format($cartSubtotal, 0, ',', '.') }}</span>
                    </div>

                    @error('cart')
                        <p class="mt-2 text-xs font-medium text-error">{{ $message }}</p>
                    @enderror

                    <button type="button" wire:click="placeOrder" wire:loading.attr="disabled" wire:target="placeOrder"
                        class="btn btn-primary mt-4 w-full">
                        <span wire:loading.remove wire:target="placeOrder">
                            <i class="ri-save-2-line"></i>
                            Simpan Order
                        </span>
                        <span wire:loading wire:target="placeOrder" class="inline-flex items-center gap-2">
                            <span class="loading loading-spinner loading-sm"></span>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </section>
        </aside>
    @endif

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
