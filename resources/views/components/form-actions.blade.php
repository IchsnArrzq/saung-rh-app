@props([
    'submitLabel' => 'Simpan',
    'cancelLabel' => 'Batal',
    'cancelHref' => null,
    'loading' => null,
    'disabled' => false,
    'align' => 'end',
])

{{--
    Posisi bawah-kanan, urutan Batal lalu Simpan — DESIGN.md "Form Actions".
    Isi slot untuk tombol tambahan; slot dirender sebelum Batal/Simpan.
--}}

@php
    $alignClass = [
        'end' => 'justify-end',
        'between' => 'justify-between',
        'start' => 'justify-start',
    ][$align] ?? 'justify-end';
@endphp

<div {{ $attributes->merge(['class' => 'mt-6 flex flex-wrap items-center gap-2 ' . $alignClass]) }}>
    {{ $slot }}

    @if ($cancelHref)
        <x-button variant="ghost" :href="$cancelHref" wire:navigate>{{ $cancelLabel }}</x-button>
    @endif

    <x-button type="submit" variant="primary" :loading="$loading" :disabled="$disabled">
        {{ $submitLabel }}
    </x-button>
</div>
