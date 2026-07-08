<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Nama Menu</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="name" required>
                @error('name')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Kategori</legend>
                <select class="select select-bordered w-full" wire:model.defer="menu_category_id">
                    <option value="">Tanpa kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('menu_category_id')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Harga</legend>
                <input type="number" step="0.01" min="0" class="input input-bordered w-full" wire:model.defer="price"
                    required>
                @error('price')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Slug</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="slug"
                    placeholder="otomatis jika kosong">
                @error('slug')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">SKU</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="sku">
                @error('sku')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">URL Gambar</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="image_url"
                    placeholder="https://...">
                @error('image_url')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Deskripsi</legend>
                <textarea class="textarea textarea-bordered w-full" rows="4" wire:model.defer="description"></textarea>
                @error('description')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Ketersediaan</legend>
                <label class="label cursor-pointer justify-start gap-3 px-0">
                    <input type="checkbox" class="checkbox checkbox-sm" wire:model="is_available">
                    <span class="label-text">Menu tersedia</span>
                </label>
                @error('is_available')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

        {{-- ============ MEDIA (gambar & video) ============ --}}
        <div class="space-y-5 rounded-2xl border border-stone-200 bg-stone-50/60 p-4 md:p-5">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-stone-800"><i class="ri-image-line"></i> Media</h3>
                <span class="text-xs text-stone-500">Gambar bisa lebih dari satu, plus video.</span>
            </div>

            {{-- Gambar tersimpan (mode edit) --}}
            @if ($existingImages->isNotEmpty())
                <div>
                    <p class="mb-2 text-sm font-medium text-stone-600">Gambar tersimpan</p>
                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6">
                        @foreach ($existingImages as $image)
                            <div wire:key="ex-img-{{ $image->id }}"
                                class="group relative overflow-hidden rounded-xl border {{ $image->is_primary ? 'border-emerald-600 ring-2 ring-emerald-600' : 'border-stone-200' }}">
                                <img src="{{ $image->url }}" alt="{{ $image->original_name }}"
                                    class="aspect-square w-full object-cover">
                                @if ($image->is_primary)
                                    <span class="absolute left-1 top-1 badge badge-success badge-xs text-white">Utama</span>
                                @endif
                                <div class="absolute inset-x-0 bottom-0 flex justify-between gap-1 bg-black/50 p-1 opacity-0 transition group-hover:opacity-100">
                                    @unless ($image->is_primary)
                                        <button type="button" class="btn btn-xs btn-success text-white"
                                            wire:click="setPrimary('{{ $image->id }}')" title="Jadikan utama">
                                            <i class="ri-star-line"></i>
                                        </button>
                                    @endunless
                                    <button type="button" class="btn btn-xs btn-error text-white ml-auto"
                                        data-confirm="Hapus gambar ini?"
                                        wire:click="deleteMedia('{{ $image->id }}')" title="Hapus">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Preview gambar baru (belum disimpan) --}}
            @if (count($newImages))
                <div>
                    <p class="mb-2 text-sm font-medium text-stone-600">Gambar baru (akan disimpan saat form disimpan)</p>
                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6">
                        @foreach ($newImages as $index => $file)
                            <div wire:key="new-img-{{ $index }}"
                                class="group relative overflow-hidden rounded-xl border border-dashed border-emerald-400">
                                <img src="{{ $file->temporaryUrl() }}" class="aspect-square w-full object-cover">
                                <button type="button"
                                    class="absolute right-1 top-1 btn btn-xs btn-error text-white opacity-0 transition group-hover:opacity-100"
                                    wire:click="removeNewImage({{ $index }})" title="Batalkan">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Tambah Gambar (bisa pilih beberapa)</legend>
                <input type="file" class="file-input file-input-bordered w-full" accept="image/*" multiple
                    wire:model="newImages">
                <p class="label text-xs text-stone-400">JPG, PNG, WEBP — maks 4 MB / gambar.</p>
                <div wire:loading wire:target="newImages" class="text-xs text-stone-500">
                    <span class="loading loading-spinner loading-xs"></span> Memuat pratinjau...
                </div>
                @error('newImages.*')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            {{-- Video tersimpan (mode edit) --}}
            @if ($existingVideos->isNotEmpty())
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($existingVideos as $video)
                        <div wire:key="ex-vid-{{ $video->id }}" class="rounded-xl border border-stone-200 p-2">
                            <video src="{{ $video->url }}" controls class="w-full rounded-lg"></video>
                            <div class="mt-2 flex items-center justify-between gap-2">
                                <span class="truncate text-xs text-stone-500">{{ $video->original_name }}</span>
                                <button type="button" class="btn btn-xs btn-error text-white"
                                    data-confirm="Hapus video ini?"
                                    wire:click="deleteMedia('{{ $video->id }}')">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Tambah Video</legend>
                <input type="file" class="file-input file-input-bordered w-full" accept="video/mp4,video/webm"
                    wire:model="newVideo">
                <p class="label text-xs text-stone-400">MP4 atau WEBM — maks 50 MB.</p>
                <div wire:loading wire:target="newVideo" class="text-xs text-stone-500">
                    <span class="loading loading-spinner loading-xs"></span> Mengunggah...
                </div>
                @if ($newVideo)
                    <div class="mt-2 flex items-center justify-between gap-2 rounded-lg border border-dashed border-emerald-400 px-3 py-2">
                        <span class="truncate text-xs text-stone-600">
                            <i class="ri-film-line"></i> {{ $newVideo->getClientOriginalName() }} (akan disimpan)
                        </span>
                        <button type="button" class="btn btn-xs btn-ghost text-error" wire:click="removeNewVideo">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                @endif
                @error('newVideo')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                {{ $menu ? 'Update' : 'Simpan' }}
            </button>
            <a href="{{ route('menus.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
