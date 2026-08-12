@props([
    'color' => 'neutral',
    'size' => 'md',
    'outline' => false,
    'icon' => null,
])

{{--
    `color` menerima token semantik (hasil Enum ->color()), bukan nama kelas.
    Pemetaan ke kelas utuh wajib — scanner Tailwind v4 tidak membaca kelas
    yang dirakit dari variabel. Lihat DESIGN.md "Status Display".
--}}

@php
    $colorClass = [
        'primary' => 'badge-primary',
        'secondary' => 'badge-secondary',
        'accent' => 'badge-accent',
        'neutral' => 'badge-neutral',
        'info' => 'badge-info',
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'error' => 'badge-error',
        'ghost' => 'badge-ghost',
        'default' => '',
    ][$color] ?? 'badge-neutral';

    $sizeClass = [
        'xs' => 'badge-xs',
        'sm' => 'badge-sm',
        'md' => '',
        'lg' => 'badge-lg',
    ][$size] ?? '';

    $classes = implode(' ', array_filter([
        'badge',
        $colorClass,
        $outline ? 'badge-outline' : null,
        $sizeClass,
    ]));
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
        <i class="{{ $icon }}" aria-hidden="true"></i>
    @endif
    {{ $slot }}
</span>
