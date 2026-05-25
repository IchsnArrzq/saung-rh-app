<div class="drawer min-h-screen border border-base-300 bg-base-100 shadow-[0_26px_90px_rgba(0,0,0,0.35)] lg:drawer-open">
    <input id="admin-drawer" checked type="checkbox" class="drawer-toggle">
    <div class="drawer-content">
        @include('layouts.portals.admin.partials.topbar')

        @isset($header)
            <div class="px-4 pt-5 md:px-6">
                <div class="rounded-2xl border border-base-300 bg-base-100 px-5 py-4 text-base-content">
                    {{ $header }}
                </div>
            </div>
        @endisset

        <main class="flex-1 px-4 py-5 md:px-6 md:py-6">
            {{ $slot }}
        </main>
    </div>

    @include('layouts.portals.admin.partials.sidebar')
</div>
