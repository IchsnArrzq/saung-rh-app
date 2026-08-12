@props([
    'label' => null,
    'name' => null,
    'hint' => null,
    'error' => null,
    'size' => 'md',
    'variant' => 'primary',
    'disabled' => false,
])

@php
    $inputId = $attributes->get('id') ?: ($name ? 'f-' . str_replace(['.', '_'], '-', $name) : null);
    $errorMessage = $error ?: ($name ? (($errors ?? null)?->first($name) ?: null) : null);

    $classes = implode(' ', array_filter([
        'checkbox',
        [
            'primary' => 'checkbox-primary',
            'accent' => 'checkbox-accent',
            'success' => 'checkbox-success',
            'plain' => '',
        ][$variant] ?? 'checkbox-primary',
        [
            'xs' => 'checkbox-xs',
            'sm' => 'checkbox-sm',
            'md' => '',
            'lg' => 'checkbox-lg',
        ][$size] ?? '',
    ]));
@endphp

<div class="form-control">
    <label class="flex cursor-pointer items-start gap-3">
        <input type="checkbox" @disabled($disabled)
            @if ($inputId) id="{{ $inputId }}" @endif
            {{ $attributes->except('id')->merge(['class' => $classes]) }}>

        <span>
            <span class="label-text">{{ $label ?? $slot }}</span>
            @if ($hint)
                <span class="block text-xs text-base-content/60">{{ $hint }}</span>
            @endif
        </span>
    </label>

    @if ($errorMessage)
        <p class="mt-1 text-xs text-error">{{ $errorMessage }}</p>
    @endif
</div>
