@props(['value'])

<label {{ $attributes->merge(['class' => 'label text-sm']) }}>
    {{ $value ?? $slot }}
</label>
