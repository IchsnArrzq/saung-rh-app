<div>
    @if ($errors->any())
        <x-alert type="error" title="Periksa input berikut:" class="mb-4">
            <ul class="mt-1 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <form wire:submit="submit">
        <div class="grid gap-4 xl:grid-cols-12 xl:items-start">
            {{-- Kiri: Detail Reservasi + Pilih Menu --}}
            <div class="space-y-4 xl:col-span-7">
                {{-- Info Reservasi --}}
                <x-card>
                    <h1 class="text-xl font-semibold">Booking Meja</h1>
                    <p class="mt-0.5 text-sm text-base-content/70">Isi detail reservasi dan pilih menu yang ingin dipesan.</p>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <x-select label="Pilih Meja" name="table_id" :required="true"
                            placeholder="-- Pilih Meja --" wire:model="table_id">
                            @foreach ($tables as $table)
                                <option value="{{ $table->id }}">
                                    {{ $table->code }} — Kapasitas {{ $table->capacity }} orang
                                </option>
                            @endforeach
                        </x-select>

                        <x-input label="Jumlah Orang" name="pax" type="number" :required="true"
                            min="1" max="30" wire:model="pax" />

                        <x-input label="Waktu Reservasi" name="reservation_at" type="datetime-local" :required="true"
                            wire:model="reservation_at" />

                        <x-textarea label="Catatan Reservasi" name="notes" :rows="2"
                            wire:model="notes" placeholder="opsional" />
                    </div>
                </x-card>

                {{-- Pilih Menu --}}
                <x-card title="Pilih Menu" class="space-y-4">
                    {{-- Filter Kategori --}}
                    <div class="flex flex-wrap gap-2">
                        <x-button size="sm" wire:click="setCategory()"
                            :variant="is_null($activeCategory) ? 'primary' : 'ghost'"
                            class="rounded-full {{ is_null($activeCategory) ? '' : 'border border-base-300' }}">
                            Semua
                            <span class="badge badge-sm">{{ $totalMenus }}</span>
                        </x-button>

                        @foreach ($categories as $category)
                            <x-button size="sm" wire:click="setCategory({{ $category->id }})"
                                :variant="$activeCategory === $category->id ? 'primary' : 'ghost'"
                                class="rounded-full {{ $activeCategory === $category->id ? '' : 'border border-base-300' }}">
                                {{ $category->name }}
                                <span class="badge badge-sm">{{ $category->menus_count }}</span>
                            </x-button>
                        @endforeach
                    </div>

                    {{-- Search --}}
                    <x-search-input wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama menu..." label="Cari menu" />

                    {{-- Grid Menu --}}
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($menus as $menu)
                            <article class="overflow-hidden rounded-2xl border border-base-200 bg-base-100 shadow-sm">
                                <div class="aspect-[4/3] bg-base-200">
                                    @if ($menu->image_url)
                                        <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}"
                                            class="h-full w-full rounded-2xl object-cover p-1">
                                    @else
                                        <div class="flex h-full items-center justify-center text-base-content/40">
                                            <i class="ri-image-line text-4xl"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="space-y-2 p-3">
                                    <div>
                                        <p class="line-clamp-1 font-semibold">{{ $menu->name }}</p>
                                        <p class="text-xs text-base-content/60">{{ $menu->category?->name ?? 'Uncategorized' }}</p>
                                    </div>
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="font-semibold">Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</p>
                                        <x-button variant="neutral" size="sm" shape="square" icon="ri-add-line text-lg"
                                            label="Tambah {{ $menu->name }} ke pesanan"
                                            wire:click="addItem('{{ $menu->id }}')" />
                                    </div>
                                </div>
                            </article>
                        @empty
                            <x-empty-state class="col-span-full" icon="ri-search-line"
                                title="Menu tidak ditemukan"
                                description="Coba kata kunci lain atau pilih kategori yang berbeda." />
                        @endforelse
                    </div>
                </x-card>
            </div>

            {{-- Kanan: Order Details --}}
            <aside class="xl:col-span-5 xl:sticky xl:top-4">
                <x-card>
                    <div class="mb-4 flex items-center justify-between gap-2">
                        <h3 class="text-xl font-semibold">Rincian Pesanan</h3>
                        @if (count($items) > 0)
                            <x-button variant="outline" size="sm" icon="ri-delete-bin-line" wire:click="resetItems">
                                Reset
                            </x-button>
                        @endif
                    </div>

                    @if (count($items) === 0)
                        <x-empty-state icon="ri-shopping-basket-line" title="Belum ada menu dipilih"
                            description="Klik tombol + pada menu di sebelah kiri untuk menambahkannya." />
                    @else
                        <div class="space-y-3">
                            @foreach ($items as $index => $item)
                                <article class="rounded-xl border border-base-300 p-3" wire:key="item-{{ $item['menu_id'] }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-start gap-3">
                                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-base-200">
                                                @if ($item['image_url'])
                                                    <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}"
                                                        class="h-full w-full object-cover">
                                                @else
                                                    <div class="flex h-full items-center justify-center text-base-content/40">
                                                        <i class="ri-image-line"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-medium leading-tight">{{ $item['name'] }}</p>
                                                <p class="mt-0.5 text-sm text-base-content/60">
                                                    Rp {{ number_format((float) $item['price'], 0, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                        <x-button variant="error" size="sm" shape="square" icon="ri-delete-bin-line"
                                            label="Hapus {{ $item['name'] }}" class="text-white"
                                            wire:click="removeItem({{ $index }})" />
                                    </div>

                                    <div class="mt-2">
                                        <x-input name="items.{{ $index }}.notes" size="sm"
                                            wire:model="items.{{ $index }}.notes" placeholder="Catatan (opsional)" />
                                    </div>

                                    <div class="mt-2 flex items-center justify-end gap-2">
                                        <x-button variant="outline" size="sm" shape="square" icon="ri-subtract-line"
                                            label="Kurangi jumlah {{ $item['name'] }}" wire:click="decrement({{ $index }})" />
                                        <span class="min-w-8 text-center text-lg font-semibold">{{ $item['qty'] }}</span>
                                        <x-button variant="outline" size="sm" shape="square" icon="ri-add-line"
                                            label="Tambah jumlah {{ $item['name'] }}" wire:click="increment({{ $index }})" />
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="mt-4 border-t border-base-300 pt-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-base-content/70">Total Item</span>
                                <span class="font-medium">{{ $totalQty }}</span>
                            </div>
                            <div class="mt-1 flex items-center justify-between text-lg font-semibold">
                                <span>Sub Total</span>
                                <span>Rp {{ number_format((float) $subtotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 space-y-2">
                        <x-button type="submit" variant="primary" :block="true" icon="ri-calendar-check-line"
                            loading="submit" @disabled(count($items) === 0)>
                            Kirim Reservasi
                        </x-button>
                        <x-button variant="ghost" :block="true" :href="route('customer.dashboard')" wire:navigate>
                            Batal
                        </x-button>
                    </div>
                </x-card>
            </aside>
        </div>
    </form>
</div>
