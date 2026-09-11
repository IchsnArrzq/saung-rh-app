<form wire:submit="save" class="space-y-6">
    <x-card title="Detail kategori"
        description="Kategori tampil sebagai pilihan saat tamu mengirim permintaan khusus dari panel mejanya.">
        <div class="grid gap-4 md:grid-cols-2">
            <x-input label="Nama kategori" name="name" wire:model="name" maxlength="80" required
                placeholder="Mis. Perayaan" />

            <x-input label="Urutan tampil" name="sort_order" type="number" min="0" max="9999" inputmode="numeric"
                wire:model="sort_order" hint="Angka kecil tampil lebih dulu. Kosongkan untuk menaruhnya paling akhir." />

            <x-textarea label="Keterangan untuk staf" name="description" wire:model="description" :rows="2"
                maxlength="255" field-class="md:col-span-2" hint="Tidak ditampilkan ke tamu." />

            <div class="md:col-span-2">
                <x-checkbox name="is_active" wire:model="is_active" label="Tampilkan di panel meja tamu"
                    hint="Kategori nonaktif tidak bisa dipilih tamu. Permintaan lama tetap menyimpan kategorinya." />
            </div>
        </div>
    </x-card>

    <x-card title="Ikon" description="Membantu tamu dan pelayan mengenali jenis permintaan sekilas.">
        <fieldset>
            <legend class="sr-only">Pilih ikon kategori</legend>

            <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-8">
                @foreach ($icons as $iconClass => $iconLabel)
                    <label wire:key="icon-choice-{{ $iconClass }}"
                        class="flex min-h-20 cursor-pointer flex-col items-center justify-center gap-1 rounded-lg bg-base-200 p-2 text-center text-xs ring-1 ring-transparent transition-colors hover:bg-base-300 has-checked:bg-primary/10 has-checked:text-primary has-checked:ring-primary has-focus-visible:ring-2 has-focus-visible:ring-primary/50">
                        <input type="radio" name="icon" value="{{ $iconClass }}" wire:model="icon" class="sr-only">
                        <i class="{{ $iconClass }} text-2xl" aria-hidden="true"></i>
                        <span>{{ $iconLabel }}</span>
                    </label>
                @endforeach
            </div>

            @error('icon')
                <p class="mt-2 text-xs text-error">{{ $message }}</p>
            @enderror
        </fieldset>

        {{-- Pratinjau hidup: $wire.name/$wire.icon adalah nilai di browser, belum dikirim ke server. --}}
        <div class="mt-5 flex flex-wrap items-center gap-3">
            <span class="text-sm text-base-content/70">Tampilan di panel tamu:</span>
            <span class="inline-flex min-h-11 items-center gap-2 rounded-lg bg-primary/10 px-3 text-sm font-medium text-primary ring-1 ring-primary">
                <i class="text-lg" x-bind:class="$wire.icon || '{{ \App\Models\SpecialRequestCategory::DEFAULT_ICON }}'" aria-hidden="true"></i>
                <span x-text="$wire.name.trim() || 'Nama kategori'">{{ $name !== '' ? $name : 'Nama kategori' }}</span>
            </span>
        </div>
    </x-card>

    <x-form-actions :cancel-href="route('special-request-categories.index')" loading="save"
        :submit-label="$category ? 'Simpan perubahan' : 'Tambah kategori'" />
</form>
