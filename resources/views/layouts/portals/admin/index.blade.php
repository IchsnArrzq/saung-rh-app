@php
    $navigationMenuPreference = (string) (auth()->user()?->navigation_menu_preference ?? 'sidebar');
    if (!in_array($navigationMenuPreference, ['sidebar', 'navbar'], true)) {
        $navigationMenuPreference = 'sidebar';
    }
@endphp

<div
    @class([
        'min-h-screen border border-base-300 bg-base-100 shadow-[0_26px_90px_rgba(0,0,0,0.35)]',
        'drawer lg:drawer-open' => $navigationMenuPreference === 'sidebar',
    ])>
    @if ($navigationMenuPreference === 'sidebar')
        <input id="admin-drawer" checked type="checkbox" class="drawer-toggle">
    @endif
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
    @if ($navigationMenuPreference === 'sidebar')
        @include('layouts.portals.admin.partials.sidebar')
    @endif
</div>
