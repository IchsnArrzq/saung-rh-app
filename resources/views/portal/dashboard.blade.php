@php
    // Hanya modul yang benar-benar boleh dibuka orang ini (RouteAccess membaca
    // middleware rutenya). Kartu yang berakhir di 403 tidak ditampilkan sama sekali.
    $visibleModules = collect($modules)
        ->filter(fn (array $module) => isset($module['route']) && \App\Support\RouteAccess::allows($module['route']))
        ->values();
@endphp

<x-admin-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold">{{ $title }}</h2>
            <p class="mt-1 text-sm text-secondary">{{ $subtitle }}</p>
        </div>
    </x-slot>

    {{-- Hanya dashboard yang meminta (resepsionis): tamu QR yang menunggu dikonfirmasi. --}}
    @if (! empty($approvals))
        @can('viewAny', App\Models\TableSession::class)
            <div class="mb-6">
                <livewire:staff.table-session-approvals />
            </div>
        @endcan
    @endif

    @if ($visibleModules->isEmpty())
        <x-empty-state icon="ri-lock-line" title="Belum ada modul yang bisa dibuka"
            description="Peran Anda belum diberi akses ke modul di portal ini. Minta admin mengatur hak akses di Peran & hak akses." />
    @else
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($visibleModules as $module)
                <a href="{{ route($module['route']) }}" wire:navigate
                    class="rounded-xl bg-base-100 p-5 transition-colors hover:bg-base-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/15 text-xl text-primary">
                        <i class="{{ $module['icon'] }}" aria-hidden="true"></i>
                    </span>

                    <h3 class="mt-3 font-semibold">{{ $module['label'] }}</h3>
                    <p class="mt-1 text-sm text-secondary">{{ $module['desc'] }}</p>
                </a>
            @endforeach
        </section>
    @endif
</x-admin-layout>
