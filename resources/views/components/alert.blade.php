@props([
    'type' => 'info',
    'title' => null,
    'icon' => null,
    'dismissible' => false,
])

{{-- Pesan sistem. Untuk error validasi per-field pakai <x-field>, bukan ini. --}}

@php
    $typeClass = [
        'info' => 'alert-info',
        'success' => 'alert-success',
        'warning' => 'alert-warning',
        'error' => 'alert-error',
    ][$type] ?? 'alert-info';

    $defaultIcon = [
        'info' => 'ri-information-line',
        'success' => 'ri-checkbox-circle-line',
        'warning' => 'ri-alert-line',
        'error' => 'ri-error-warning-line',
    ][$type] ?? 'ri-information-line';
@endphp

<div role="alert" @if ($dismissible) x-data="{ shown: true }" x-show="shown" @endif
    {{ $attributes->merge(['class' => 'alert ' . $typeClass]) }}>
    <i class="{{ $icon ?? $defaultIcon }} text-lg" aria-hidden="true"></i>
    <div>
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <span>{{ $slot }}</span>
    </div>
    @if ($dismissible)
        <x-button variant="ghost" size="sm" shape="circle" icon="ri-close-line"
            label="Tutup pesan" x-on:click="shown = false" />
    @endif
</div>
