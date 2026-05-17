<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Kode Meja</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="code" required>
                @error('code')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Nama Meja</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="name">
                @error('name')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Kapasitas</legend>
                <input type="number" min="1" class="input input-bordered w-full" wire:model.defer="capacity"
                    required>
                @error('capacity')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Status</legend>
                <select class="select select-bordered w-full" wire:model.defer="table_status_id" required>
                    <option value="">Pilih status meja</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
                @error('table_status_id')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Kategori Meja</legend>
                <select class="select select-bordered w-full" wire:model.defer="table_category_id">
                    <option value="">Tanpa kategori</option>
                    @foreach ($categoryOptions as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('table_category_id')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Catatan</legend>
                <textarea class="textarea textarea-bordered w-full" rows="4" wire:model.defer="notes"></textarea>
                @error('notes')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                {{ $table ? 'Update' : 'Simpan' }}
            </button>
            <a href="{{ route('tables.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
