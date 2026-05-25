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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-neutral text-base-content antialiased" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <div
        class="relative mx-auto min-h-screen w-full max-w-[1560px] overflow-hidden border border-base-300 bg-base-100 shadow-[0_30px_100px_rgba(0,0,0,0.35)]">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(39,39,42,0.12),transparent_55%)]">
        </div>

        <header class="relative z-30 border-b border-base-300/90 bg-base-100/95 px-4 py-4 backdrop-blur md:px-8">
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('public.home') }}" class="mr-auto inline-flex items-center gap-3"
                    aria-label="CR Cafe & Resto">
                    <img src="{{ asset('assets/logo-cr-cafe-resto.png') }}" alt="CR Cafe & Resto logo"
                        class="h-11 w-auto">
                </a>

                <nav class="order-3 w-full md:order-none md:w-auto">
                    <ul class="flex flex-wrap items-center gap-2 text-sm font-semibold text-secondary">
                        <li>
                            <a href="{{ route('public.home') }}"
                                class="rounded-full px-4 py-2 transition hover:bg-base-200 hover:text-base-content">
                                Home
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('public.menu') }}#menu"
                                class="rounded-full px-4 py-2 transition hover:bg-base-200 hover:text-base-content">
                                Menu
                            </a>
                        </li>
                    </ul>
                </nav>

                <div class="flex items-center gap-2">
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}"
                            class="btn btn-sm btn-outline border-base-300 bg-base-100 text-base-content hover:border-secondary hover:bg-base-200">
                            Login
                        </a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-sm btn-primary">
                            Register
                        </a>
                    @endif
                </div>
            </div>
        </header>

        <main class="relative px-4 py-6 md:px-8 md:py-8">
            {{ $slot }}
        </main>

        <footer class="relative border-t border-base-300 bg-base-200/70 px-4 py-10 md:px-8">
            <div class="grid gap-8 md:grid-cols-4">
                <div class="md:col-span-2">
                    <div class="inline-flex items-center gap-3">
                        <img src="{{ asset('assets/logo-cr-mark.png') }}" alt="CR logo mark"
                            class="h-10 w-10 rounded-lg border border-base-300 bg-base-100 p-1 object-contain">
                        <p class="text-xl font-semibold text-base-content"
                            style="font-family: 'Playfair Display', serif;">
                            CR Cafe & Resto
                        </p>
                    </div>
                    <p class="mt-3 max-w-xl text-sm text-secondary">
                        Menu crafted for balance, texture, and flavor. Built for guests who enjoy clean plating and
                        warm,
                        honest taste.
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Visit</p>
                    <p class="mt-3 text-sm text-base-content">Jl. Bunga Raya No. 30</p>
                    <p class="text-sm text-base-content">Bandung, Indonesia</p>
                    <p class="mt-3 text-sm font-semibold text-base-content">08.00 - 21.00</p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Connect</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span
                            class="rounded-full border border-base-300 bg-base-100 px-3 py-1 text-xs font-semibold text-base-content">Instagram</span>
                        <span
                            class="rounded-full border border-base-300 bg-base-100 px-3 py-1 text-xs font-semibold text-base-content">Facebook</span>
                        <span
                            class="rounded-full border border-base-300 bg-base-100 px-3 py-1 text-xs font-semibold text-base-content">YouTube</span>
                    </div>
                </div>
            </div>

            <p class="mt-8 border-t border-base-300 pt-5 text-xs text-secondary">
                &copy; {{ now()->year }} CR Cafe & Resto. All rights reserved.
            </p>
        </footer>
    </div>

    @livewireScripts
</body>

</html>
