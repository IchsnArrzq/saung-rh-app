<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <x-input field-class="md:col-span-2" label="Nama Supplier" name="name" :required="true"
                wire:model.defer="name" required />

            <x-input label="Kode" name="code" wire:model.defer="code" placeholder="Opsional, mis. SUP-001" />

            <x-input label="Nama Kontak (PIC)" name="contact_person" wire:model.defer="contact_person" />

            <x-input label="Telepon" name="phone" wire:model.defer="phone" />

            <x-input label="Email" name="email" type="email" wire:model.defer="email" />

            <x-textarea field-class="md:col-span-2" label="Alamat" name="address" :rows="2" wire:model.defer="address" />

            <x-textarea field-class="md:col-span-2" label="Catatan" name="notes" :rows="2" wire:model.defer="notes" />

            <div class="md:col-span-2">
                <x-checkbox label="Supplier aktif" name="is_active" size="sm" wire:model="is_active" />
            </div>
        </div>

        <x-form-actions :submit-label="$supplier ? 'Update' : 'Simpan'" :cancel-href="route('suppliers.index')"
            loading="save" />
    </form>
</div>
