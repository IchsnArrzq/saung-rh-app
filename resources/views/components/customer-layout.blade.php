<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="autumn">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Saung RH') }} - Customer</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-stone-100 text-stone-800" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex w-full max-w-6xl items-center gap-3 px-4 py-4 md:px-6">
            <a href="{{ route('public.home') }}" class="text-2xl font-semibold text-emerald-800"
                style="font-family: 'Playfair Display', serif;">
                SaungRH<span class="text-orange-500">.</span>
            </a>

            <nav class="ml-auto">
                <ul class="flex items-center gap-2 text-sm font-semibold">
                    <li>
                        <a href="{{ route('customer.dashboard') }}"
                            class="rounded-full px-4 py-2 {{ request()->routeIs('customer.dashboard') ? 'bg-emerald-800 text-amber-50' : 'text-stone-700 hover:bg-stone-100' }}">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('customer.menus.tables') }}"
                            class="rounded-full px-4 py-2 {{ request()->routeIs('customer.menus.*') ? 'bg-emerald-800 text-amber-50' : 'text-stone-700 hover:bg-stone-100' }}">
                            Menu
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('customer.bookings.create') }}"
                            class="rounded-full px-4 py-2 {{ request()->routeIs('customer.bookings.*') ? 'bg-emerald-800 text-amber-50' : 'text-stone-700 hover:bg-stone-100' }}">
                            Booking
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('profile') }}" class="rounded-full px-4 py-2 text-stone-700 hover:bg-stone-100">
                            Profil
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl px-4 py-6 md:px-6 md:py-8">
        @if (session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <p class="font-semibold">Periksa kembali input berikut:</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot }}
    </main>
</body>

</html>
