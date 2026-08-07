@props([
    'label' => null,
    'name' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'size' => 'md',
    'placeholder' => null,
    'options' => null,
    'disabled' => false,
    'bare' => false,
    'fieldClass' => null,
])

{{--
    Isi opsi lewat slot, atau lewat `:options` untuk array [value => label].
    `bare` melepas pembungkus label/hint/error — untuk kontrol inline di dalam tabel.
    Untuk memilih dari daftar bergambar/berstatus, pertimbangkan picker visual —
    lihat DESIGN.md "Recognition over Recall".
--}}

@php
    $inputId = $attributes->get('id') ?: ($name ? 'f-' . str_replace(['.', '_'], '-', $name) : null);
    $errorMessage = $error ?: ($name ? (($errors ?? null)?->first($name) ?: null) : null);

    $sizeClass = [
        'xs' => 'select-xs',
        'sm' => 'select-sm',
        'md' => '',
        'lg' => 'select-lg',
    ][$size] ?? '';

    $classes = implode(' ', array_filter([
        'select select-bordered',
        $bare ? null : 'w-full',
        $sizeClass,
        $errorMessage ? 'select-error' : null,
    ]));
@endphp

@if ($bare)
    <select @disabled($disabled)
        @if ($inputId) id="{{ $inputId }}" @endif
        @if ($errorMessage) aria-invalid="true" @endif
        @if ($label) aria-label="{{ $label }}" @endif
        {{ $attributes->except('id')->merge(['class' => $classes]) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @if (is_array($options))
            @foreach ($options as $value => $text)
                <option value="{{ $value }}">{{ $text }}</option>
            @endforeach
        @endif
        {{ $slot }}
    </select>
@else
    <x-field :label="$label" :name="$name" :hint="$hint" :error="$error" :required="$required" :for="$inputId"
        class="{{ $fieldClass }}">
        <select @disabled($disabled)
            @if ($inputId) id="{{ $inputId }}" @endif
            @if ($errorMessage) aria-invalid="true" @endif
            {{ $attributes->except('id')->merge(['class' => $classes]) }}>

            @if ($placeholder !== null)
                <option value="">{{ $placeholder }}</option>
            @endif

            @if (is_array($options))
                @foreach ($options as $value => $text)
                    <option value="{{ $value }}">{{ $text }}</option>
                @endforeach
            @endif

            {{ $slot }}
        </select>
    </x-field>
@endif
