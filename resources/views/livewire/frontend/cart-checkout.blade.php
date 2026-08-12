<div>
    @if (session('success'))
        <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
    @endif

    @if ($errors->any())
        <x-alert type="error" title="Periksa input checkout:" class="mb-4">
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <x-page-header title="Cart Pesanan" description="Pesan langsung ke dapur untuk meja Anda (dine-in).">
        <x-slot:actions>
            <x-button variant="ghost" size="sm" icon="ri-arrow-left-line"
                :href="route('public.menu', ['table_id' => $tableId])">
                Kembali ke Menu
            </x-button>
        </x-slot:actions>

        <div class="mt-4 rounded-xl border border-info/30 bg-info/10 p-4 text-sm text-base-content/80">
            <p class="font-semibold"><i class="ri-information-line"></i> Pesan Sekarang (Dine-in)</p>
            <p class="mt-1">Pilih menu, pastikan meja Anda terpilih, lalu kirim pesanan. Order langsung masuk ke dapur.</p>
            <p class="mt-2">
                Ingin <span class="font-semibold">reservasi meja untuk nanti</span>?
                <a href="{{ route('login') }}" class="link link-primary font-semibold">Masuk / Daftar</a>
                untuk membuat booking.
            </p>
        </div>
    </x-page-header>

    <section class="mt-6 grid gap-6 lg:grid-cols-[1.2fr_1fr]">
        <x-card title="List Menu di Cart">
            <div class="mt-4 space-y-3">
                @forelse ($cartItems as $item)
                    <div class="rounded-xl border border-base-300 p-3">
                        <div class="flex items-start gap-3">
                            <div class="h-16 w-20 shrink-0 overflow-hidden rounded-lg bg-base-200">
                                @if ($item['image_url'])
                                    <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center text-base-content/40">
                                        <i class="ri-image-line text-xl"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('public.menu.show', ['menu' => $item['menu_id'], 'table_id' => $tableId]) }}"
                                    class="font-semibold hover:text-primary hover:underline">
                                    {{ $item['name'] }}
                                </a>
                                <p class="text-sm text-base-content/60">Rp {{ number_format((float) $item['price'], 0, ',', '.') }}</p>
                                @if (! empty($item['notes']))
                                    <p class="mt-1 text-xs text-base-content/50">Catatan: {{ $item['notes'] }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3 flex items-center gap-2">
                            <x-button variant="outline" size="sm" shape="square" icon="ri-subtract-line"
                                label="Kurangi jumlah {{ $item['name'] }}"
                                wire:click="decrementQty('{{ $item['menu_id'] }}')" />
                            <span class="w-8 text-center text-sm font-semibold">{{ $item['qty'] }}</span>
                            <x-button variant="outline" size="sm" shape="square" icon="ri-add-line"
                                label="Tambah jumlah {{ $item['name'] }}"
                                wire:click="incrementQty('{{ $item['menu_id'] }}')" />
                            <x-button variant="error" size="sm" shape="square" icon="ri-delete-bin-line"
                                class="ml-auto text-white" label="Hapus {{ $item['name'] }}"
                                wire:click="removeItem('{{ $item['menu_id'] }}')"
                                data-confirm="Hapus item ini dari cart?" />
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="ri-shopping-cart-line" title="Cart masih kosong"
                        description="Silakan pilih menu dulu.">
                        <x-slot:actions>
                            <x-button variant="primary" size="sm" icon="ri-restaurant-line"
                                :href="route('public.menu', ['table_id' => $tableId])">
                                Lihat Menu
                            </x-button>
                        </x-slot:actions>
                    </x-empty-state>
                @endforelse
            </div>
        </x-card>

        <x-card title="Checkout">
            <p class="mt-1 text-sm text-base-content/70">
                Total Estimasi: <span class="font-semibold text-base-content">Rp {{ number_format((float) $subtotal, 0, ',', '.') }}</span>
            </p>

            <div class="mt-4 space-y-4">
                <div>
                    <p class="text-sm font-semibold">Pilih Meja</p>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        @foreach ($tables as $table)
                            <button type="button" wire:click="selectTable('{{ $table->id }}')"
                                aria-pressed="{{ (string) $tableId === (string) $table->id ? 'true' : 'false' }}"
                                class="rounded-xl border p-3 text-left text-sm transition {{ (string) $tableId === (string) $table->id ? 'border-primary bg-primary/10' : 'border-base-300 bg-base-100 hover:border-primary/50' }}">
                                <p class="font-semibold">{{ $table->code }}</p>
                                <p class="text-xs text-base-content/60">{{ $table->capacity }} orang</p>
                            </button>
                        @endforeach
                    </div>
                </div>

                <x-input label="Nama Pemesan (opsional)" name="customerName" wire:model="customerName"
                    placeholder="Nama Anda" />

                <x-textarea label="Catatan Pesanan" name="notes" :rows="3" wire:model="notes"
                    placeholder="opsional" />

                <x-button variant="primary" :block="true" icon="ri-send-plane-2-line"
                    wire:click="checkout" loading="checkout"
                    data-confirm="Kirim pesanan ini ke dapur?">
                    Kirim Pesanan ke Dapur
                </x-button>
            </div>
        </x-card>
    </section>
</div>
