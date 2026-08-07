@props([
    'title',
    'description' => null,
])

{{--
    Kepala halaman: judul + deskripsi opsional + slot `actions`.
    Satu btn-primary saja di sini — DESIGN.md "Button Hierarchy".
--}}

<section {{ $attributes->merge(['class' => 'rounded-xl border border-base-300 bg-base-100 p-4 md:p-5']) }}>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold">{{ $title }}</h1>
            @if ($description)
                <p class="mt-0.5 text-sm text-base-content/70">{{ $description }}</p>
            @endif
            {{ $slot }}
        </div>

        @isset($actions)
            <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
        @endisset
    </div>
</section>
