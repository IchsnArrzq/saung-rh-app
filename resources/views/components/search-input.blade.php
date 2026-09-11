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

    Ikon hidup DI DALAM pembungkus `.input` (pola daisyUI 5), bukan diposisikan
    absolut di atas <input>. Di daisyUI 5 `.input` sendiri `position: relative`
    dengan latar base-100, jadi ikon absolut yang ditaruh sebelum <input>
    tertimpa latarnya dan tidak pernah terlihat.

    `class` menempel ke pembungkus (itulah kotak yang terlihat); atribut lain —
    wire:model, placeholder, dst. — ke <input> di dalamnya.
--}}

@php
    $sizeClass = [
        'sm' => 'input-sm',
        'md' => '',
        'lg' => 'input-lg',
    ][$size] ?? '';
@endphp

<label {{ $attributes->only('class')->merge(['class' => trim('input w-full ' . $sizeClass)]) }}>
    <i class="{{ $icon }} shrink-0 text-base text-base-content/50" aria-hidden="true"></i>

    <input type="search" aria-label="{{ $label }}" placeholder="{{ $placeholder }}" autocomplete="off"
        {{ $attributes->except('class')->merge(['class' => 'min-w-0 grow']) }}>
</label>
