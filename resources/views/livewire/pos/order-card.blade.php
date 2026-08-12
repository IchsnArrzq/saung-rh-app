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
            <x-button size="sm" wire:click="setCategory"
                :variant="is_null($activeCategoryId) ? 'primary' : 'ghost'"
                class="rounded-full {{ is_null($activeCategoryId) ? '' : 'border border-base-300' }}">
                Semua
                <span class="badge badge-sm">{{ $totalAvailableMenus }}</span>
            </x-button>

            @foreach ($categories as $category)
                <x-button size="sm" wire:click="setCategory({{ $category->id }})"
                    :variant="$activeCategoryId === $category->id ? 'primary' : 'ghost'"
                    class="rounded-full {{ $activeCategoryId === $category->id ? '' : 'border border-base-300' }}">
                    {{ $category->name }}
                    <span class="badge badge-sm">{{ $category->menus_count }}</span>
                </x-button>
            @endforeach
        </div>

        <x-search-input wire:model.live.debounce.300ms="search"
            placeholder="Cari menu, deskripsi, SKU, atau kategori..." label="Cari menu" />

        <div
            @class([
                'grid gap-2 sm:grid-cols-2',
                'xl:grid-cols-2 2xl:grid-cols-3' => $cartCount > 0,
                'xl:grid-cols-3 2xl:grid-cols-4' => $cartCount <= 0,
            ])>
            @forelse ($menus as $menu)
                <article class="overflow-hidden rounded-2xl border border-base-200 bg-base-100 shadow-sm">
                    <div class="relative aspect-[4/3]">
                        @if ($menu->image_url)
                            <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}"
                                class="h-full w-full rounded-2xl object-cover p-1">
                        @else
                            <div class="flex h-full items-center justify-center text-base-content/60">
                                <i class="ri-image-line text-4xl"></i>
                            </div>
                        @endif

                        <x-button variant="neutral" size="sm" shape="circle" icon="ri-information-line"
                            label="Lihat detail {{ $menu->name }}" class="absolute right-2 top-2 z-10 opacity-90"
                            wire:click="showMenuDetail('{{ $menu->id }}')" />
                    </div>
                    <div class="space-y-3 p-4">
                        <div>
                            <p class="line-clamp-1 text-base font-semibold">{{ $menu->name }}</p>
                            <p class="text-xs text-base-content/70">{{ $menu->category?->name ?? 'Uncategorized' }}</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-lg font-semibold">Rp {{ number_format((float) $menu->price, 0, ',', '.') }}
                            </p>
                            <x-button variant="neutral" size="sm" shape="square" icon="ri-add-line text-lg"
                                label="Tambah {{ $menu->name }} ke order"
                                wire:click="addToCart('{{ $menu->id }}')" loading="addToCart('{{ $menu->id }}')" />
                        </div>
                    </div>
                </article>
            @empty
                <x-empty-state class="col-span-full" icon="ri-restaurant-line"
                    title="Belum ada menu tersedia"
                    description="Tidak ada menu pada kategori ini. Coba kategori lain atau ubah kata pencarian." />
            @endforelse
        </div>
    </div>

    @if ($cartCount > 0)
        <aside class="xl:col-span-5">
            <x-card>
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h3 class="text-xl font-semibold">Rincian Order</h3>
                    <x-button variant="outline" size="sm" icon="ri-delete-bin-line" wire:click="clearCart"
                        data-confirm="Reset semua item order ini?">
                        Reset Order
                    </x-button>
                </div>

                <div class="space-y-3 rounded-xl border border-base-300 bg-base-100 p-3">
                    <x-select label="Meja (Opsional)" name="tableId" wire:model.defer="tableId">
                        <option value="">Tanpa meja (take away / online)</option>
                        @foreach ($tables as $table)
                            <option value="{{ $table->id }}">
                                {{ $table->code }} - {{ $table->name }}
                                @if ($status = \App\Domains\Table\Enums\TableStatus::tryFrom((string) $table->status))
                                    ({{ $status->label() }})
                                @endif
                            </option>
                        @endforeach
                    </x-select>

                    <x-input label="Nama Customer" name="customerName" wire:model.defer="customerName"
                        placeholder="Contoh: Budi / Walk-in customer" />

                    <x-textarea label="Catatan Order" name="notes" :rows="2" wire:model.defer="notes"
                        placeholder="Catatan tambahan untuk dapur (opsional)" />

                    <div class="rounded-xl border border-base-300 bg-base-200/60 p-3">
                        <label class="flex cursor-pointer items-center justify-between gap-3">
                            <span>
                                <span class="block text-xs font-semibold uppercase tracking-wide text-base-content/70">Pembayaran</span>
                                <span class="text-sm font-medium text-base-content">Langsung buat payment</span>
                            </span>
                            <input type="checkbox" class="toggle toggle-primary" wire:model.live="payNow">
                        </label>

                        @if ($payNow)
                            <x-select label="Metode Pembayaran" name="paymentMethod" class="mt-3"
                                wire:model.defer="paymentMethod" :options="[
                                    'cash' => 'Cash',
                                    'qris' => 'QRIS',
                                    'debit_card' => 'Debit Card',
                                    'credit_card' => 'Credit Card',
                                    'transfer' => 'Transfer',
                                    'ewallet' => 'E-Wallet',
                                ]" />
                        @endif
                    </div>
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

                                <x-button variant="error" size="sm" shape="square" icon="ri-delete-bin-line"
                                    label="Hapus {{ $item['name'] }}" class="text-white"
                                    wire:click="removeCartItem('{{ $item['menu_id'] }}')"
                                    data-confirm="Hapus item ini dari order?" />
                            </div>

                            <div class="mt-3 flex items-center justify-end gap-2">
                                <x-button variant="outline" size="sm" shape="square" icon="ri-subtract-line"
                                    label="Kurangi jumlah {{ $item['name'] }}"
                                    wire:click="decrementQty('{{ $item['menu_id'] }}')" />
                                <span class="min-w-8 text-center text-lg font-semibold">{{ $item['qty'] }}</span>
                                <x-button variant="outline" size="sm" shape="square" icon="ri-add-line"
                                    label="Tambah jumlah {{ $item['name'] }}"
                                    wire:click="incrementQty('{{ $item['menu_id'] }}')" />
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

                    <x-button variant="primary" :block="true" class="mt-4" icon="ri-save-2-line"
                        wire:click="placeOrder" loading="placeOrder">
                        Simpan Order
                    </x-button>
                </div>
            </x-card>
        </aside>
    @endif

    <x-modal name="menu-detail-modal" maxWidth="lg">
        @if ($selectedMenu)
            <div class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-xl font-semibold">{{ $selectedMenu['name'] }}</h3>
                        <p class="text-sm text-base-content/70">{{ $selectedMenu['category_name'] }}</p>
                    </div>
                    <x-button variant="ghost" size="sm" shape="circle" icon="ri-close-line text-lg"
                        label="Tutup" wire:click="closeMenuDetail" />
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
                    <x-badge :color="$selectedMenu['status_color']">{{ $selectedMenu['status_name'] }}</x-badge>
                    <x-badge color="default" :outline="true">SKU: {{ $selectedMenu['sku'] }}</x-badge>
                    <x-badge :color="$selectedMenu['is_available'] ? 'success' : 'error'">
                        {{ $selectedMenu['is_available'] ? 'Aktif Dijual' : 'Tidak Dijual' }}
                    </x-badge>
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
