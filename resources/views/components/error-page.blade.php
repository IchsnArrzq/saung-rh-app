@props([
    'code',
    'title',
    'icon' => 'ri-error-warning-line',
    'tone' => 'primary',
    'exception' => null,
])

{{--
    Kerangka halaman kesalahan (resources/views/errors/*): dokumen HTML lengkap,
    tanpa Livewire dan tanpa query yang wajib berhasil. Halaman ini justru tampil
    saat sesuatu sedang bermasalah — termasuk database — jadi nama & ikon bisnis
    dibaca lewat rescue() dengan cadangan bawaan aplikasi.

    Pesan dari abort(4xx, '…') ditampilkan hanya bila memang ditulis untuk manusia
    (kalimat Indonesia dari kode kita, mis. "QR meja tidak valid…"). Pesan bawaan
    framework/paket (Inggris, berisi nama kelas) dan semua pesan 5xx tidak pernah
    dirender — CLAUDE.md § C: jangan buang pesan exception mentah ke layar.
--}}

@php
    $profile = rescue(fn () => app(\App\Domains\System\Services\BusinessProfile::class), null, false);
    $brandName = rescue(fn () => $profile?->name(), null, false) ?: config('app.name');
    $brandMark = rescue(fn () => $profile?->markUrl(), null, false) ?: asset('assets/logo-cr-mark.png');
    $homeUrl = \Illuminate\Support\Facades\Route::has('public.home') ? route('public.home') : url('/');

    $status = (int) $code;
    $detail = trim((string) ($exception?->getMessage() ?? ''));
    $showDetail = $status < 500
        && $detail !== ''
        && ! str_contains($detail, '\\')
        // Kalimat bawaan framework/paket berbahasa Inggris: "The route … could not
        // be found.", "The GET method is not supported …", "User does not have …".
        && ! preg_match('/^(the |user |no query results|this action|unauthenticated|unauthorized|forbidden|not found|too many|page expired|csrf|invalid signature|route \[|service unavailable|server error|method )/i', $detail);

    $toneClass = [
        'primary' => 'bg-primary/10 text-primary',
        'warning' => 'bg-warning/15 text-warning',
        'info' => 'bg-info/15 text-info',
        'error' => 'bg-error/10 text-error',
    ][$tone] ?? 'bg-primary/10 text-primary';
@endphp

<!DOCTYPE html>
<html lang="id" data-theme="cr-cafe-resto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <title>{{ $title }} · {{ $brandName }}</title>

    @include('layouts.partials.theme-script')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-neutral text-base-content antialiased">
    <main class="flex min-h-screen flex-col items-center justify-center gap-6 px-4 py-10">
        <a href="{{ $homeUrl }}"
            class="inline-flex items-center gap-3 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary">
            <img src="{{ $brandMark }}" alt="" class="h-10 w-10 rounded-lg object-contain">
            <span class="text-lg font-semibold">{{ $brandName }}</span>
        </a>

        <section class="w-full max-w-lg rounded-xl bg-base-100 p-6 text-center md:p-10" aria-labelledby="error-title">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-xl text-3xl {{ $toneClass }}">
                <i class="{{ $icon }}" aria-hidden="true"></i>
            </div>

            <p class="mt-5 text-6xl font-bold leading-none tabular-nums text-base-content/15" aria-hidden="true">{{ $code }}</p>

            <h1 id="error-title" class="mt-3 text-2xl font-semibold">{{ $title }}</h1>

            <div class="mx-auto mt-3 max-w-md text-base text-base-content/70">{{ $slot }}</div>

            @if ($showDetail)
                <p class="mx-auto mt-5 max-w-md rounded-lg bg-base-200 px-4 py-3 text-sm text-base-content/80">{{ $detail }}</p>
            @endif

            @isset($actions)
                <div class="mt-8 flex flex-wrap items-center justify-center gap-2">{{ $actions }}</div>
            @endisset
        </section>

        <p class="text-xs text-base-content/50">
            Kode kesalahan <span class="tabular-nums">{{ $code }}</span> ·
            <time class="tabular-nums" datetime="{{ now()->toIso8601String() }}">{{ now()->format('d/m/Y H.i') }}</time>
        </p>
    </main>
</body>

</html>
