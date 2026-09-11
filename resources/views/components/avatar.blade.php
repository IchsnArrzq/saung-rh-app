@props([
    'user' => null,
    'name' => null,
    'src' => null,
    'size' => 'md',
])

{{--
    Foto profil bundar dengan inisial sebagai cadangan.

      <x-avatar :user="$user" size="sm" />

    Inisial selalu dirender di bawah fotonya: kalau foto gagal dimuat, onerror
    membuang <img> dan inisialnya yang terlihat — tidak pernah ikon gambar rusak.
    Dekoratif (alt kosong): nama orangnya selalu tertulis di sebelahnya.
--}}

@php
    $name = (string) ($name ?? ($user?->name ?? ''));
    $src = $src ?? ($user?->avatar_url ?? null);

    $initials = collect(preg_split('/\s+/', trim($name)) ?: [])
        ->filter()
        ->take(2)
        ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');

    $sizeClass = [
        'xs' => 'h-7 w-7 text-xs',
        'sm' => 'h-9 w-9 text-sm',
        'md' => 'h-11 w-11 text-base',
        'lg' => 'h-16 w-16 text-xl',
        'xl' => 'h-24 w-24 text-3xl',
    ][$size] ?? 'h-11 w-11 text-base';
@endphp

<span {{ $attributes->merge(['class' => 'relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary/15 font-semibold text-primary ' . $sizeClass]) }}
    aria-hidden="true">
    <span>{{ $initials !== '' ? $initials : '?' }}</span>

    @if ($src)
        <img src="{{ $src }}" alt="" class="absolute inset-0 h-full w-full object-cover" onerror="this.remove()">
    @endif
</span>
