<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <x-input label="Nama Kategori" name="name" :required="true" wire:model.defer="name" required />

            <x-input label="Slug" name="slug" wire:model.defer="slug" placeholder="otomatis jika kosong" />

            <x-input label="Urutan" name="sort_order" type="number" min="0" wire:model.defer="sort_order" />

            <x-checkbox label="Kategori aktif" name="is_active" size="sm" wire:model="is_active" />

            <x-textarea field-class="md:col-span-2" label="Deskripsi" name="description" :rows="4"
                wire:model.defer="description" />
        </div>

        <x-form-actions :submit-label="$tableCategory ? 'Update' : 'Simpan'"
            :cancel-href="route('table-categories.index')" loading="save" />
    </form>
</div>
