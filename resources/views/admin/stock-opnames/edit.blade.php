<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('stock-opnames.index') }}" class="btn btn-sm btn-ghost">
                <i class="ri-arrow-left-line"></i>
            </a>
            <h2 class="text-xl font-semibold">Opname: {{ $opname->code }}</h2>
        </div>
    </x-slot>

    <livewire:admin.stock-opnames.form :opname="$opname" />
</x-admin-layout>
