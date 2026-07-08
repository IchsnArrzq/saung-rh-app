<div class="space-y-6">
    @include('admin.partials.flash')

    {{-- ============ IMAGES ============ --}}
    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-stone-800">
                <i class="ri-image-line"></i> Galeri Gambar
            </h3>
            <span class="text-xs text-stone-500">{{ $images->count() }} gambar</span>
        </div>

        @if ($images->isEmpty())
            <p class="text-sm text-stone-400">Belum ada gambar. Unggah di bawah.</p>
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                @foreach ($images as $image)
                    <div wire:key="img-{{ $image->id }}"
                        class="group relative overflow-hidden rounded-xl border {{ $image->is_primary ? 'border-emerald-600 ring-2 ring-emerald-600' : 'border-stone-200' }}">
                        <img src="{{ $image->url }}" alt="{{ $image->original_name }}"
                            class="aspect-square w-full object-cover">

                        @if ($image->is_primary)
                            <span class="absolute left-2 top-2 badge badge-success badge-sm text-white">Utama</span>
                        @endif

                        <div class="absolute inset-x-0 bottom-0 flex justify-between gap-1 bg-black/50 p-2 opacity-0 transition group-hover:opacity-100">
                            @unless ($image->is_primary)
                                <button type="button" class="btn btn-xs btn-success text-white"
                                    wire:click="setPrimary('{{ $image->id }}')" title="Jadikan utama">
                                    <i class="ri-star-line"></i>
                                </button>
                            @endunless
                            <button type="button" class="btn btn-xs btn-error text-white ml-auto"
                                data-confirm="Hapus gambar ini?"
                                wire:click="remove('{{ $image->id }}')" title="Hapus">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <form wire:submit="uploadImages" class="space-y-3 border-t border-stone-100 pt-4">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Tambah Gambar (bisa pilih beberapa)</legend>
                <input type="file" class="file-input file-input-bordered w-full" accept="image/*"
                    multiple wire:model="newImages">
                <p class="label text-xs text-stone-400">JPG, PNG, WEBP — maks 4 MB / gambar.</p>
                @error('newImages.*')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <div wire:loading wire:target="newImages" class="text-sm text-stone-500">
                <span class="loading loading-spinner loading-xs"></span> Mengunggah...
            </div>

            <button type="submit" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700"
                wire:loading.attr="disabled" wire:target="uploadImages, newImages">
                <i class="ri-upload-2-line"></i> Simpan Gambar
            </button>
        </form>
    </section>

    {{-- ============ VIDEOS ============ --}}
    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-stone-800">
                <i class="ri-film-line"></i> Video
            </h3>
            <span class="text-xs text-stone-500">{{ $videos->count() }} video</span>
        </div>

        @if ($videos->isEmpty())
            <p class="text-sm text-stone-400">Belum ada video.</p>
        @else
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($videos as $video)
                    <div wire:key="vid-{{ $video->id }}" class="rounded-xl border border-stone-200 p-2">
                        <video src="{{ $video->url }}" controls class="w-full rounded-lg"></video>
                        <div class="mt-2 flex items-center justify-between gap-2">
                            <span class="truncate text-xs text-stone-500">{{ $video->original_name }}</span>
                            <button type="button" class="btn btn-xs btn-error text-white"
                                data-confirm="Hapus video ini?"
                                wire:click="remove('{{ $video->id }}')">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <form wire:submit="uploadVideo" class="space-y-3 border-t border-stone-100 pt-4">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Tambah Video</legend>
                <input type="file" class="file-input file-input-bordered w-full" accept="video/mp4,video/webm"
                    wire:model="newVideo">
                <p class="label text-xs text-stone-400">MP4 atau WEBM — maks 50 MB.</p>
                @error('newVideo')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <div wire:loading wire:target="newVideo" class="text-sm text-stone-500">
                <span class="loading loading-spinner loading-xs"></span> Mengunggah...
            </div>

            <button type="submit" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700"
                wire:loading.attr="disabled" wire:target="uploadVideo, newVideo">
                <i class="ri-upload-2-line"></i> Simpan Video
            </button>
        </form>
    </section>
</div>
