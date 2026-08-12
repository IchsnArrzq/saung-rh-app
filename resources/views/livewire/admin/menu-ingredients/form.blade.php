<div class="space-y-5">
    @include('admin.partials.flash')

    <x-card>
        <p class="text-sm text-base-content/60">
            Tentukan bahan baku dan jumlah yang dibutuhkan untuk <strong>1 porsi</strong> menu ini.
            Stok akan dikurangi otomatis setiap kali ada pembayaran yang berhasil.
        </p>
    </x-card>

    <form wire:submit="save" class="space-y-4">
        <x-data-table :zebra="false">
            <x-slot:head>
                <tr>
                    <th>Bahan</th>
                    <th>Jumlah (per 1 porsi)</th>
                    <th class="w-10"></th>
                </tr>
            </x-slot:head>

            @forelse ($rows as $index => $row)
                <tr wire:key="row-{{ $index }}">
                    <td class="w-2/3">
                        <x-select :bare="true" class="w-full" label="Bahan baris {{ $index + 1 }}"
                            name="rows.{{ $index }}.ingredient_id" placeholder="-- Pilih Bahan --"
                            wire:model="rows.{{ $index }}.ingredient_id">
                            @foreach ($ingredients as $ingredient)
                                <option value="{{ $ingredient->id }}">
                                    {{ $ingredient->name }} ({{ $ingredient->unit }})
                                </option>
                            @endforeach
                        </x-select>
                        @error("rows.{$index}.ingredient_id")
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                        @enderror
                    </td>
                    <td>
                        <x-input :bare="true" type="number" step="0.001" min="0.001" class="w-full"
                            label="Jumlah baris {{ $index + 1 }}"
                            wire:model="rows.{{ $index }}.qty" placeholder="0.000" />
                        @error("rows.{$index}.qty")
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                        @enderror
                    </td>
                    <td>
                        <x-button variant="ghost" size="sm" shape="square" icon="ri-delete-bin-line"
                            class="text-error" label="Hapus baris {{ $index + 1 }}"
                            wire:click="removeRow({{ $index }})" />
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="py-6 text-center text-base-content/50">
                        Belum ada bahan. Klik "Tambah Bahan" untuk menambahkan.
                    </td>
                </tr>
            @endforelse
        </x-data-table>

        <div class="flex items-center justify-between">
            <x-button variant="outline" size="sm" icon="ri-add-line" wire:click="addRow">
                Tambah Bahan
            </x-button>

            <x-form-actions class="mt-0" submit-label="Simpan Resep" cancel-label="Kembali"
                :cancel-href="route('menus.index')" loading="save" />
        </div>
    </form>
</div>
