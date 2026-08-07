@props([
    'rows' => 3,
    'height' => 'h-4',
])

{{--
    Placeholder saat memuat — DESIGN.md "Loading States": jangan pernah layar kosong.
    Pakai bersama wire:loading:

    <div wire:loading wire:target="search"><x-skeleton :rows="5" /></div>
--}}

<div {{ $attributes->merge(['class' => 'space-y-2']) }} aria-hidden="true">
    @for ($i = 0; $i < (int) $rows; $i++)
        <div class="skeleton {{ $height }} w-full"></div>
    @endfor
</div>
