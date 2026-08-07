<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <x-input field-class="md:col-span-2" label="Nama Menu" name="name" :required="true"
                wire:model.defer="name" required />

            <x-select label="Kategori" name="menu_category_id" placeholder="Tanpa kategori"
                wire:model.defer="menu_category_id">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </x-select>

            <x-input label="Harga" name="price" type="number" step="0.01" min="0" :required="true"
                wire:model.defer="price" required />

            <x-input label="Slug" name="slug" wire:model.defer="slug" placeholder="otomatis jika kosong" />

            <x-input label="SKU" name="sku" wire:model.defer="sku" />

            <x-input field-class="md:col-span-2" label="URL Gambar" name="image_url"
                wire:model.defer="image_url" placeholder="https://..." />

            <x-textarea field-class="md:col-span-2" label="Deskripsi" name="description" :rows="4"
                wire:model.defer="description" />

            <div class="md:col-span-2">
                <x-checkbox label="Menu tersedia" name="is_available" size="sm" wire:model="is_available" />
            </div>
        </div>

        {{-- ============ MEDIA (gambar & video) ============ --}}
        <x-card class="space-y-5 bg-base-200/60">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold"><i class="ri-image-line"></i> Media</h3>
                <span class="text-xs text-base-content/60">Gambar bisa lebih dari satu, plus video.</span>
            </div>

            {{-- Gambar tersimpan (mode edit) --}}
            @if ($existingImages->isNotEmpty())
                <div>
                    <p class="mb-2 text-sm font-medium text-base-content/70">Gambar tersimpan</p>
                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6">
                        @foreach ($existingImages as $image)
                            <div wire:key="ex-img-{{ $image->id }}"
                                class="group relative overflow-hidden rounded-xl border {{ $image->is_primary ? 'border-accent ring-2 ring-accent' : 'border-base-300' }}">
                                <img src="{{ $image->url }}" alt="{{ $image->original_name }}"
                                    class="aspect-square w-full object-cover">
                                @if ($image->is_primary)
                                    <x-badge color="success" size="xs" class="absolute left-1 top-1 text-white">
                                        Utama
                                    </x-badge>
                                @endif
                                <div class="absolute inset-x-0 bottom-0 flex justify-between gap-1 bg-black/50 p-1">
                                    @unless ($image->is_primary)
                                        <x-button variant="success" size="xs" icon="ri-star-line" class="text-white"
                                            label="Jadikan gambar utama" wire:click="setPrimary('{{ $image->id }}')" />
                                    @endunless
                                    <x-button variant="error" size="xs" icon="ri-delete-bin-line"
                                        class="ml-auto text-white" label="Hapus gambar"
                                        data-confirm="Hapus gambar ini?"
                                        wire:click="deleteMedia('{{ $image->id }}')" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Preview gambar baru (belum disimpan) --}}
            @if (count($newImages))
                <div>
                    <p class="mb-2 text-sm font-medium text-base-content/70">Gambar baru (akan disimpan saat form disimpan)</p>
                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6">
                        @foreach ($newImages as $index => $file)
                            <div wire:key="new-img-{{ $index }}"
                                class="group relative overflow-hidden rounded-xl border border-dashed border-accent">
                                <img src="{{ $file->temporaryUrl() }}" class="aspect-square w-full object-cover">
                                <x-button variant="error" size="xs" icon="ri-close-line"
                                    class="absolute right-1 top-1 text-white" label="Batalkan gambar ini"
                                    wire:click="removeNewImage({{ $index }})" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <x-field label="Tambah Gambar (bisa pilih beberapa)" name="newImages.*"
                hint="JPG, PNG, WEBP — maks 4 MB / gambar.">
                <input type="file" class="file-input file-input-bordered w-full" accept="image/*" multiple
                    wire:model="newImages">
                <div wire:loading wire:target="newImages" class="mt-1 text-xs text-base-content/60">
                    <x-spinner size="xs" /> Memuat pratinjau...
                </div>
            </x-field>

            {{-- Video tersimpan (mode edit) --}}
            @if ($existingVideos->isNotEmpty())
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($existingVideos as $video)
                        <div wire:key="ex-vid-{{ $video->id }}" class="rounded-xl border border-base-300 p-2">
                            <video src="{{ $video->url }}" controls class="w-full rounded-lg"></video>
                            <div class="mt-2 flex items-center justify-between gap-2">
                                <span class="truncate text-xs text-base-content/60">{{ $video->original_name }}</span>
                                <x-button variant="error" size="xs" icon="ri-delete-bin-line" class="text-white"
                                    label="Hapus video" data-confirm="Hapus video ini?"
                                    wire:click="deleteMedia('{{ $video->id }}')" />
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <x-field label="Tambah Video" name="newVideo" hint="MP4 atau WEBM — maks 50 MB.">
                <input type="file" class="file-input file-input-bordered w-full" accept="video/mp4,video/webm"
                    wire:model="newVideo">
                <div wire:loading wire:target="newVideo" class="mt-1 text-xs text-base-content/60">
                    <x-spinner size="xs" /> Mengunggah...
                </div>
                @if ($newVideo)
                    <div class="mt-2 flex items-center justify-between gap-2 rounded-lg border border-dashed border-accent px-3 py-2">
                        <span class="truncate text-xs text-base-content/80">
                            <i class="ri-film-line"></i> {{ $newVideo->getClientOriginalName() }} (akan disimpan)
                        </span>
                        <x-button variant="ghost" size="xs" icon="ri-close-line" class="text-error"
                            label="Batalkan video" wire:click="removeNewVideo" />
                    </div>
                @endif
            </x-field>
        </x-card>

        <x-form-actions :submit-label="$menu ? 'Update' : 'Simpan'" :cancel-href="route('menus.index')"
            loading="save" />
    </form>
</div>
