@props([
    'label' => null,
    'name' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'size' => 'md',
    'rows' => 3,
    'disabled' => false,
    'fieldClass' => null,
])

{{-- `fieldClass` kelas untuk pembungkus (mis. `md:col-span-2`); `class` menempel ke textarea. --}}

@php
    $inputId = $attributes->get('id') ?: ($name ? 'f-' . str_replace(['.', '_'], '-', $name) : null);
    $errorMessage = $error ?: ($name ? (($errors ?? null)?->first($name) ?: null) : null);

    $sizeClass = [
        'xs' => 'textarea-xs',
        'sm' => 'textarea-sm',
        'md' => '',
        'lg' => 'textarea-lg',
    ][$size] ?? '';

    $classes = implode(' ', array_filter([
        'textarea textarea-bordered w-full',
        $sizeClass,
        $errorMessage ? 'textarea-error' : null,
    ]));
@endphp

<x-field :label="$label" :name="$name" :hint="$hint" :error="$error" :required="$required" :for="$inputId"
    class="{{ $fieldClass }}">
    <textarea rows="{{ $rows }}" @disabled($disabled)
        @if ($inputId) id="{{ $inputId }}" @endif
        @if ($errorMessage) aria-invalid="true" @endif
        {{ $attributes->except('id')->merge(['class' => $classes]) }}>{{ $slot }}</textarea>
</x-field>
