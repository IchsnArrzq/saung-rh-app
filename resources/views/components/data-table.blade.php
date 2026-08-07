@props([
    'zebra' => true,
    'size' => 'md',
])

{{--
    Pembungkus tabel. `overflow-x-auto` wajib — DESIGN.md "Table Responsiveness".

    <x-data-table>
        <x-slot:head><tr><th>Nama</th></tr></x-slot:head>
        <tr>...</tr>
    </x-data-table>
--}}

@php
    $tableClasses = implode(' ', array_filter([
        'table',
        $zebra ? 'table-zebra' : null,
        ['xs' => 'table-xs', 'sm' => 'table-sm', 'md' => '', 'lg' => 'table-lg'][$size] ?? '',
    ]));
@endphp

<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-xl border border-base-300 bg-base-100']) }}>
    <table class="{{ $tableClasses }}">
        @isset($head)
            <thead>{{ $head }}</thead>
        @endisset

        <tbody>{{ $slot }}</tbody>

        @isset($foot)
            <tfoot>{{ $foot }}</tfoot>
        @endisset
    </table>
</div>
