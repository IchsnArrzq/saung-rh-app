<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="cr-cafe-resto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Resto App') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class=" bg-neutral text-base-content antialiased" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <main
        class="grid w-full min-h-screen  bg-base-100 shadow-[0_26px_90px_rgba(0,0,0,0.35)] md:grid-cols-[1fr_1.1fr]">
        <section class="flex items-center px-5 py-10 md:px-10">
            <div class="w-full">
                <a href="{{ url('/') }}" class="inline-flex items-center" aria-label="CR Cafe & Resto">
                    <img src="{{ asset('assets/logo-cr-cafe-resto.png') }}" alt="CR Cafe & Resto logo"
                        class="h-20 w-auto">
                </a>
                <div class="mt-8 max-w-lg ">
                    {{ $slot }}
                </div>
                <p class="mt-6 text-sm text-stone-500">
                    <a href="{{ url('/') }}"
                        class="font-semibold text-primary hover:underline">Back to
                        homepage</a>
                </p>
            </div>
        </section>

        <aside class="relative hidden overflow-hidden md:block">
            <img src="{{ asset('assets/media/stock/900x600/12.jpg') }}"
                alt="Chef preparing food" class="h-full w-full object-cover">
        </aside>
    </main>
    @livewireScripts
</body>

</html>
