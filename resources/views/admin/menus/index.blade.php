<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Menu</h2>
    </x-slot>

    @php
        $activeTab = request()->query('tab', request()->query('view', 'table'));
        if (! in_array($activeTab, ['table', 'card'], true)) {
            $activeTab = 'table';
        }
    @endphp

    <div class="space-y-4">
        <div class="tabs tabs-boxed rounded-2xl border border-base-300 bg-base-100 p-1">
            <a role="tab" wire:navigate href="{{ route('menus.index', ['tab' => 'table']) }}"
                class="tab tab-lg rounded-xl {{ $activeTab === 'table' ? 'tab-active bg-primary text-primary-content' : 'text-secondary' }}">
                Tabel Menu
            </a>
            <a role="tab" wire:navigate href="{{ route('menus.index', ['tab' => 'card']) }}"
                class="tab tab-lg rounded-xl {{ $activeTab === 'card' ? 'tab-active bg-primary text-primary-content' : 'text-secondary' }}">
                Kartu Menu
            </a>
        </div>

        @if ($activeTab === 'card')
            <livewire:admin.menus.menu-card />
        @else
            <livewire:admin.menus.table />
        @endif
    </div>
</x-admin-layout>
