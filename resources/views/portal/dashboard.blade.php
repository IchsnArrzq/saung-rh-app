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

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($modules as $module)
            @php
                $hasLink = isset($module['route']) && \Illuminate\Support\Facades\Route::has($module['route']);
            @endphp

            {{-- Modul tanpa rute dirender sebagai <div>, bukan tautan buntu: kalau tidak
                 bisa dibuka, ia tidak boleh terlihat seperti tautan. --}}
            <{{ $hasLink ? 'a' : 'div' }}
                @if ($hasLink) href="{{ route($module['route']) }}" wire:navigate @endif
                class="rounded-xl border border-base-300 bg-base-100 p-5 transition {{ $hasLink ? 'hover:border-primary hover:bg-base-200' : 'opacity-70' }}">

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/15 text-xl text-primary">
                    <i class="{{ $module['icon'] }}" aria-hidden="true"></i>
                </span>

                <h3 class="mt-3 font-semibold">{{ $module['label'] }}</h3>
                <p class="mt-1 text-sm text-secondary">{{ $module['desc'] }}</p>

                @unless ($hasLink)
                    <p class="mt-3 text-xs text-base-content/60">Belum tersedia.</p>
                @endunless
            </{{ $hasLink ? 'a' : 'div' }}>
        @endforeach
    </section>
</x-admin-layout>
