@props([
    'label' => null,
    'name' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'size' => 'md',
    'placeholder' => null,
    'searchPlaceholder' => null,
    'options' => null,
    'selected' => null,
    'disabled' => false,
    'bare' => false,
    'enhance' => true,
    'fieldClass' => null,
])

{{--
    Isi opsi lewat slot, atau lewat `:options`:
      - [value => label]        -> opsi biasa
      - [label, label, ...]     -> value = label
      - [grup => [value => label]] -> <optgroup>

    Setiap <select> otomatis di-upgrade jadi Tom Select oleh resources/js/enhance.js:
    ada kotak pencarian di dalam dropdown dan tombol clear (kecuali `required`).
    Set `:enhance="false"` untuk memaksa select bawaan browser.

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

    // Tom Select membaca ini untuk teks abu-abu saat belum ada pilihan, dan
    // untuk placeholder kotak pencariannya.
    $enhanceAttributes = $enhance
        ? array_filter([
            'data-placeholder' => $placeholder ?: ($label ? 'Pilih ' . lcfirst($label) : null),
            'data-search-placeholder' => $searchPlaceholder,
            // Field wajib tidak punya keadaan kosong yang sah, jadi tombol clear-nya dimatikan.
            'data-clearable' => $required ? 'false' : null,
        ])
        : ['data-enhance' => 'off'];

    $isSelected = fn ($value) => $selected !== null && (string) $selected === (string) $value;

    // Tanpa wire:model field ini adalah kontrol HTML biasa di dalam <form> asli,
    // jadi ia butuh `name` (kalau tidak, nilainya tidak ikut terkirim) dan
    // `required` sungguhan. Field Livewire tidak: nilainya sudah lewat wire:model.
    $isWired = $attributes->whereStartsWith('wire:model')->isNotEmpty();

    $nativeAttributes = $isWired ? [] : array_filter([
        'name' => $name,
        'required' => $required ?: null,
    ]);
@endphp

@php
    // Satu-satunya sumber markup opsi; dipakai kedua cabang di bawah.
    $renderOptions = function (array $options) use (&$renderOptions, $isSelected) {
        $html = '';

        // Cek sekali per level, bukan per key: `[3 => 'Kopi']` (id dari DB) tetap
        // punya value 3, sementara `['S', 'M', 'L']` pakai labelnya sebagai value.
        $isList = array_is_list($options);

        foreach ($options as $value => $text) {
            if (is_array($text)) {
                $html .= '<optgroup label="' . e($value) . '">' . $renderOptions($text) . '</optgroup>';

                continue;
            }

            $optionValue = $isList ? $text : $value;

            $html .= '<option value="' . e($optionValue) . '"'
                . ($isSelected($optionValue) ? ' selected' : '')
                . '>' . e($text) . '</option>';
        }

        return $html;
    };
@endphp

@if ($bare)
    <select @disabled($disabled)
        @if ($inputId) id="{{ $inputId }}" @endif
        @if ($errorMessage) aria-invalid="true" @endif
        @if ($label) aria-label="{{ $label }}" @endif
        {{ $attributes->except('id')->merge($nativeAttributes)->merge($enhanceAttributes)->merge(['class' => $classes]) }}>
        @if ($placeholder !== null)
            <option value="" @selected($isSelected(''))>{{ $placeholder }}</option>
        @endif
        @if (is_array($options))
            {!! $renderOptions($options) !!}
        @endif
        {{ $slot }}
    </select>
@else
    <x-field :label="$label" :name="$name" :hint="$hint" :error="$error" :required="$required" :for="$inputId"
        class="{{ $fieldClass }}">
        <select @disabled($disabled)
            @if ($inputId) id="{{ $inputId }}" @endif
            @if ($errorMessage) aria-invalid="true" @endif
            {{ $attributes->except('id')->merge($nativeAttributes)->merge($enhanceAttributes)->merge(['class' => $classes]) }}>

            @if ($placeholder !== null)
                <option value="" @selected($isSelected(''))>{{ $placeholder }}</option>
            @endif

            @if (is_array($options))
                {!! $renderOptions($options) !!}
            @endif

            {{ $slot }}
        </select>
    </x-field>
@endif
