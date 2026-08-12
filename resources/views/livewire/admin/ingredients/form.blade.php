<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <x-input field-class="md:col-span-2" label="Nama Bahan" name="name" :required="true"
                wire:model.defer="name" required />

            <x-input label="Satuan" name="unit" wire:model.defer="unit"
                placeholder="gram, ml, butir, buah, liter..." />

            <x-input label="Harga per Satuan (Rp)" name="cost_per_unit" type="number" step="0.01" min="0"
                wire:model.defer="cost_per_unit" placeholder="Opsional" />

            <x-input label="Stok Awal" name="stock" type="number" step="0.001" min="0"
                wire:model.defer="stock" />

            <x-input label="Stok Minimum (alert)" name="min_stock" type="number" step="0.001" min="0"
                wire:model.defer="min_stock" hint="Stok di bawah angka ini akan ditandai Rendah." />

            <div class="md:col-span-2">
                <x-checkbox label="Bahan aktif digunakan" name="is_active" size="sm" wire:model="is_active" />
            </div>
        </div>

        <x-form-actions :submit-label="$ingredient ? 'Update' : 'Simpan'" :cancel-href="route('ingredients.index')"
            loading="save" />
    </form>
</div>
