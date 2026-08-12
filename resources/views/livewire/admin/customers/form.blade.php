<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <x-input field-class="md:col-span-2" label="Nama Pelanggan" name="name" :required="true"
                wire:model.defer="name" required />

            <x-input label="Kode" name="code" wire:model.defer="code" placeholder="Opsional, mis. CUS-001" />

            <x-input label="Telepon" name="phone" wire:model.defer="phone" />

            <x-input field-class="md:col-span-2" label="Email" name="email" type="email" wire:model.defer="email" />

            <x-textarea field-class="md:col-span-2" label="Alamat" name="address" :rows="2" wire:model.defer="address" />

            <x-textarea field-class="md:col-span-2" label="Catatan" name="notes" :rows="2" wire:model.defer="notes" />

            <div class="md:col-span-2">
                <x-checkbox label="Pelanggan aktif" name="is_active" size="sm" wire:model="is_active" />
            </div>
        </div>

        <x-form-actions :submit-label="$customer ? 'Update' : 'Simpan'" :cancel-href="route('customers.index')"
            loading="save" />
    </form>
</div>
