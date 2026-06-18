<div class="drawer lg:drawer-open min-h-screen bg-neutral text-base-content">
    <input id="customer-drawer" checked type="checkbox" class="drawer-toggle">

    <div class="drawer-content flex min-h-screen flex-col">
            @include('layouts.portals.customer.partials.top-nav')

            <main class="w-full flex-1 px-4 py-5 pb-24 md:px-6 md:py-6 md:pb-6">
                <div class="mx-auto w-full max-w-[1480px]">
                    @include('layouts.portals.customer.partials.alerts')
                    {{ $slot }}
                </div>
            </main>
    </div>

    @include('layouts.portals.customer.partials.sidebar')

    @include('layouts.portals.customer.partials.bottom-nav')
</div>
