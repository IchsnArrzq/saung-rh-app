<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="cr-cafe-resto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $business->name() }}</title>

    @include('layouts.partials.theme-script')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class=" bg-neutral text-base-content antialiased">
    <main
        class="grid w-full min-h-screen  bg-base-100 shadow-[0_26px_90px_rgba(0,0,0,0.35)] md:grid-cols-[1fr_1.1fr]">
        <section class="flex items-center px-5 py-10 md:px-10">
            <div class="w-full">
                <a href="{{ url('/') }}" class="inline-flex items-center" aria-label="{{ $business->name() }}">
                    <img src="{{ asset('assets/logo-cr-cafe-resto.png') }}" alt="Logo {{ $business->name() }}"
                        class="h-20 w-auto">
                </a>
                <p class="mt-2 text-sm font-medium text-stone-500 md:hidden">{{ $business->tagline() }}</p>
                <div class="mt-8 max-w-lg ">
                    {{ $slot }}
                </div>
                <p class="mt-8 text-sm text-stone-500">
                    <a href="{{ url('/') }}"
                        class="inline-flex items-center gap-1 font-semibold text-primary hover:underline">
                        <i class="ri-arrow-left-line"></i> Kembali ke beranda</a>
                </p>
            </div>
        </section>

        <aside class="relative hidden overflow-hidden md:block">
            <img src="{{ asset('assets/media/stock/900x600/12.jpg') }}"
                alt="Chef sedang menyiapkan hidangan" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/45 to-black/10"></div>

            <div class="relative flex h-full flex-col justify-end gap-8 p-12 text-white">
                <div>
                    <h2 class="text-3xl font-extrabold leading-tight lg:text-4xl">
                        Kelola cafe &amp; resto Anda,<br>lebih pintar.
                    </h2>
                    <p class="mt-3 max-w-md text-white/80">
                        Satu platform untuk pesanan, dapur, kasir, dan laporan — semuanya real-time.
                    </p>
                </div>

                <ul class="space-y-4">
                    @foreach ([
                        ['ri-qr-code-line', 'Pesan mandiri lewat QR di meja'],
                        ['ri-restaurant-2-line', 'Dapur & bar sinkron real-time (KDS)'],
                        ['ri-line-chart-line', 'Laporan penjualan otomatis'],
                    ] as [$icon, $label])
                        <li class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/25 backdrop-blur">
                                <i class="{{ $icon }} text-xl"></i>
                            </span>
                            <span class="text-sm font-medium text-white/90">{{ $label }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </main>
    @livewireScripts
</body>

</html>
