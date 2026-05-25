<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="cr-cafe-resto">

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

<body class="min-h-screen bg-neutral text-base-content antialiased" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    @php
        $activePortal = $portal ?? 'admin';
    @endphp

    @if ($activePortal === 'customer')
        @include('layouts.portals.customer.index', ['slot' => $slot])
    @else
        @include('layouts.portals.admin.index', ['slot' => $slot, 'header' => $header ?? null])
    @endif

    @livewireScripts
</body>

</html>
