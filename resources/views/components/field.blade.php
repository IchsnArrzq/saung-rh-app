@props([
    'label' => null,
    'for' => null,
    'name' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
])

{{--
    Urutan wajib menurut DESIGN.md "Field Order": Label → Input → Hint → Error.
    Error diambil otomatis dari bag validasi lewat `name`.
--}}

@php
    // $errors hanya di-share oleh middleware web; jaga komponen tetap aman di luar request.
    $errorMessage = $error ?: ($name ? (($errors ?? null)?->first($name) ?: null) : null);
@endphp

<div {{ $attributes->merge(['class' => 'form-control w-full']) }}>
    @if ($label)
        <label class="label px-0 pb-1" @if ($for) for="{{ $for }}" @endif>
            <span class="label-text">
                {{ $label }}@if ($required)<span class="text-error" aria-hidden="true"> *</span>@endif
            </span>
        </label>
    @endif

    {{ $slot }}

    @if ($hint)
        <p class="mt-1 text-xs text-base-content/60">{{ $hint }}</p>
    @endif

    @if ($errorMessage)
        <p class="mt-1 text-xs text-error">{{ $errorMessage }}</p>
    @endif
</div>
