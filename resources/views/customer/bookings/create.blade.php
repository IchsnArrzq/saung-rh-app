<x-customer-layout>
    <section class="rounded-2xl border border-stone-200 bg-white p-5 md:p-6">
        <div class="mb-5">
            <h1 class="text-2xl font-semibold text-stone-900">Booking Meja</h1>
            <p class="mt-1 text-sm text-stone-600">Pilih meja, jumlah orang, menu, lalu kirim reservasi Anda.</p>
        </div>

        <form action="{{ route('customer.bookings.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-1.5">
                    <label for="table_id" class="block text-sm font-semibold text-stone-700">Pilih Meja</label>
                    <select id="table_id" name="table_id" class="select select-bordered w-full" required>
                        <option value="">Pilih meja</option>
                        @foreach ($tables as $table)
                            <option value="{{ $table->id }}" @selected((string) old('table_id') === (string) $table->id)>
                                {{ $table->code }} - Kapasitas {{ $table->capacity }} orang
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label for="pax" class="block text-sm font-semibold text-stone-700">Jumlah Orang</label>
                    <input id="pax" type="number" name="pax" min="1" max="30" class="input input-bordered w-full"
                        value="{{ old('pax', 2) }}" required>
                </div>

                <div class="space-y-1.5">
                    <label for="reservation_at" class="block text-sm font-semibold text-stone-700">Waktu Reservasi</label>
                    <input id="reservation_at" type="datetime-local" name="reservation_at" class="input input-bordered w-full"
                        value="{{ old('reservation_at') }}" required>
                </div>

                <div class="space-y-1.5">
                    <label for="notes" class="block text-sm font-semibold text-stone-700">Catatan Reservasi</label>
                    <textarea id="notes" name="notes" rows="3" class="textarea textarea-bordered w-full" placeholder="opsional">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="rounded-2xl border border-stone-200 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="font-semibold text-stone-900">Pilih Menu</h2>
                    <button type="button" id="add-booking-item" class="btn btn-sm btn-outline">Tambah Menu</button>
                </div>

                @php
                    $oldItems = old('items', [['menu_id' => '', 'qty' => 1, 'notes' => '']]);
                @endphp

                <div id="booking-items" class="space-y-3">
                    @foreach ($oldItems as $index => $item)
                        <div class="booking-item rounded-xl border border-stone-200 p-3" data-index="{{ $index }}">
                            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_160px_auto] md:items-end">
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-semibold text-stone-600">Menu</label>
                                    <select name="items[{{ $index }}][menu_id]" class="select select-bordered select-sm w-full" required>
                                    <option value="">Pilih menu</option>
                                    @foreach ($menus as $menu)
                                        <option value="{{ $menu->id }}" @selected((string) ($item['menu_id'] ?? '') === (string) $menu->id)>
                                            {{ $menu->name }} - Rp {{ number_format((float) $menu->price, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                    </select>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-xs font-semibold text-stone-600">Jumlah</label>
                                    <input type="number" min="1" max="20" name="items[{{ $index }}][qty]"
                                        value="{{ $item['qty'] ?? 1 }}" class="input input-bordered input-sm w-full" required>
                                </div>

                                <button type="button" class="btn btn-sm btn-error text-white remove-item">
                                    Hapus
                                </button>
                            </div>

                            <div class="mt-3 space-y-1.5">
                                <label class="block text-xs font-semibold text-stone-600">Catatan Menu</label>
                                <input type="text" name="items[{{ $index }}][notes]" value="{{ $item['notes'] ?? '' }}"
                                    class="input input-bordered input-sm w-full" placeholder="contoh: kurang gula / ekstra pedas">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">Kirim Reservasi</button>
                <a href="{{ route('customer.dashboard') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </section>

    <template id="booking-item-template">
        <div class="booking-item rounded-xl border border-stone-200 p-3" data-index="__INDEX__">
            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_160px_auto] md:items-end">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-stone-600">Menu</label>
                    <select name="items[__INDEX__][menu_id]" class="select select-bordered select-sm w-full" required>
                    <option value="">Pilih menu</option>
                    @foreach ($menus as $menu)
                        <option value="{{ $menu->id }}">{{ $menu->name }} - Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</option>
                    @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-stone-600">Jumlah</label>
                    <input type="number" min="1" max="20" name="items[__INDEX__][qty]" value="1" class="input input-bordered input-sm w-full" required>
                </div>

                <button type="button" class="btn btn-sm btn-error text-white remove-item">Hapus</button>
            </div>

            <div class="mt-3 space-y-1.5">
                <label class="block text-xs font-semibold text-stone-600">Catatan Menu</label>
                <input type="text" name="items[__INDEX__][notes]" class="input input-bordered input-sm"
                    placeholder="contoh: kurang gula / ekstra pedas">
            </div>
        </div>
    </template>

    <script>
        (() => {
            const list = document.getElementById('booking-items');
            const template = document.getElementById('booking-item-template');
            const addButton = document.getElementById('add-booking-item');

            if (!list || !template || !addButton) {
                return;
            }

            const nextIndex = () => {
                const items = [...list.querySelectorAll('.booking-item')];
                if (!items.length) {
                    return 0;
                }

                const max = Math.max(...items.map((item) => Number(item.dataset.index || 0)));
                return max + 1;
            };

            addButton.addEventListener('click', () => {
                const html = template.innerHTML.replaceAll('__INDEX__', nextIndex());
                list.insertAdjacentHTML('beforeend', html);
            });

            list.addEventListener('click', (event) => {
                if (!(event.target instanceof HTMLElement)) {
                    return;
                }

                if (!event.target.classList.contains('remove-item')) {
                    return;
                }

                const rows = list.querySelectorAll('.booking-item');
                if (rows.length <= 1) {
                    return;
                }

                event.target.closest('.booking-item')?.remove();
            });
        })();
    </script>
</x-customer-layout>
