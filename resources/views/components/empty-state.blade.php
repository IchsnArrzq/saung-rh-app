@props([
    'icon' => 'ri-inbox-line',
    'title' => 'Belum ada data',
    'description' => null,
    'dashed' => true,
])

{{--
    Struktur wajib menurut DESIGN.md "Empty States": Ikon → Pesan → Aksi.
    Empty state adalah tempat terbaik menaruh CTA — isi slot `actions`.
--}}

<div {{ $attributes->merge([
    'class' => 'rounded-xl border ' . ($dashed ? 'border-dashed ' : '') . 'border-base-300 bg-base-100 p-8 text-center',
]) }}>
    <i class="{{ $icon }} text-4xl text-base-content/30" aria-hidden="true"></i>

    <p class="mt-3 font-medium text-base-content/70">{{ $title }}</p>

    @if ($description)
        <p class="mt-1 text-sm text-base-content/50">{{ $description }}</p>
    @endif

    {{ $slot }}

    @isset($actions)
        <div class="mt-4 flex flex-wrap items-center justify-center gap-2">{{ $actions }}</div>
    @endisset
</div>
