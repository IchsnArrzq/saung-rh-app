<div class="space-y-5">
    @include('admin.partials.flash')

    <div class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <p class="text-sm text-stone-500">
            Tentukan bahan baku dan jumlah yang dibutuhkan untuk <strong>1 porsi</strong> menu ini.
            Stok akan dikurangi otomatis setiap kali ada pembayaran yang berhasil.
        </p>
    </div>

    <form wire:submit="save" class="space-y-4">
        <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
            <table class="table">
                <thead>
                    <tr>
                        <th>Bahan</th>
                        <th>Jumlah (per 1 porsi)</th>
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $index => $row)
                        <tr wire:key="row-{{ $index }}">
                            <td class="w-2/3">
                                <select class="select select-bordered w-full" wire:model="rows.{{ $index }}.ingredient_id">
                                    <option value="">-- Pilih Bahan --</option>
                                    @foreach ($ingredients as $ingredient)
                                        <option value="{{ $ingredient->id }}">
                                            {{ $ingredient->name }} ({{ $ingredient->unit }})
                                        </option>
                                    @endforeach
                                </select>
                                @error("rows.{$index}.ingredient_id")
                                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                                @enderror
                            </td>
                            <td>
                                <input type="number" step="0.001" min="0.001"
                                    class="input input-bordered w-full"
                                    wire:model="rows.{{ $index }}.qty"
                                    placeholder="0.000">
                                @error("rows.{$index}.qty")
                                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                                @enderror
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-ghost text-error"
                                    wire:click="removeRow({{ $index }})">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-stone-400 py-6">
                                Belum ada bahan. Klik "+ Tambah Bahan" untuk menambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between">
            <button type="button" class="btn btn-sm btn-outline" wire:click="addRow">
                <i class="ri-add-line"></i> Tambah Bahan
            </button>

            <div class="flex gap-2">
                <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                    Simpan Resep
                </button>
                <a href="{{ route('menus.index') }}" class="btn btn-ghost">Kembali</a>
            </div>
        </div>
    </form>
</div>
