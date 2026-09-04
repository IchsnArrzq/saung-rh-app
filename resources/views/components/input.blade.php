@props([
    'label' => null,
    'name' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'icon' => null,
    'size' => 'md',
    'type' => 'text',
    'disabled' => false,
    'bare' => false,
    'fieldClass' => null,
])

{{--
    `bare`       melepas pembungkus label/hint/error — untuk kontrol inline di dalam tabel.
    `fieldClass` kelas untuk pembungkus (mis. `md:col-span-2` di dalam grid);
                 `class` biasa tetap menempel ke elemen input-nya.
--}}

@php
    $inputId = $attributes->get('id') ?: ($name ? 'f-' . str_replace(['.', '_'], '-', $name) : null);
    $errorMessage = $error ?: ($name ? (($errors ?? null)?->first($name) ?: null) : null);

    $sizeClass = [
        'xs' => 'input-xs',
        'sm' => 'input-sm',
        'md' => '',
        'lg' => 'input-lg',
    ][$size] ?? '';

    $classes = implode(' ', array_filter([
        'input input-bordered',
        $bare ? null : 'w-full',
        $sizeClass,
        $icon ? 'pl-10' : null,
        $errorMessage ? 'input-error' : null,
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

@if ($bare)
    <input type="{{ $type }}" @disabled($disabled)
        @if ($inputId) id="{{ $inputId }}" @endif
        @if ($errorMessage) aria-invalid="true" @endif
        @if ($label) aria-label="{{ $label }}" @endif
        {{ $attributes->except('id')->merge($nativeAttributes)->merge(['class' => $classes]) }}>
@else
    <x-field :label="$label" :name="$name" :hint="$hint" :error="$error" :required="$required" :for="$inputId"
        class="{{ $fieldClass }}">
        <div @class(['relative' => (bool) $icon])>
            @if ($icon)
                <i class="{{ $icon }} pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"
                    aria-hidden="true"></i>
            @endif

            <input type="{{ $type }}" @disabled($disabled)
                @if ($inputId) id="{{ $inputId }}" @endif
                @if ($errorMessage) aria-invalid="true" @endif
                {{ $attributes->except('id')->merge($nativeAttributes)->merge(['class' => $classes]) }}>
        </div>
    </x-field>
@endif
