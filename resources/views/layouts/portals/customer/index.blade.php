<div class="min-h-screen bg-neutral text-base-content">
    <div class="flex min-h-screen">
        @include('layouts.portals.customer.partials.sidebar')

        <div class="flex min-w-0 flex-1 flex-col">
            @include('layouts.portals.customer.partials.top-nav')

            <main class="w-full flex-1 px-4 py-5 pb-24 md:px-6 md:py-6 md:pb-6">
                <div class="mx-auto w-full max-w-6xl">
                    @include('layouts.portals.customer.partials.alerts')
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @include('layouts.portals.customer.partials.bottom-nav')
</div>
