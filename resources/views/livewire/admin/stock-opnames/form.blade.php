<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Bahan</legend>
                <select class="select select-bordered w-full" wire:model.defer="ingredient_id" required>
                    <option value="">-- Pilih Bahan --</option>
                    @foreach ($ingredients as $ingredient)
                        <option value="{{ $ingredient->id }}">
                            {{ $ingredient->name }} (stok: {{ number_format((float) $ingredient->stock, 3, ',', '.') }} {{ $ingredient->unit }})
                        </option>
                    @endforeach
                </select>
                @error('ingredient_id')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Tipe</legend>
                <select class="select select-bordered w-full" wire:model.defer="type">
                    <option value="in">Penambahan Stok (Masuk)</option>
                    <option value="adjustment">Koreksi / Stok Fisik</option>
                </select>
                @error('type')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Jumlah</legend>
                <input type="number" step="0.001" min="0.001" class="input input-bordered w-full"
                    wire:model.defer="qty" placeholder="0.000">
                <p class="label text-stone-400 text-xs">
                    Untuk "Koreksi", isi dengan stok fisik aktual (bukan selisih)
                </p>
                @error('qty')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Catatan</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="notes"
                    placeholder="Contoh: Pembelian dari supplier A">
                @error('notes')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                Simpan
            </button>
            <a href="{{ route('stock-opnames.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
