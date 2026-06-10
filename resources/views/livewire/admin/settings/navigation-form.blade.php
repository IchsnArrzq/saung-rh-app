<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <section class="rounded-2xl border border-base-300 bg-base-100 p-5">
            <h3 class="text-lg font-semibold">Pilih Tampilan Navigasi</h3>
            <p class="mt-1 text-sm text-base-content/70">
                Pilih salah satu mode navigasi. Jika memilih <strong>Sidebar</strong>, menu di navbar akan disembunyikan.
                Jika memilih <strong>Navbar</strong>, menu di sidebar akan disembunyikan.
            </p>

            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <label
                    @class([
                        'flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition',
                        'border-primary bg-primary/10' => $navigation_menu_preference === 'sidebar',
                        'border-base-300 bg-base-100 hover:border-primary/40' => $navigation_menu_preference !== 'sidebar',
                    ])>
                    <input type="radio" class="radio radio-primary mt-0.5" wire:model.live="navigation_menu_preference"
                        value="sidebar">
                    <span>
                        <span class="block font-semibold">Sidebar</span>
                        <span class="block text-sm text-base-content/70">Navigasi utama di panel samping kiri.</span>
                    </span>
                </label>

                <label
                    @class([
                        'flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition',
                        'border-primary bg-primary/10' => $navigation_menu_preference === 'navbar',
                        'border-base-300 bg-base-100 hover:border-primary/40' => $navigation_menu_preference !== 'navbar',
                    ])>
                    <input type="radio" class="radio radio-primary mt-0.5" wire:model.live="navigation_menu_preference"
                        value="navbar">
                    <span>
                        <span class="block font-semibold">Navbar</span>
                        <span class="block text-sm text-base-content/70">Navigasi utama di bar atas halaman.</span>
                    </span>
                </label>
            </div>

            @error('navigation_menu_preference')
                <p class="mt-3 text-sm text-error">{{ $message }}</p>
            @enderror
        </section>

        <div class="flex gap-2">
            <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">Simpan</button>
        </div>
    </form>
</div>
