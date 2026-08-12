@props([
    'placeholder' => 'Cari...',
    'icon' => 'ri-search-line',
    'size' => 'md',
    'label' => 'Cari',
])

{{--
    Kotak pencarian standar. Pemanggil yang menentukan sumber datanya:
    <x-search-input wire:model.live.debounce.300ms="search" placeholder="Cari menu..." />

    Debounce 300ms wajib untuk input live — DESIGN.md "Doherty Threshold".
--}}

@php
    $sizeClass = [
        'sm' => 'input-sm',
        'md' => '',
        'lg' => 'input-lg',
    ][$size] ?? '';
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'relative w-full']) }}>
    <i class="{{ $icon }} pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"
        aria-hidden="true"></i>

    <input type="text" aria-label="{{ $label }}" placeholder="{{ $placeholder }}"
        {{ $attributes->except('class')->merge(['class' => trim('input input-bordered w-full pl-10 ' . $sizeClass)]) }}>
</div>
