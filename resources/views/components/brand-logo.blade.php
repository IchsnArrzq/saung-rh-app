@props([
    'variant' => 'full',
    'fallback' => 'image',
])

{{--
    Logo bisnis dari Pengaturan Aplikasi (BusinessProfile), bukan berkas yang
    ditulis di halaman.

    variant  : full (wordmark memanjang) · mark (persegi, untuk kotak kecil)
    fallback : image    — pakai berkas bawaan aplikasi selama belum ada unggahan
               initials — pakai monogram nama; dipakai navigasi supaya kotak
                          40 px tidak berubah tampilan sampai admin benar-benar
                          mengunggah ikonnya sendiri.
--}}

@php
    // $business dibagikan ke semua view oleh AppServiceProvider; resolusi manual
    // hanya jaring pengaman untuk view yang dirender di luar request web.
    $profile = $business ?? app(\App\Domains\System\Services\BusinessProfile::class);

    $isMark = $variant === 'mark';
    $hasUpload = $isMark ? $profile->hasCustomMark() : $profile->hasCustomLogo();
@endphp

@if ($fallback === 'initials' && ! $hasUpload)
    <span
        {{ $attributes->class('inline-flex shrink-0 items-center justify-center rounded-box bg-primary font-bold text-primary-content') }}>
        {{ $profile->initials() }}
    </span>
@else
    <img src="{{ $isMark ? $profile->markUrl() : $profile->logoUrl() }}" alt="Logo {{ $profile->name() }}"
        {{ $attributes->class('shrink-0 object-contain') }}>
@endif
