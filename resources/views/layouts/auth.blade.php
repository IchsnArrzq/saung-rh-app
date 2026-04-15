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
    <main
        class=" grid min-h-[calc(100vh-2.5rem)] w-full  overflow-hidden rounded-[2rem] bg-amber-50 shadow-[0_26px_90px_rgba(0,0,0,0.35)] md:grid-cols-[1fr_1.1fr]">
        <section class="flex items-center px-5 py-10 md:px-10">
            <div class="w-full">
                <a href="{{ url('/') }}" class="inline-flex items-center text-3xl font-semibold text-emerald-800"
                    style="font-family: 'Playfair Display', serif;">
                    SaungRH<span class="text-orange-500">.</span>
                </a>

                <div class="mt-8">
                    <p class="text-xs font-bold uppercase tracking-[0.26em] text-stone-500">Welcome Back</p>
                    <h1 class="mt-3 text-4xl leading-tight text-stone-900 md:text-5xl"
                        style="font-family: 'Playfair Display', serif;">
                        Start your session with fresh ideas.
                    </h1>
                    <p class="mt-3 text-sm text-stone-600">
                        Masuk untuk mengelola menu, pesanan, dan pengalaman pelanggan Saung RH dengan cepat.
                    </p>
                </div>

                <div class="mt-8 max-w-lg ">
                    {{ $slot }}
                </div>

                <p class="mt-6 text-sm text-stone-500">
                    Need public menu? <a href="{{ url('/') }}"
                        class="font-semibold text-emerald-800 hover:underline">Back to
                        homepage</a>
                </p>
            </div>
        </section>

        <aside class="relative hidden overflow-hidden md:block">
            <img src="https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=1500&q=80"
                alt="Chef preparing food" class="h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-emerald-950/85 via-emerald-900/55 to-amber-300/30"></div>

            <div
                class="absolute inset-x-8 top-8 rounded-3xl border border-amber-100/40 bg-amber-300/90 p-5 text-stone-900">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-stone-700">Daily Special</p>
                <p class="mt-2 text-2xl leading-tight" style="font-family: 'Playfair Display', serif;">
                    Bold flavor, smooth workflow.
                </p>
            </div>

            <div
                class="absolute bottom-8 left-8 right-8 rounded-3xl border border-white/20 bg-stone-900/55 p-6 text-amber-50 backdrop-blur-sm">
                <p class="text-xs uppercase tracking-[0.26em] text-amber-200">Kitchen Notes</p>
                <p class="mt-2 text-3xl leading-tight" style="font-family: 'Playfair Display', serif;">
                    Cook with confidence, serve with style.
                </p>
                <p class="mt-3 text-sm text-amber-100/85">
                    Dashboard ini dirancang untuk bantu tim fokus ke rasa, timing, dan kualitas layanan.
                </p>
            </div>
        </aside>
    </main>
    @livewireScripts
</body>

</html>
