<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="autumn">

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

<body class="min-h-screen bg-stone-800  text-stone-800 " style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <div
        class="mx-auto w-full  overflow-hidden rounded-[2.2rem] border border-amber-100/70 bg-amber-50 shadow-[0_28px_90px_rgba(0,0,0,0.35)]">
        <header class="border-b border-stone-200/70 bg-amber-50 px-5 py-4 md:px-8 md:py-5">
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ url('/') }}" class="mr-auto text-3xl font-semibold leading-none text-emerald-800"
                    style="font-family: 'Playfair Display', serif;">
                    SaungRH<span class="text-orange-500">.</span>
                </a>

                <nav class="order-3 w-full md:order-none md:w-auto">
                    <ul class="flex flex-wrap items-center gap-1 md:gap-2">
                        <li>
                            <a href="{{ url('/') }}"
                                class="rounded-full px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-white">
                                Home
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/') }}#menu"
                                class="rounded-full px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-white">
                                Menu
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/') }}#highlight"
                                class="rounded-full px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-white">
                                Highlight
                            </a>
                        </li>
                    </ul>
                </nav>

                <div class="flex items-center gap-2">
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}"
                            class="rounded-full border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-emerald-800 hover:text-emerald-800">
                            Login
                        </a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="rounded-full bg-amber-300 px-4 py-2 text-sm font-semibold text-stone-900 transition hover:bg-amber-400">
                            Register
                        </a>
                    @endif
                </div>
            </div>
        </header>

        <main class="px-4 py-5 md:px-8 md:py-7">
            {{ $slot }}
        </main>

        <footer class="border-t border-stone-200/70 bg-amber-100/50 px-5 py-10 md:px-8">
            <div class="grid gap-8 md:grid-cols-4">
                <div>
                    <p class="text-4xl font-semibold text-emerald-800" style="font-family: 'Playfair Display', serif;">
                        SaungRH<span class="text-orange-500">.</span>
                    </p>
                    <p class="mt-3 text-sm text-stone-700">
                        Menikmati menu favorit dengan rasa hangat rumahan dan presentasi modern.
                    </p>
                </div>

                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-stone-500">Visit Us</p>
                    <p class="mt-3 text-sm text-stone-700">Jl. Bunga Raya No. 30</p>
                    <p class="text-sm text-stone-700">Bandung, Indonesia</p>
                    <p class="mt-3 text-sm font-semibold text-stone-900">Open: 08.00 - 21.00</p>
                </div>

                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-stone-500">Know More</p>
                    <ul class="mt-3 space-y-2 text-sm text-stone-700">
                        <li><a href="{{ url('/') }}#menu" class="hover:text-emerald-800">Restaurants</a></li>
                        <li><a href="{{ url('/') }}#menu" class="hover:text-emerald-800">Burger Menu</a></li>
                        <li><a href="{{ url('/') }}#highlight" class="hover:text-emerald-800">Chicken Specials</a>
                        </li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-stone-500">Help Centre</p>
                    <ul class="mt-3 space-y-2 text-sm text-stone-700">
                        <li>Order</li>
                        <li>Payment</li>
                        <li>Tracking</li>
                        <li>Privacy Policy</li>
                    </ul>
                </div>
            </div>

            <div class="mt-9 border-t border-stone-200 pt-6">
                <p class="mb-4 text-sm font-semibold text-stone-600">Social Profiles</p>
                <div class="flex flex-wrap gap-3">
                    <span class="rounded-full bg-emerald-800 px-5 py-2 text-xs font-semibold text-white">LinkedIn</span>
                    <span class="rounded-full bg-orange-500 px-5 py-2 text-xs font-semibold text-white">Instagram</span>
                    <span
                        class="rounded-full bg-amber-300 px-5 py-2 text-xs font-semibold text-stone-900">Facebook</span>
                    <span
                        class="rounded-full bg-stone-200 px-5 py-2 text-xs font-semibold text-stone-700">YouTube</span>
                </div>
            </div>
        </footer>
    </div>
    @livewireScripts
</body>

</html>
