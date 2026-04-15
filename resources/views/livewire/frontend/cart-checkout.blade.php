<div>
    @php
        $isOffline = $mode === 'offline';
    @endphp

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-semibold">Periksa input checkout:</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-3xl border border-stone-200 bg-white p-5 md:p-6">
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-stone-900">Cart Pesanan</h1>
                <p class="mt-1 text-sm text-stone-600">
                    {{ $isOffline ? 'Checkout offline dari scan QR meja.' : 'Checkout online untuk booking meja.' }}
                </p>
            </div>

            <div class="ml-auto flex items-center gap-2">
                <button type="button" wire:click="setMode('online')"
                    class="btn btn-sm {{ ! $isOffline ? 'bg-emerald-800 text-amber-50 hover:bg-emerald-700' : 'btn-ghost' }}">Online</button>
                <button type="button" wire:click="setMode('offline')"
                    class="btn btn-sm {{ $isOffline ? 'bg-emerald-800 text-amber-50 hover:bg-emerald-700' : 'btn-ghost' }}">Offline QR</button>
                <a href="{{ route('public.menu.index', ['mode' => $mode, 'table_id' => $tableId]) }}" class="btn btn-sm btn-ghost">Kembali ke Menu</a>
            </div>
        </div>

        <div class="mt-4 rounded-2xl border p-4 text-sm {{ $isOffline ? 'border-amber-200 bg-amber-50 text-stone-700' : 'border-emerald-200 bg-emerald-50 text-stone-700' }}">
            @if ($isOffline)
                <p class="font-semibold">Flow Offline (Scan QR Meja)</p>
                <p class="mt-1">Pilih menu di cart ini, pilih meja, lalu kirim pesanan. Order langsung masuk ke admin sebagai pesanan offline.</p>
            @else
                <p class="font-semibold">Flow Online Booking</p>
                <p class="mt-1">Pilih meja, tanggal, jumlah orang, lalu submit untuk membuat booking reservasi online.</p>
            @endif
        </div>
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-[1.2fr_1fr]">
        <article class="rounded-3xl border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-stone-900">List Menu di Cart</h2>

            <div class="mt-4 space-y-3">
                @forelse ($cartItems as $item)
                    <div class="rounded-2xl border border-stone-200 p-3">
                        <div class="flex items-start gap-3">
                            <img src="{{ $item['image_url'] ?: 'https://picsum.photos/seed/'.urlencode((string) $item['menu_id']).'/200/160' }}"
                                alt="{{ $item['name'] }}" class="h-16 w-20 rounded-lg object-cover">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-stone-900">{{ $item['name'] }}</p>
                                <p class="text-sm text-stone-500">Rp {{ number_format((float) $item['price'], 0, ',', '.') }}</p>
                                @if (! empty($item['notes']))
                                    <p class="mt-1 text-xs text-stone-500">Catatan: {{ $item['notes'] }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3 flex items-center gap-2">
                            <button type="button" wire:click="decrementQty('{{ $item['menu_id'] }}')" class="btn btn-sm btn-ghost">-</button>
                            <span class="w-8 text-center text-sm font-semibold">{{ $item['qty'] }}</span>
                            <button type="button" wire:click="incrementQty('{{ $item['menu_id'] }}')" class="btn btn-sm btn-ghost">+</button>
                            <button type="button" wire:click="removeItem('{{ $item['menu_id'] }}')" class="btn btn-sm btn-error text-white ml-auto">Hapus</button>
                        </div>
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-stone-300 p-4 text-sm text-stone-500">
                        Cart masih kosong. Silakan pilih menu dulu.
                    </p>
                @endforelse
            </div>
        </article>

        <article class="rounded-3xl border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-stone-900">Checkout</h2>
            <p class="mt-1 text-sm text-stone-600">Total Estimasi: <span class="font-semibold text-stone-900">Rp {{ number_format((float) $subtotal, 0, ',', '.') }}</span></p>

            <div class="mt-4 space-y-4">
                <div>
                    <p class="text-sm font-semibold text-stone-700">Pilih Meja (Card)</p>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        @foreach ($tables as $table)
                            <button type="button" wire:click="selectTable('{{ $table->id }}')"
                                class="rounded-xl border p-3 text-left text-sm {{ (string) $tableId === (string) $table->id ? 'border-emerald-700 bg-emerald-50' : 'border-stone-200 bg-white' }}">
                                <p class="font-semibold text-stone-900">{{ $table->code }}</p>
                                <p class="text-xs text-stone-500">{{ $table->capacity }} orang</p>
                            </button>
                        @endforeach
                    </div>
                </div>

                @if ($isOffline)
                    <label class="form-control">
                        <span class="label-text">Catatan Pesanan</span>
                        <textarea wire:model="notes" rows="3" class="textarea textarea-bordered" placeholder="opsional"></textarea>
                    </label>

                    <button type="button" wire:click="checkout" class="btn w-full bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                        Kirim Pesanan Offline
                    </button>
                @else
                    <label class="form-control">
                        <span class="label-text">Tanggal & Jam Reservasi</span>
                        <input type="datetime-local" wire:model="reservationAt" class="input input-bordered">
                    </label>

                    <label class="form-control">
                        <span class="label-text">Jumlah Orang</span>
                        <input type="number" wire:model="pax" min="1" max="30" class="input input-bordered">
                    </label>

                    <label class="form-control">
                        <span class="label-text">Nama Pemesan</span>
                        <input type="text" wire:model="customerName" class="input input-bordered">
                    </label>

                    <label class="form-control">
                        <span class="label-text">No. Telepon</span>
                        <input type="text" wire:model="phone" class="input input-bordered">
                    </label>

                    <label class="form-control">
                        <span class="label-text">Catatan Reservasi</span>
                        <textarea wire:model="notes" rows="3" class="textarea textarea-bordered" placeholder="opsional"></textarea>
                    </label>

                    <button type="button" wire:click="checkout" class="btn w-full bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                        Buat Booking Online
                    </button>
                @endif
            </div>
        </article>
    </section>
</div>
