<x-customer-layout>
    <section class="rounded-2xl border border-stone-200 bg-white p-5 md:p-6">
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-stone-900">Cart Meja {{ $table->code }}</h1>
                <p class="mt-1 text-sm text-stone-600">Periksa item pesanan sebelum dikirim ke dapur.</p>
            </div>

            <div class="ml-auto flex items-center gap-2">
                <a href="{{ route('customer.menus.index', ['table_id' => $table->id]) }}" class="btn btn-sm btn-ghost">Kembali ke Menu</a>
                <a href="{{ route('customer.menus.tables') }}" class="btn btn-sm btn-outline">Ganti Meja</a>
            </div>
        </div>
    </section>

    @if ($cartItems->isEmpty())
        <section class="mt-5 rounded-2xl border border-dashed border-stone-300 bg-white p-5 text-center">
            <p class="text-sm text-stone-600">Cart masih kosong. Tambahkan menu dulu.</p>
            <a href="{{ route('customer.menus.index', ['table_id' => $table->id]) }}"
                class="btn btn-sm mt-3 bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                Pilih Menu
            </a>
        </section>
    @else
        <section class="mt-5 overflow-x-auto rounded-2xl border border-stone-200 bg-white">
            <table class="table">
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th>Harga</th>
                        <th class="w-56">Qty & Catatan</th>
                        <th>Subtotal</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cartItems as $item)
                        <tr>
                            <td>
                                <p class="font-semibold text-stone-900">{{ $item['name'] }}</p>
                                <p class="text-xs text-stone-500">ID Menu: {{ $item['menu_id'] }}</p>
                            </td>
                            <td>Rp {{ number_format((float) $item['price'], 0, ',', '.') }}</td>
                            <td>
                                <form action="{{ route('customer.menus.cart.update', $item['menu_id']) }}" method="POST"
                                    class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="table_id" value="{{ $table->id }}">
                                    <input type="number" name="qty" min="1" max="50"
                                        class="input input-bordered input-sm w-full"
                                        value="{{ $item['qty'] }}" required>
                                    <input type="text" name="notes" class="input input-bordered input-sm w-full"
                                        value="{{ $item['notes'] }}" placeholder="Catatan item">
                                    <button type="submit" class="btn btn-sm btn-ghost">Update</button>
                                </form>
                            </td>
                            <td>
                                Rp {{ number_format(((float) $item['price']) * ((int) $item['qty']), 0, ',', '.') }}
                            </td>
                            <td class="text-right">
                                <form action="{{ route('customer.menus.cart.destroy', $item['menu_id']) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="table_id" value="{{ $table->id }}">
                                    <button type="submit" class="btn btn-sm btn-error text-white"
                                        onclick="return confirm('Hapus item ini dari cart?')">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="mt-5 rounded-2xl border border-stone-200 bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm text-stone-600">Total item: {{ $cartCount }}</p>
                    <p class="text-xl font-bold text-emerald-800">Total Rp {{ number_format($subtotal, 0, ',', '.') }}</p>
                </div>

                <form action="{{ route('customer.menus.cart.checkout') }}" method="POST" class="w-full max-w-lg space-y-3">
                    @csrf
                    <input type="hidden" name="table_id" value="{{ $table->id }}">
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Catatan Order</legend>
                        <textarea name="notes" class="textarea textarea-bordered w-full" rows="2"
                            placeholder="contoh: utamakan menu tanpa pedas"></textarea>
                    </fieldset>
                    <button type="submit" class="btn w-full bg-emerald-800 text-amber-50 hover:bg-emerald-700"
                        onclick="return confirm('Kirim pesanan ini ke admin?')">
                        Buat Order
                    </button>
                </form>
            </div>
        </section>
    @endif
</x-customer-layout>
