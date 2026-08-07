@props([
    'title',
    'value',
    'description' => null,
    'icon' => null,
    'color' => null,
    'href' => null,
])

{{-- Kartu KPI dashboard — DESIGN.md "KPI Cards". --}}

@php
    $valueColor = [
        'primary' => 'text-primary',
        'accent' => 'text-accent',
        'success' => 'text-success',
        'warning' => 'text-warning',
        'error' => 'text-error',
        'info' => 'text-info',
    ][$color] ?? '';

    $tag = $href ? 'a' : 'article';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" wire:navigate @endif
    {{ $attributes->merge([
        'class' => 'block rounded-xl border border-base-300 bg-base-100 p-5' . ($href ? ' transition hover:bg-base-200' : ''),
    ]) }}>

    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-medium text-base-content/70">{{ $title }}</p>
        @if ($icon)
            <i class="{{ $icon }} text-xl text-base-content/40" aria-hidden="true"></i>
        @endif
    </div>

    <p class="mt-2 text-3xl font-semibold {{ $valueColor }}">{{ $value }}</p>

    @if ($description)
        <p class="mt-1 text-sm text-base-content/60">{{ $description }}</p>
    @endif

    {{ $slot }}
</{{ $tag }}>
