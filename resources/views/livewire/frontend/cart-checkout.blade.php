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
                :href="route('public.menu')">
                Kembali ke Menu
            </x-button>
        </x-slot:actions>

        <div class="mt-4 rounded-xl border border-info/30 bg-info/10 p-4 text-sm text-base-content/80">
            <p class="font-semibold"><i class="ri-information-line"></i> Pesan Sekarang (Dine-in)</p>
            <p class="mt-1">Pesanan dikirim ke meja tempat Anda scan QR, setelah kasir mengonfirmasi meja itu. Order langsung masuk ke dapur.</p>
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
                            <x-menu-photo :src="$item['image_url']" :alt="$item['name']" compact
                                class="h-16 w-20 shrink-0 rounded-lg" />
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('public.menu.show', ['menu' => $item['menu_id']]) }}"
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
                                class="ml-auto text-error-content" label="Hapus {{ $item['name'] }}"
                                wire:click="removeItem('{{ $item['menu_id'] }}')"
                                data-confirm="Hapus item ini dari cart?" />
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="ri-shopping-cart-line" title="Cart masih kosong"
                        description="Silakan pilih menu dulu.">
                        <x-slot:actions>
                            <x-button variant="primary" size="sm" icon="ri-restaurant-line"
                                :href="route('public.menu')">
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
                {{-- Tidak ada pemilih meja: meja selalu milik sesi QR perangkat ini. --}}
                <div>
                    <p class="text-sm font-semibold">Meja</p>
                    @if ($tableSession?->isActive())
                        <p class="mt-1 text-sm">
                            Meja <span class="font-semibold tabular-nums">{{ $tableSession->table->code }}</span>
                            &middot; atas nama {{ $tableSession->customer_name }}
                        </p>
                    @elseif ($tableSession)
                        <x-alert type="warning" class="mt-2">
                            Meja {{ $tableSession->table->code }} masih menunggu konfirmasi kasir. Siapkan pesanan dulu, lalu kirim setelah disetujui.
                        </x-alert>
                    @else
                        <x-alert type="info" class="mt-2">
                            Scan QR yang ada di meja Anda untuk bisa mengirim pesanan.
                        </x-alert>
                    @endif
                </div>

                <x-input label="Nama Pemesan (opsional)" name="customerName" wire:model="customerName"
                    placeholder="Nama Anda" />

                <x-textarea label="Catatan Pesanan" name="notes" :rows="3" wire:model="notes"
                    placeholder="opsional" />

                <x-button variant="primary" :block="true" icon="ri-send-plane-2-line"
                    :disabled="! $tableSession?->isActive()"
                    wire:click="checkout" loading="checkout"
                    data-confirm="Kirim pesanan ini ke dapur?">
                    Kirim Pesanan ke Dapur
                </x-button>
            </div>
        </x-card>
    </section>
</div>
