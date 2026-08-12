@props([
    'status',
    'enum' => null,
    'size' => 'md',
    'outline' => false,
])

{{--
    Satu-satunya cara menampilkan status di UI. Label & warna diambil dari Enum
    domain — jangan pernah menulis label atau warna status di view.

    <x-status-badge :status="$order->status" />
    <x-status-badge :status="$row->status" :enum="\App\Domains\Order\Enums\OrderStatus::class" />
--}}

@php
    $resolved = $status;

    if (! $resolved instanceof \BackedEnum && $status !== null && $status !== '' && $enum !== null) {
        $resolved = $enum::tryFrom((string) $status);
    }

    $hasEnum = $resolved instanceof \BackedEnum;

    $statusLabel = $hasEnum && method_exists($resolved, 'label')
        ? $resolved->label()
        : (string) ($status ?? '-');

    $statusColor = $hasEnum && method_exists($resolved, 'color')
        ? $resolved->color()
        : 'neutral';
@endphp

<x-badge :color="$statusColor" :size="$size" :outline="$outline" {{ $attributes }}>
    {{ $statusLabel }}
</x-badge>
