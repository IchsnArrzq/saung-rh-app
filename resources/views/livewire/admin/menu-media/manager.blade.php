<div class="space-y-6">
    @include('admin.partials.flash')

    {{-- ============ IMAGES ============ --}}
    <x-card class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold">
                <i class="ri-image-line" aria-hidden="true"></i> Galeri gambar
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
                            <x-badge color="success" size="sm" class="absolute left-2 top-2">
                                Utama
                            </x-badge>
                        @endif

                        {{-- check-ui-allow: scrim gelap di atas foto supaya tombolnya terbaca. --}}
                        <div class="absolute inset-x-0 bottom-0 flex justify-between gap-1 bg-black/50 p-2">
                            @unless ($image->is_primary)
                                <x-button variant="success" size="xs" icon="ri-star-line"
                                    label="Jadikan gambar utama" wire:click="setPrimary('{{ $image->id }}')" />
                            @endunless
                            <x-button variant="error" size="xs" icon="ri-delete-bin-line" class="ml-auto"
                                label="Hapus gambar" data-confirm="Hapus gambar ini?"
                                wire:click="remove('{{ $image->id }}')" />
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <form wire:submit="uploadImages" class="space-y-3 pt-2">
            <x-image-upload name="newImages" wire:model="newImages" label="Tambah gambar" prompt="Pilih gambar" multiple
                hint="Bisa pilih beberapa sekaligus. JPG, PNG, atau WEBP — maks 4 MB per gambar. Gambar pertama yang diunggah menjadi gambar utama bila belum ada." />

            @if (count($newImages) > 0 && ! $errors->has('newImages.*'))
                {{-- Pratinjau gambar yang sudah terunggah tapi belum disimpan ke menu. --}}
                <div class="flex flex-wrap gap-2" aria-label="Gambar siap disimpan">
                    @foreach ($newImages as $pending)
                        @if (method_exists($pending, 'isPreviewable') && $pending->isPreviewable())
                            <img src="{{ $pending->temporaryUrl() }}" alt="{{ $pending->getClientOriginalName() }}"
                                class="h-16 w-16 rounded-lg object-cover">
                        @endif
                    @endforeach
                </div>
                <p class="text-sm text-base-content/70">
                    <span class="tabular-nums">{{ count($newImages) }}</span> gambar siap disimpan.
                </p>
            @endif

            <x-button type="submit" variant="primary" size="sm" icon="ri-upload-2-line"
                loading="uploadImages, newImages">
                Simpan gambar
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
                            <x-button variant="error" size="xs" icon="ri-delete-bin-line"
                                label="Hapus video" data-confirm="Hapus video ini?"
                                wire:click="remove('{{ $video->id }}')" />
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <form wire:submit="uploadVideo" class="space-y-3 pt-2">
            <x-image-upload name="newVideo" wire:model="newVideo" label="Tambah video" icon="ri-film-line"
                prompt="Pilih video" accept="video/mp4,video/webm"
                hint="MP4 atau WEBM — maks 50 MB. Video pertama tampil di detail menu, dengan gambar utama sebagai sampulnya." />

            @if ($newVideo && ! $errors->has('newVideo'))
                <p class="text-sm text-base-content/70">{{ $newVideo->getClientOriginalName() }} siap disimpan.</p>
            @endif

            <x-button type="submit" variant="primary" size="sm" icon="ri-upload-2-line"
                loading="uploadVideo, newVideo">
                Simpan video
            </x-button>
        </form>
    </x-card>
</div>
