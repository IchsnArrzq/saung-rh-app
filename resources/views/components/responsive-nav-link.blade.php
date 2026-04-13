@props(['active'])

@php
$classes = ($active ?? false)
            ? 'btn btn-ghost justify-start w-full bg-base-200'
            : 'btn btn-ghost justify-start w-full';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
