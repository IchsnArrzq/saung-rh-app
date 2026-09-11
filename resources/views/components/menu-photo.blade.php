@props([
    'src' => null,
    'alt' => '',
    'compact' => false,
])

{{--
    Foto menu dengan cadangan yang jujur — satu tempat untuk semua katalog,
    keranjang, dan modal detail, supaya sumbernya selalu sama:
    `$menu->display_image_url` (gambar utama dari Media menu, lalu kolom lama
    `image_url`), bukan foto stok atau acak.

      <x-menu-photo :src="$menu->display_image_url" :alt="$menu->name" class="aspect-[4/3]" />

    - `src` kosong → langsung tampil cadangan "Foto belum tersedia".
    - `src` gagal dimuat (404, berkas terhapus) → onerror menyembunyikan <img>
      dan memunculkan cadangan yang sama.

    wire:key mengikuti src: foto yang berganti membuat elemen baru, bukan
    ditambal. wire:ignore menjaga keadaan onerror dari morph Livewire berikutnya —
    tanpa itu morph mengembalikan <img> rusak yang tadi sudah disembunyikan.

    `compact` untuk thumbnail kecil (keranjang): ikon saja, tanpa teks.
--}}

@php
    $src = filled($src) ? (string) $src : null;
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden bg-base-200']) }}
    wire:key="photo-{{ $src ? substr(md5($src), 0, 12) : 'none' }}">
    <div wire:ignore class="h-full w-full">
        @if ($src)
            <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" decoding="async"
                class="h-full w-full object-cover"
                onerror="this.hidden = true; this.nextElementSibling.hidden = false;">
        @endif

        <div @if ($src) hidden @endif
            class="absolute inset-0 flex flex-col items-center justify-center gap-1 p-2 text-center text-base-content/40">
            <i class="ri-restaurant-2-line {{ $compact ? 'text-xl' : 'text-4xl' }}" aria-hidden="true"></i>
            @if ($compact)
                <span class="sr-only">Foto {{ $alt }} belum tersedia</span>
            @else
                <span class="text-xs font-medium">Foto belum tersedia</span>
            @endif
        </div>
    </div>
</div>
