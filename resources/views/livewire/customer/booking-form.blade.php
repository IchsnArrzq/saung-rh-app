<div>
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-error/30 bg-error/10 px-4 py-3 text-sm text-error">
            <p class="font-semibold">Periksa input berikut:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit="submit">
        <div class="grid gap-4 xl:grid-cols-12 xl:items-start">
            {{-- Kiri: Detail Reservasi + Pilih Menu --}}
            <div class="space-y-4 xl:col-span-7">
                {{-- Info Reservasi --}}
                <section class="rounded-2xl border border-base-300 bg-base-100 p-4 md:p-5">
                    <h1 class="text-xl font-semibold">Booking Meja</h1>
                    <p class="mt-0.5 text-sm text-base-content/70">Isi detail reservasi dan pilih menu yang ingin dipesan.</p>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="form-control w-full">
                            <span class="label-text mb-1">Pilih Meja</span>
                            <select wire:model="table_id" class="select select-bordered w-full">
                                <option value="">-- Pilih Meja --</option>
                                @foreach ($tables as $table)
                                    <option value="{{ $table->id }}">
                                        {{ $table->code }} — Kapasitas {{ $table->capacity }} orang
                                    </option>
                                @endforeach
                            </select>
                            @error('table_id')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="form-control w-full">
                            <span class="label-text mb-1">Jumlah Orang</span>
                            <input type="number" wire:model="pax" min="1" max="30" class="input input-bordered w-full">
                            @error('pax')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="form-control w-full">
                            <span class="label-text mb-1">Waktu Reservasi</span>
                            <input type="datetime-local" wire:model="reservation_at" class="input input-bordered w-full">
                            @error('reservation_at')<span class="mt-1 text-xs text-error">{{ $message }}</span>@enderror
                        </label>

                        <label class="form-control w-full">
                            <span class="label-text mb-1">Catatan Reservasi</span>
                            <textarea wire:model="notes" rows="2" class="textarea textarea-bordered w-full"
                                placeholder="opsional"></textarea>
                        </label>
                    </div>
                </section>

                {{-- Pilih Menu --}}
                <section class="space-y-4 rounded-2xl border border-base-300 bg-base-100 p-4 md:p-5">
                    <h2 class="font-semibold">Pilih Menu</h2>

                    {{-- Filter Kategori --}}
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="setCategory()"
                            class="btn btn-sm rounded-full {{ is_null($activeCategory) ? 'btn-primary' : 'btn-ghost border border-base-300' }}">
                            Semua
                            <span class="badge badge-sm">{{ $totalMenus }}</span>
                        </button>
                        @foreach ($categories as $category)
                            <button type="button" wire:click="setCategory({{ $category->id }})"
                                class="btn btn-sm rounded-full {{ $activeCategory === $category->id ? 'btn-primary' : 'btn-ghost border border-base-300' }}">
                                {{ $category->name }}
                                <span class="badge badge-sm">{{ $category->menus_count }}</span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Search --}}
                    <div class="relative">
                        <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></i>
                        <input type="text" wire:model.live.debounce.300ms="search" class="input input-bordered w-full pl-10"
                            placeholder="Cari nama menu...">
                    </div>

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
                                        <button type="button" wire:click="addItem('{{ $menu->id }}')"
                                            class="btn btn-sm btn-neutral btn-square">
                                            <i class="ri-add-line text-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="col-span-full rounded-2xl border border-dashed border-base-300 p-8 text-center text-base-content/50">
                                Menu tidak ditemukan.
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            {{-- Kanan: Order Details --}}
            <aside class="xl:col-span-5 xl:sticky xl:top-4">
                <section class="rounded-2xl border border-base-300 bg-base-100 p-4">
                    <div class="mb-4 flex items-center justify-between gap-2">
                        <h3 class="text-xl font-semibold">Order Details</h3>
                        @if (count($items) > 0)
                            <button type="button" wire:click="resetItems" class="btn btn-sm btn-outline">
                                <i class="ri-delete-bin-line"></i> Reset
                            </button>
                        @endif
                    </div>

                    @if (count($items) === 0)
                        <div class="rounded-xl border border-dashed border-base-300 p-6 text-center text-sm text-base-content/50">
                            Belum ada menu dipilih.<br>Klik tombol <span class="font-semibold">+</span> pada menu di kiri.
                        </div>
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
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                            class="btn btn-sm btn-error btn-square text-white">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>

                                    <div class="mt-2">
                                        <input type="text" wire:model="items.{{ $index }}.notes"
                                            class="input input-bordered input-sm w-full" placeholder="Catatan (opsional)">
                                    </div>

                                    <div class="mt-2 flex items-center justify-end gap-2">
                                        <button type="button" wire:click="decrement({{ $index }})"
                                            class="btn btn-sm btn-outline btn-square">
                                            <i class="ri-subtract-line"></i>
                                        </button>
                                        <span class="min-w-8 text-center text-lg font-semibold">{{ $item['qty'] }}</span>
                                        <button type="button" wire:click="increment({{ $index }})"
                                            class="btn btn-sm btn-outline btn-square">
                                            <i class="ri-add-line"></i>
                                        </button>
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
                        <button type="submit" @disabled(count($items) === 0)
                            class="btn btn-primary w-full">
                            <i class="ri-calendar-check-line"></i>
                            Kirim Reservasi
                        </button>
                        <a href="{{ route('customer.dashboard') }}" wire:navigate class="btn btn-ghost w-full">Batal</a>
                    </div>
                </section>
            </aside>
        </div>
    </form>
</div>
