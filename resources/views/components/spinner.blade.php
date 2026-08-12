@props([
    'size' => 'md',
    'label' => 'Memuat',
])

@php
    $sizeClass = [
        'xs' => 'loading-xs',
        'sm' => 'loading-sm',
        'md' => 'loading-md',
        'lg' => 'loading-lg',
    ][$size] ?? 'loading-md';
@endphp

<span role="status" aria-label="{{ $label }}"
    {{ $attributes->merge(['class' => 'loading loading-spinner ' . $sizeClass]) }}></span>
