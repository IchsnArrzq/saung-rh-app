<x-customer-layout>
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

    <form action="{{ route('customer.bookings.store') }}" method="POST"
        x-data="bookingForm({{ $menus->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'price' => (float)$m->price, 'image_url' => $m->image_url, 'category_id' => $m->menu_category_id, 'category_name' => $m->category?->name ?? 'Uncategorized'])->values()->toJson() }})">
        @csrf

        <div class="grid gap-4 xl:grid-cols-12 xl:items-start">

            {{-- Kiri: Detail Reservasi + Pilih Menu --}}
            <div class="space-y-4 xl:col-span-7">

                {{-- Info Reservasi --}}
                <section class="rounded-2xl border border-base-300 bg-base-100 p-4 md:p-5">
                    <h1 class="text-xl font-semibold">Booking Meja</h1>
                    <p class="mt-0.5 text-sm text-base-content/70">Isi detail reservasi dan pilih menu yang ingin dipesan.</p>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Pilih Meja</legend>
                            <select name="table_id" class="select select-bordered w-full" required>
                                <option value="">-- Pilih Meja --</option>
                                @foreach ($tables as $table)
                                    <option value="{{ $table->id }}" @selected(old('table_id') === (string) $table->id)>
                                        {{ $table->code }} — Kapasitas {{ $table->capacity }} orang
                                    </option>
                                @endforeach
                            </select>
                            @error('table_id')<p class="label text-error">{{ $message }}</p>@enderror
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Jumlah Orang</legend>
                            <input type="number" name="pax" min="1" max="30" class="input input-bordered w-full"
                                value="{{ old('pax', 2) }}" required>
                            @error('pax')<p class="label text-error">{{ $message }}</p>@enderror
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Waktu Reservasi</legend>
                            <input type="datetime-local" name="reservation_at" class="input input-bordered w-full"
                                value="{{ old('reservation_at') }}" required>
                            @error('reservation_at')<p class="label text-error">{{ $message }}</p>@enderror
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Catatan Reservasi</legend>
                            <textarea name="notes" rows="2" class="textarea textarea-bordered w-full"
                                placeholder="opsional">{{ old('notes') }}</textarea>
                        </fieldset>
                    </div>
                </section>

                {{-- Pilih Menu --}}
                <section class="space-y-4 rounded-2xl border border-base-300 bg-base-100 p-4 md:p-5">
                    <h2 class="font-semibold">Pilih Menu</h2>

                    {{-- Filter Kategori --}}
                    <div class="flex flex-wrap gap-2">
                        <button type="button"
                            @click="activeCategory = null"
                            :class="activeCategory === null ? 'btn-primary' : 'btn-ghost border border-base-300'"
                            class="btn btn-sm rounded-full">
                            Semua
                            <span class="badge badge-sm">{{ $menus->count() }}</span>
                        </button>
                        @foreach ($categories as $category)
                            <button type="button"
                                @click="activeCategory = {{ $category->id }}"
                                :class="activeCategory === {{ $category->id }} ? 'btn-primary' : 'btn-ghost border border-base-300'"
                                class="btn btn-sm rounded-full">
                                {{ $category->name }}
                                <span class="badge badge-sm">{{ $category->menus_count }}</span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Search --}}
                    <div class="relative">
                        <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></i>
                        <input type="text" x-model="search" class="input input-bordered w-full pl-10"
                            placeholder="Cari nama menu...">
                    </div>

                    {{-- Grid Menu --}}
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <template x-for="menu in filteredMenus" :key="menu.id">
                            <article class="overflow-hidden rounded-2xl border border-base-200 bg-base-100 shadow-sm">
                                <div class="aspect-[4/3] bg-base-200">
                                    <template x-if="menu.image_url">
                                        <img :src="menu.image_url" :alt="menu.name"
                                            class="h-full w-full rounded-2xl object-cover p-1">
                                    </template>
                                    <template x-if="!menu.image_url">
                                        <div class="flex h-full items-center justify-center text-base-content/40">
                                            <i class="ri-image-line text-4xl"></i>
                                        </div>
                                    </template>
                                </div>
                                <div class="space-y-2 p-3">
                                    <div>
                                        <p class="line-clamp-1 font-semibold" x-text="menu.name"></p>
                                        <p class="text-xs text-base-content/60" x-text="menu.category_name"></p>
                                    </div>
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="font-semibold"
                                            x-text="'Rp ' + menu.price.toLocaleString('id-ID')"></p>
                                        <button type="button" @click="addItem(menu)"
                                            class="btn btn-sm btn-neutral btn-square">
                                            <i class="ri-add-line text-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </template>

                        <template x-if="filteredMenus.length === 0">
                            <div class="col-span-full rounded-2xl border border-dashed border-base-300 p-8 text-center text-base-content/50">
                                Menu tidak ditemukan.
                            </div>
                        </template>
                    </div>
                </section>
            </div>

            {{-- Kanan: Order Details --}}
            <aside class="xl:col-span-5 xl:sticky xl:top-4">
                <section class="rounded-2xl border border-base-300 bg-base-100 p-4">
                    <div class="mb-4 flex items-center justify-between gap-2">
                        <h3 class="text-xl font-semibold">Order Details</h3>
                        <button type="button" @click="items = []" class="btn btn-sm btn-outline"
                            x-show="items.length > 0">
                            <i class="ri-delete-bin-line"></i> Reset
                        </button>
                    </div>

                    {{-- Empty state --}}
                    <template x-if="items.length === 0">
                        <div class="rounded-xl border border-dashed border-base-300 p-6 text-center text-sm text-base-content/50">
                            Belum ada menu dipilih.<br>Klik tombol <span class="font-semibold">+</span> pada menu di kiri.
                        </div>
                    </template>

                    {{-- Item list --}}
                    <div class="space-y-3" x-show="items.length > 0">
                        <template x-for="(item, index) in items" :key="item.id">
                            <article class="rounded-xl border border-base-300 p-3">
                                {{-- Hidden inputs --}}
                                <input type="hidden" :name="`items[${index}][menu_id]`" :value="item.id">
                                <input type="hidden" :name="`items[${index}][qty]`" :value="item.qty">
                                <input type="hidden" :name="`items[${index}][notes]`" :value="item.notes">

                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-base-200">
                                            <template x-if="item.image_url">
                                                <img :src="item.image_url" :alt="item.name"
                                                    class="h-full w-full object-cover">
                                            </template>
                                            <template x-if="!item.image_url">
                                                <div class="flex h-full items-center justify-center text-base-content/40">
                                                    <i class="ri-image-line"></i>
                                                </div>
                                            </template>
                                        </div>
                                        <div>
                                            <p class="font-medium leading-tight" x-text="item.name"></p>
                                            <p class="mt-0.5 text-sm text-base-content/60"
                                                x-text="'Rp ' + item.price.toLocaleString('id-ID')"></p>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeItem(index)"
                                        class="btn btn-sm btn-error btn-square text-white">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>

                                {{-- Catatan --}}
                                <div class="mt-2">
                                    <input type="text" x-model="item.notes"
                                        class="input input-bordered input-sm w-full"
                                        placeholder="Catatan (opsional)" @input="items[index].notes = $event.target.value">
                                </div>

                                {{-- Qty --}}
                                <div class="mt-2 flex items-center justify-end gap-2">
                                    <button type="button" @click="decrement(index)"
                                        class="btn btn-sm btn-outline btn-square">
                                        <i class="ri-subtract-line"></i>
                                    </button>
                                    <span class="min-w-8 text-center text-lg font-semibold" x-text="item.qty"></span>
                                    <button type="button" @click="increment(index)"
                                        class="btn btn-sm btn-outline btn-square">
                                        <i class="ri-add-line"></i>
                                    </button>
                                </div>
                            </article>
                        </template>
                    </div>

                    {{-- Subtotal + Submit --}}
                    <div class="mt-4 border-t border-base-300 pt-4" x-show="items.length > 0">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-base-content/70">Total Item</span>
                            <span class="font-medium" x-text="totalQty"></span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-lg font-semibold">
                            <span>Sub Total</span>
                            <span x-text="'Rp ' + subtotal.toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2">
                        <button type="submit"
                            :disabled="items.length === 0"
                            :class="items.length === 0 ? 'btn-disabled' : 'btn-primary'"
                            class="btn w-full">
                            <i class="ri-calendar-check-line"></i>
                            Kirim Reservasi
                        </button>
                        <a href="{{ route('customer.dashboard') }}" class="btn btn-ghost w-full">Batal</a>
                    </div>
                </section>
            </aside>
        </div>
    </form>

    <script>
        function bookingForm(allMenus) {
            return {
                allMenus,
                items: [],
                search: '',
                activeCategory: null,

                get filteredMenus() {
                    return this.allMenus.filter(m => {
                        const matchCat = this.activeCategory === null || m.category_id === this.activeCategory;
                        const s = this.search.toLowerCase();
                        const matchSearch = s === '' || m.name.toLowerCase().includes(s);
                        return matchCat && matchSearch;
                    });
                },

                get totalQty() {
                    return this.items.reduce((sum, i) => sum + i.qty, 0);
                },

                get subtotal() {
                    return this.items.reduce((sum, i) => sum + i.price * i.qty, 0);
                },

                addItem(menu) {
                    const existing = this.items.find(i => i.id === menu.id);
                    if (existing) {
                        existing.qty++;
                    } else {
                        this.items.push({ ...menu, qty: 1, notes: '' });
                    }
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                increment(index) {
                    this.items[index].qty++;
                },

                decrement(index) {
                    if (this.items[index].qty > 1) {
                        this.items[index].qty--;
                    } else {
                        this.removeItem(index);
                    }
                },
            };
        }
    </script>
</x-customer-layout>
