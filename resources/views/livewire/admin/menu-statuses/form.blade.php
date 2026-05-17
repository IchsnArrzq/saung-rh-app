<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Nama Status</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="name" required>
                @error('name')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Key Status</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="key" placeholder="available"
                    @readonly($isReservedStatus) required>
                @if ($isReservedStatus)
                    <p class="label">Key status sistem tidak bisa diubah.</p>
                @endif
                @error('key')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Warna</legend>
                <select class="select select-bordered w-full" wire:model.defer="color">
                    <option value="success">Success</option>
                    <option value="error">Error</option>
                    <option value="warning">Warning</option>
                    <option value="info">Info</option>
                    <option value="neutral">Neutral</option>
                </select>
                @error('color')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Urutan</legend>
                <input type="number" min="0" class="input input-bordered w-full" wire:model.defer="sort_order">
                @error('sort_order')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Aktif</legend>
                <label class="label cursor-pointer justify-start gap-3 px-0">
                    <input type="checkbox" class="checkbox checkbox-sm" wire:model="is_active">
                    <span class="label-text">Status aktif</span>
                </label>
                @error('is_active')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Default</legend>
                <label class="label cursor-pointer justify-start gap-3 px-0">
                    <input type="checkbox" class="checkbox checkbox-sm" wire:model="is_default">
                    <span class="label-text">Jadikan default</span>
                </label>
                @error('is_default')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                {{ $menuStatus ? 'Update' : 'Simpan' }}
            </button>
            <a href="{{ route('menu-statuses.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
