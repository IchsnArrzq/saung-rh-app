<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="autumn">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Admin Resto') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-stone-800 text-stone-800 " style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <div
        class="drawer min-h-[calc(100vh-1.5rem)] border border-amber-100/80 bg-amber-50 shadow-[0_26px_90px_rgba(0,0,0,0.35)] lg:drawer-open">
        <input id="admin-drawer" type="checkbox" class="drawer-toggle">

        <div class="drawer-content">
            <livewire:layout.navigation />

            @isset($header)
                <div class="px-4 pt-5 md:px-6">
                    <div class="rounded-2xl border border-stone-200 bg-white/80 px-5 py-4 text-stone-900">
                        {{ $header }}
                    </div>
                </div>
            @endisset

            <main class="flex-1 px-4 py-5 md:px-6 md:py-6">
                {{ $slot }}
            </main>
        </div>

        <livewire:layout.sidebar />

    </div>
    @livewireScripts
</body>

</html>
