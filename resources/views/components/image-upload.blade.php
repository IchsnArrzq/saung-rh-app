@props([
    'label' => null,
    'name',
    'hint' => null,
    'preview' => null,
    'shape' => 'square',
    'icon' => 'ri-image-add-line',
    'prompt' => 'Pilih foto',
    'accept' => 'image/png,image/jpeg,image/webp',
    'multiple' => false,
    'required' => false,
])

{{--
    Unggah berkas untuk komponen Livewire (WithFileUploads): area seret-lepas
    dengan pratinjau dan bilah kemajuan, pengganti <input type="file"> polos.

      <x-image-upload name="photo" wire:model="photo" label="Foto profil" shape="circle"
          :preview="$previewUrl" hint="PNG, JPG, atau WEBP — maks 2 MB." />

    Livewire memancarkan livewire-upload-start/progress/finish/error dari <input>
    (menggelembung ke <label>) — didengar di sini untuk spinner dan persen. Berkas
    yang dilepas ke area ini dipasang ke <input> lalu event `change` dikirim:
    jalur yang sama persis dengan memilih lewat dialog berkas.

    `class` menempel ke pembungkus field; atribut lain (wire:model, …) ke <input>.
--}}

@php
    $inputId = $attributes->get('id') ?: 'upload-' . str_replace(['.', '_', '*'], '-', $name);
    $errorMessage = ($errors ?? null)?->first($name) ?: (($errors ?? null)?->first($name . '.*') ?: null);
    $previewShape = $shape === 'circle' ? 'rounded-full' : 'rounded-xl';
@endphp

<x-field :label="$label" :for="$inputId" :hint="$hint" :required="$required" :error="$errorMessage"
    class="{{ $attributes->get('class') }}">
    <label for="{{ $inputId }}"
        x-data="{ dragging: false, uploading: false, progress: 0 }"
        x-on:dragover.prevent="dragging = true"
        x-on:dragleave.prevent="dragging = false"
        x-on:drop.prevent="
            dragging = false;
            if ($event.dataTransfer.files.length) {
                $refs.input.files = $event.dataTransfer.files;
                $refs.input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        "
        x-on:livewire-upload-start="uploading = true; progress = 0"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
        x-on:livewire-upload-finish="uploading = false"
        x-on:livewire-upload-error="uploading = false"
        x-on:livewire-upload-cancel="uploading = false"
        :class="dragging ? 'border-primary bg-primary/5' : 'border-base-300 hover:border-primary/60 hover:bg-base-200'"
        class="flex cursor-pointer flex-col items-center gap-4 rounded-xl border-2 border-dashed border-base-300 p-4 text-center transition-colors has-focus-visible:ring-2 has-focus-visible:ring-primary/40 sm:flex-row sm:text-left">
        <span class="relative flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden bg-base-200 text-base-content/40 {{ $previewShape }}">
            @if ($preview)
                <img src="{{ $preview }}" alt="" class="h-full w-full object-cover">
            @else
                <i class="{{ $icon }} text-3xl" aria-hidden="true"></i>
            @endif

            <span x-show="uploading" style="display: none"
                class="absolute inset-0 flex items-center justify-center bg-base-100/70">
                <span class="loading loading-spinner loading-md text-primary"></span>
            </span>
        </span>

        <span class="min-w-0 flex-1 space-y-2">
            <span class="block text-sm">
                <span class="font-semibold text-primary">{{ $prompt }}</span> atau seret ke sini
            </span>
            <progress x-show="uploading" style="display: none" class="progress progress-primary w-full" max="100"
                x-bind:value="progress"></progress>
            <span x-show="uploading" style="display: none" class="block text-xs tabular-nums text-base-content/60"
                x-text="'Mengunggah ' + progress + '%'"></span>
        </span>

        <input id="{{ $inputId }}" x-ref="input" type="file" accept="{{ $accept }}" class="sr-only"
            @if ($multiple) multiple @endif
            @if ($errorMessage) aria-invalid="true" @endif
            {{ $attributes->except(['class', 'id']) }}>
    </label>
</x-field>
