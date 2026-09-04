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

    // Tanpa wire:model field ini adalah kontrol HTML biasa di dalam <form> asli,
    // jadi ia butuh `name` (kalau tidak, nilainya tidak ikut terkirim) dan
    // `required` sungguhan. Field Livewire tidak: nilainya sudah lewat wire:model.
    $isWired = $attributes->whereStartsWith('wire:model')->isNotEmpty();

    $nativeAttributes = $isWired ? [] : array_filter([
        'name' => $name,
        'required' => $required ?: null,
    ]);
@endphp

<x-field :label="$label" :name="$name" :hint="$hint" :error="$error" :required="$required" :for="$inputId"
    class="{{ $fieldClass }}">
    <textarea rows="{{ $rows }}" @disabled($disabled)
        @if ($inputId) id="{{ $inputId }}" @endif
        @if ($errorMessage) aria-invalid="true" @endif
        {{ $attributes->except('id')->merge($nativeAttributes)->merge(['class' => $classes]) }}>{{ $slot }}</textarea>
</x-field>
