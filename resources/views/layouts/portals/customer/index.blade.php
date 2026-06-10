<div class="min-h-screen bg-stone-100 text-stone-800">
    @include('layouts.portals.customer.partials.top-nav')

    <main class="mx-auto w-full max-w-6xl px-4 py-6 pb-24 md:px-6 md:py-8 md:pb-8">
        @include('layouts.portals.customer.partials.alerts')
        {{ $slot }}
    </main>

    @include('layouts.portals.customer.partials.bottom-nav')
</div>
