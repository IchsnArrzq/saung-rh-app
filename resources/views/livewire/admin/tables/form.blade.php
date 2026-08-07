<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <x-input label="Kode Meja" name="code" :required="true" wire:model.defer="code" required />

            <x-input label="Nama Meja" name="name" wire:model.defer="name" />

            <x-input label="Kapasitas" name="capacity" type="number" min="1" :required="true"
                wire:model.defer="capacity" required />

            <x-select label="Status" name="status" :required="true" placeholder="Pilih status meja"
                wire:model.defer="status" required>
                @foreach ($statusOptions as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </x-select>

            <x-select label="Kategori Meja" name="table_category_id" placeholder="Tanpa kategori"
                wire:model.defer="table_category_id">
                @foreach ($categoryOptions as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </x-select>

            <x-textarea field-class="md:col-span-2" label="Catatan" name="notes" :rows="4"
                wire:model.defer="notes" />
        </div>

        <x-form-actions :submit-label="$table ? 'Update' : 'Simpan'" :cancel-href="route('tables.index')"
            loading="save" />
    </form>
</div>
