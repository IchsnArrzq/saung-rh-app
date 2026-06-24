<x-admin-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold">{{ $title }}</h2>
            <p class="mt-1 text-sm text-secondary">{{ $subtitle }}</p>
        </div>
    </x-slot>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($modules as $module)
            @php
                $hasLink = isset($module['route']) && \Illuminate\Support\Facades\Route::has($module['route']);
                $url = $hasLink ? route($module['route']) : null;
                $isActive = ($module['phase'] ?? '') === 'Aktif';
            @endphp
            <a
                @if ($hasLink) href="{{ $url }}" wire:navigate @else href="#" onclick="return false;" @endif
                class="card border border-base-300 bg-base-100 rounded-xl p-5 transition {{ $hasLink ? 'hover:border-primary hover:shadow-sm' : 'cursor-default opacity-90' }}"
            >
                <div class="flex items-start justify-between gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-primary/15 text-primary text-xl">
                        <i class="{{ $module['icon'] }}"></i>
                    </span>
                    <span class="badge badge-sm {{ $isActive ? 'badge-success' : 'badge-ghost' }}">{{ $module['phase'] }}</span>
                </div>
                <h3 class="mt-3 font-semibold">{{ $module['label'] }}</h3>
                <p class="mt-1 text-sm text-secondary">{{ $module['desc'] }}</p>
                @unless ($hasLink)
                    <p class="mt-3 text-xs text-secondary/70">Segera hadir.</p>
                @endunless
            </a>
        @endforeach
    </section>
</x-admin-layout>
