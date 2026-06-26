<div>
    @if (session('success'))
        <div class="mb-4 rounded-xl border border-success/30 bg-success/10 px-4 py-3 text-sm font-medium text-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-error/30 bg-error/10 px-4 py-3 text-sm text-error">
            <p class="font-semibold">Periksa input checkout:</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-3xl border border-base-300 bg-base-100 p-5 md:p-6">
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <h1 class="text-2xl font-semibold">Cart Pesanan</h1>
                <p class="mt-1 text-sm text-base-content/70">Pesan langsung ke dapur untuk meja Anda (dine-in).</p>
            </div>

            <a href="{{ route('public.menu', ['table_id' => $tableId]) }}" class="btn btn-sm btn-ghost ml-auto">
                <i class="ri-arrow-left-line"></i> Kembali ke Menu
            </a>
        </div>

        <div class="mt-4 rounded-2xl border border-info/30 bg-info/10 p-4 text-sm text-base-content/80">
            <p class="font-semibold"><i class="ri-information-line"></i> Pesan Sekarang (Dine-in)</p>
            <p class="mt-1">Pilih menu, pastikan meja Anda terpilih, lalu kirim pesanan. Order langsung masuk ke dapur.</p>
            <p class="mt-2">
                Ingin <span class="font-semibold">reservasi meja untuk nanti</span>?
                <a href="{{ route('login') }}" class="link link-primary font-semibold">Masuk / Daftar</a>
                untuk membuat booking.
            </p>
        </div>
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-[1.2fr_1fr]">
        <article class="rounded-3xl border border-base-300 bg-base-100 p-5">
            <h2 class="text-lg font-semibold">List Menu di Cart</h2>

            <div class="mt-4 space-y-3">
                @forelse ($cartItems as $item)
                    <div class="rounded-2xl border border-base-300 p-3">
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
                            <button type="button" wire:click="decrementQty('{{ $item['menu_id'] }}')" class="btn btn-sm btn-outline btn-square">
                                <i class="ri-subtract-line"></i>
                            </button>
                            <span class="w-8 text-center text-sm font-semibold">{{ $item['qty'] }}</span>
                            <button type="button" wire:click="incrementQty('{{ $item['menu_id'] }}')" class="btn btn-sm btn-outline btn-square">
                                <i class="ri-add-line"></i>
                            </button>
                            <button type="button" wire:click="removeItem('{{ $item['menu_id'] }}')" class="btn btn-sm btn-error btn-square text-white ml-auto">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-base-300 p-4 text-sm text-base-content/60">
                        Cart masih kosong. Silakan pilih menu dulu.
                    </p>
                @endforelse
            </div>
        </article>

        <article class="rounded-3xl border border-base-300 bg-base-100 p-5">
            <h2 class="text-lg font-semibold">Checkout</h2>
            <p class="mt-1 text-sm text-base-content/70">
                Total Estimasi: <span class="font-semibold text-base-content">Rp {{ number_format((float) $subtotal, 0, ',', '.') }}</span>
            </p>

            <div class="mt-4 space-y-4">
                <div>
                    <p class="text-sm font-semibold">Pilih Meja</p>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        @foreach ($tables as $table)
                            <button type="button" wire:click="selectTable('{{ $table->id }}')"
                                class="rounded-xl border p-3 text-left text-sm transition {{ (string) $tableId === (string) $table->id ? 'border-primary bg-primary/10' : 'border-base-300 bg-base-100 hover:border-primary/50' }}">
                                <p class="font-semibold">{{ $table->code }}</p>
                                <p class="text-xs text-base-content/60">{{ $table->capacity }} orang</p>
                            </button>
                        @endforeach
                    </div>
                </div>

                <label class="form-control w-full">
                    <span class="label-text mb-1">Nama Pemesan (opsional)</span>
                    <input type="text" wire:model="customerName" class="input input-bordered w-full" placeholder="Nama Anda">
                </label>

                <label class="form-control w-full">
                    <span class="label-text mb-1">Catatan Pesanan</span>
                    <textarea wire:model="notes" rows="3" class="textarea textarea-bordered w-full" placeholder="opsional"></textarea>
                </label>

                <button type="button" wire:click="checkout" wire:confirm="Kirim pesanan ini ke dapur?"
                    class="btn btn-primary w-full">
                    <i class="ri-send-plane-2-line"></i> Kirim Pesanan ke Dapur
                </button>
            </div>
        </article>
    </section>
</div>
