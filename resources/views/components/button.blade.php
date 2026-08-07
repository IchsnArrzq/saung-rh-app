@props([
    'variant' => 'primary',
    'size' => 'md',
    'outline' => false,
    'icon' => null,
    'iconRight' => null,
    'shape' => null,
    'block' => false,
    'href' => null,
    'loading' => null,
    'label' => null,
    'as' => null,
])

{{--
    Aksi tunggal untuk seluruh aplikasi — lihat DESIGN.md "Button Hierarchy".
    variant : primary (aksi utama, satu per section) · outline (sekunder)
              error (destruktif) · ghost (aksi teks) · + token semantik lain
    shape   : square|circle untuk tombol ikon-saja — wajib isi `label`
    loading : nama method Livewire; menghasilkan disabled + spinner otomatis
    href    : merender <a>, bukan <button>
    as      : paksa tag lain — dipakai `label` untuk toggle drawer daisyUI
--}}

@php
    $variantClass = [
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'accent' => 'btn-accent',
        'neutral' => 'btn-neutral',
        'ghost' => 'btn-ghost',
        'link' => 'btn-link',
        'info' => 'btn-info',
        'success' => 'btn-success',
        'warning' => 'btn-warning',
        'error' => 'btn-error',
        'outline' => 'btn-outline',
        'plain' => '',
    ][$variant] ?? 'btn-primary';

    $sizeClass = [
        'xs' => 'btn-xs',
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ][$size] ?? '';

    $shapeClass = [
        'square' => 'btn-square',
        'circle' => 'btn-circle',
    ][$shape] ?? '';

    $classes = implode(' ', array_filter([
        'btn',
        $variantClass,
        $outline && $variant !== 'outline' ? 'btn-outline' : null,
        $sizeClass,
        $shapeClass,
        $block ? 'btn-block' : null,
    ]));

    $spinnerSize = in_array($size, ['xs', 'sm'], true) ? 'loading-xs' : 'loading-sm';
    $iconOnly = $shape !== null && trim((string) $slot) === '';

    // Aksesibilitas: tombol ikon-saja tidak punya teks, jadi butuh nama.
    $a11y = [];
    if ($label !== null) {
        $a11y['aria-label'] = $label;
        $a11y['title'] = $label;
    }

    // Livewire loading state — pola berulang di seluruh tabel admin.
    $wire = [];
    if ($loading !== null) {
        $wire['wire:loading.attr'] = 'disabled';
        if (is_string($loading)) {
            $wire['wire:target'] = $loading;
        }
    }
    $tag = $as ?: ($href ? 'a' : 'button');
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @elseif ($tag === 'button') type="{{ $attributes->get('type', 'button') }}" @endif
    {{ $attributes->except('type')->merge(['class' => $classes])->merge($a11y)->merge($wire) }}>

    @if ($loading !== null)
        <span class="loading loading-spinner {{ $spinnerSize }}" wire:loading
            @if (is_string($loading)) wire:target="{{ $loading }}" @endif></span>
    @endif

    @if ($icon)
        <i class="{{ $icon }}" aria-hidden="true"
            @if ($loading !== null) wire:loading.remove @if (is_string($loading)) wire:target="{{ $loading }}" @endif @endif></i>
    @endif

    @unless ($iconOnly)
        {{ $slot }}
    @endunless

    @if ($iconRight)
        <i class="{{ $iconRight }}" aria-hidden="true"></i>
    @endif
</{{ $tag }}>
