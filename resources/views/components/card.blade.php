@props([
    'title' => null,
    'description' => null,
    'padding' => 'md',
    'flush' => false,
])

{{--
    Pembungkus standar semua grup konten. Tema ini `--border:0 --depth:0`,
    jadi pemisahan datang dari base-300, bukan shadow — lihat DESIGN.md "Card".

    Slot opsional: `actions` (kanan atas), `footer`.
    `flush` mematikan padding untuk isi yang mengatur padding sendiri (tabel).
--}}

@php
    $paddingClass = $flush ? '' : ([
        'sm' => 'p-3',
        'md' => 'p-4 md:p-5',
        'lg' => 'p-6',
    ][$padding] ?? 'p-4 md:p-5');

    $hasHeader = $title !== null || $description !== null || isset($actions);
@endphp

<section {{ $attributes->merge(['class' => 'rounded-xl border border-base-300 bg-base-100 ' . $paddingClass]) }}>
    @if ($hasHeader)
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                @if ($title)
                    <h2 class="text-lg font-semibold">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="mt-0.5 text-sm text-base-content/70">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    {{ $slot }}

    @isset($footer)
        <div class="mt-4 border-t border-base-300 pt-4">{{ $footer }}</div>
    @endisset
</section>
