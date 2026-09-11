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
    `icon`       memakai pola daisyUI 5: `.input` menjadi <label> pembungkus berisi
                 ikon + <input>. Ikon absolut di atas <input> tertimpa latar `.input`
                 (yang di v5 sendiri `position: relative`) dan tidak pernah terlihat.
                 Pada varian ini `class` menempel ke pembungkusnya — itulah kotak
                 yang terlihat.
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

    $boxClasses = implode(' ', array_filter([
        'input input-bordered',
        $bare ? null : 'w-full',
        $sizeClass,
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

    $iconBoxClasses = trim($boxClasses . ' ' . $attributes->get('class', ''));
@endphp

@if ($bare)
    @if ($icon)
        <label class="{{ $iconBoxClasses }}">
            <i class="{{ $icon }} shrink-0 text-base-content/50" aria-hidden="true"></i>
            <input type="{{ $type }}" @disabled($disabled)
                @if ($inputId) id="{{ $inputId }}" @endif
                @if ($errorMessage) aria-invalid="true" @endif
                @if ($label) aria-label="{{ $label }}" @endif
                {{ $attributes->except(['id', 'class'])->merge($nativeAttributes)->merge(['class' => 'min-w-0 grow']) }}>
        </label>
    @else
        <input type="{{ $type }}" @disabled($disabled)
            @if ($inputId) id="{{ $inputId }}" @endif
            @if ($errorMessage) aria-invalid="true" @endif
            @if ($label) aria-label="{{ $label }}" @endif
            {{ $attributes->except('id')->merge($nativeAttributes)->merge(['class' => $boxClasses]) }}>
    @endif
@else
    <x-field :label="$label" :name="$name" :hint="$hint" :error="$error" :required="$required" :for="$inputId"
        class="{{ $fieldClass }}">
        @if ($icon)
            <label class="{{ $iconBoxClasses }}">
                <i class="{{ $icon }} shrink-0 text-base-content/50" aria-hidden="true"></i>
                <input type="{{ $type }}" @disabled($disabled)
                    @if ($inputId) id="{{ $inputId }}" @endif
                    @if ($errorMessage) aria-invalid="true" @endif
                    {{ $attributes->except(['id', 'class'])->merge($nativeAttributes)->merge(['class' => 'min-w-0 grow']) }}>
            </label>
        @else
            <input type="{{ $type }}" @disabled($disabled)
                @if ($inputId) id="{{ $inputId }}" @endif
                @if ($errorMessage) aria-invalid="true" @endif
                {{ $attributes->except('id')->merge($nativeAttributes)->merge(['class' => $boxClasses]) }}>
        @endif
    </x-field>
@endif
