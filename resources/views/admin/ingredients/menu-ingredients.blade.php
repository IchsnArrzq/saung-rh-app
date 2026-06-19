<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('menus.index') }}" class="btn btn-sm btn-ghost">
                <i class="ri-arrow-left-line"></i>
            </a>
            <h2 class="text-xl font-semibold">Resep: {{ $menu->name }}</h2>
        </div>
    </x-slot>

    <livewire:admin.menu-ingredients.form :menu="$menu" />
</x-admin-layout>
