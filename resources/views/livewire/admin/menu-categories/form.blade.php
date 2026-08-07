<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4">
            <x-input label="Nama Kategori" name="name" :required="true" wire:model.defer="name" required />

            <x-input label="Slug" name="slug" wire:model.defer="slug" placeholder="otomatis jika kosong" />

            <x-textarea label="Deskripsi" name="description" :rows="4" wire:model.defer="description" />

            <x-checkbox label="Kategori aktif" name="is_active" size="sm" wire:model="is_active" />
        </div>

        <x-form-actions :submit-label="$menuCategory ? 'Update' : 'Simpan'"
            :cancel-href="route('menu-categories.index')" loading="save" />
    </form>
</div>
