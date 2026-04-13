@props(['active'])

@php
$classes = ($active ?? false)
            ? 'btn btn-ghost btn-sm bg-base-200'
            : 'btn btn-ghost btn-sm';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
