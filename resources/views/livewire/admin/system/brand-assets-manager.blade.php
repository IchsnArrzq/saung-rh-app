@php
    // temporaryUrl() melempar untuk berkas yang bukan gambar, dan berkasnya
    // sudah terunggah (lalu dirender) sebelum tombol simpan pernah ditekan —
    // jadi pratinjau hanya untuk yang memang bisa dipratinjau.
    $logoPreview = $logo?->isPreviewable() ? $logo->temporaryUrl() : $business->logoUrl();
    $markPreview = $mark?->isPreviewable() ? $mark->temporaryUrl() : $business->markUrl();
@endphp

<div class="space-y-4">
    @if (session('brand-status'))
        <x-alert type="success">{{ session('brand-status') }}</x-alert>
    @endif

    <x-card title="Logo & ikon"
        description="Dipakai di halaman publik, halaman masuk, navigasi, dan ikon layar utama ponsel.">
        <div class="grid gap-6 md:grid-cols-2">
            {{-- ============ WORDMARK ============ --}}
            <div class="space-y-3">
                <div>
                    <h3 class="text-lg font-semibold">Logo</h3>
                    <p class="mt-0.5 text-sm text-base-content/70">
                        Logo memanjang untuk header situs dan halaman masuk.
                    </p>
                </div>

                <div class="flex h-28 items-center justify-center rounded-xl border border-base-300 bg-base-200 p-4">
                    <img src="{{ $logoPreview }}"
                        alt="Logo {{ $business->name() }}" class="max-h-full w-auto object-contain">
                </div>

                @unless ($business->hasCustomLogo())
                    <p class="text-xs text-base-content/60">Masih memakai logo bawaan aplikasi.</p>
                @endunless

                @can('update', $appSetting)
                    <form wire:submit="saveLogo" class="space-y-3">
                        <x-field label="Ganti logo" name="logo" for="brand-logo-upload"
                            hint="PNG, JPG, atau WEBP — maks 2 MB.">
                            <input type="file" id="brand-logo-upload"
                                class="file-input file-input-bordered file-input-sm w-full"
                                accept="image/png,image/jpeg,image/webp" wire:model="logo">
                        </x-field>

                        <div wire:loading wire:target="logo" class="flex items-center gap-2 text-sm text-base-content/60">
                            <x-spinner size="xs" /> Mengunggah berkas...
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <x-button type="submit" variant="outline" size="sm" icon="ri-upload-2-line"
                                loading="saveLogo, logo">
                                Simpan logo
                            </x-button>

                            @if ($business->hasCustomLogo())
                                <x-button variant="error" outline size="sm" icon="ri-delete-bin-line"
                                    wire:click="removeLogo" loading="removeLogo"
                                    data-confirm="Hapus logo yang diunggah dan kembali ke bawaan aplikasi?">
                                    Hapus logo
                                </x-button>
                            @endif
                        </div>
                    </form>
                @endcan
            </div>

            {{-- ============ SQUARE MARK ============ --}}
            <div class="space-y-3">
                <div>
                    <h3 class="text-lg font-semibold">Ikon</h3>
                    <p class="mt-0.5 text-sm text-base-content/70">
                        Versi persegi untuk navigasi, footer, dan ikon layar utama ponsel.
                    </p>
                </div>

                <div class="flex h-28 items-center justify-center rounded-xl border border-base-300 bg-base-200 p-4">
                    <img src="{{ $markPreview }}"
                        alt="Ikon {{ $business->name() }}" class="h-full w-auto object-contain">
                </div>

                @unless ($business->hasCustomMark())
                    <p class="text-xs text-base-content/60">Masih memakai ikon bawaan aplikasi.</p>
                @endunless

                @can('update', $appSetting)
                    <form wire:submit="saveMark" class="space-y-3">
                        <x-field label="Ganti ikon" name="mark" for="brand-mark-upload"
                            hint="Persegi, PNG, JPG, atau WEBP — maks 2 MB.">
                            <input type="file" id="brand-mark-upload"
                                class="file-input file-input-bordered file-input-sm w-full"
                                accept="image/png,image/jpeg,image/webp" wire:model="mark">
                        </x-field>

                        <div wire:loading wire:target="mark" class="flex items-center gap-2 text-sm text-base-content/60">
                            <x-spinner size="xs" /> Mengunggah berkas...
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <x-button type="submit" variant="outline" size="sm" icon="ri-upload-2-line"
                                loading="saveMark, mark">
                                Simpan ikon
                            </x-button>

                            @if ($business->hasCustomMark())
                                <x-button variant="error" outline size="sm" icon="ri-delete-bin-line"
                                    wire:click="removeMark" loading="removeMark"
                                    data-confirm="Hapus ikon yang diunggah dan kembali ke bawaan aplikasi?">
                                    Hapus ikon
                                </x-button>
                            @endif
                        </div>
                    </form>
                @endcan
            </div>
        </div>
    </x-card>
</div>
