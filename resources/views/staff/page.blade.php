<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            @isset($icon)
                <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-primary/15 text-primary text-xl">
                    <i class="{{ $icon }}"></i>
                </span>
            @endisset
            <div>
                <h2 class="text-xl font-semibold">{{ $title }}</h2>
                <p class="mt-1 text-sm text-secondary">{{ $subtitle }}</p>
            </div>
        </div>
    </x-slot>

    @livewire($component, $params ?? [])
</x-admin-layout>
