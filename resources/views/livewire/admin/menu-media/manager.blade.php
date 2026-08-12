<div class="space-y-6">
    @include('admin.partials.flash')

    {{-- ============ IMAGES ============ --}}
    <x-card class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold">
                <i class="ri-image-line"></i> Galeri Gambar
            </h3>
            <span class="text-xs text-base-content/60">{{ $images->count() }} gambar</span>
        </div>

        @if ($images->isEmpty())
            <x-empty-state icon="ri-image-line" title="Belum ada gambar"
                description="Unggah gambar pertama lewat form di bawah." />
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                @foreach ($images as $image)
                    <div wire:key="img-{{ $image->id }}"
                        class="relative overflow-hidden rounded-xl border {{ $image->is_primary ? 'border-accent ring-2 ring-accent' : 'border-base-300' }}">
                        <img src="{{ $image->url }}" alt="{{ $image->original_name }}"
                            class="aspect-square w-full object-cover">

                        @if ($image->is_primary)
                            <x-badge color="success" size="sm" class="absolute left-2 top-2 text-white">
                                Utama
                            </x-badge>
                        @endif

                        <div class="absolute inset-x-0 bottom-0 flex justify-between gap-1 bg-black/50 p-2">
                            @unless ($image->is_primary)
                                <x-button variant="success" size="xs" icon="ri-star-line" class="text-white"
                                    label="Jadikan gambar utama" wire:click="setPrimary('{{ $image->id }}')" />
                            @endunless
                            <x-button variant="error" size="xs" icon="ri-delete-bin-line" class="ml-auto text-white"
                                label="Hapus gambar" data-confirm="Hapus gambar ini?"
                                wire:click="remove('{{ $image->id }}')" />
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <form wire:submit="uploadImages" class="space-y-3 border-t border-base-300 pt-4">
            <x-field label="Tambah Gambar (bisa pilih beberapa)" name="newImages.*"
                hint="JPG, PNG, WEBP — maks 4 MB / gambar.">
                <input type="file" class="file-input file-input-bordered w-full" accept="image/*"
                    multiple wire:model="newImages">
            </x-field>

            <div wire:loading wire:target="newImages" class="text-sm text-base-content/60">
                <x-spinner size="xs" /> Mengunggah...
            </div>

            <x-button type="submit" variant="primary" size="sm" icon="ri-upload-2-line"
                loading="uploadImages, newImages">
                Simpan Gambar
            </x-button>
        </form>
    </x-card>

    {{-- ============ VIDEOS ============ --}}
    <x-card class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold">
                <i class="ri-film-line"></i> Video
            </h3>
            <span class="text-xs text-base-content/60">{{ $videos->count() }} video</span>
        </div>

        @if ($videos->isEmpty())
            <x-empty-state icon="ri-film-line" title="Belum ada video"
                description="Unggah video lewat form di bawah." />
        @else
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($videos as $video)
                    <div wire:key="vid-{{ $video->id }}" class="rounded-xl border border-base-300 p-2">
                        <video src="{{ $video->url }}" controls class="w-full rounded-lg"></video>
                        <div class="mt-2 flex items-center justify-between gap-2">
                            <span class="truncate text-xs text-base-content/60">{{ $video->original_name }}</span>
                            <x-button variant="error" size="xs" icon="ri-delete-bin-line" class="text-white"
                                label="Hapus video" data-confirm="Hapus video ini?"
                                wire:click="remove('{{ $video->id }}')" />
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <form wire:submit="uploadVideo" class="space-y-3 border-t border-base-300 pt-4">
            <x-field label="Tambah Video" name="newVideo" hint="MP4 atau WEBM — maks 50 MB.">
                <input type="file" class="file-input file-input-bordered w-full" accept="video/mp4,video/webm"
                    wire:model="newVideo">
            </x-field>

            <div wire:loading wire:target="newVideo" class="text-sm text-base-content/60">
                <x-spinner size="xs" /> Mengunggah...
            </div>

            <x-button type="submit" variant="primary" size="sm" icon="ri-upload-2-line"
                loading="uploadVideo, newVideo">
                Simpan Video
            </x-button>
        </form>
    </x-card>
</div>
