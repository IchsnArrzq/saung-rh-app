<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Visual Denah Meja</h2>
    </x-slot>

    @php
        $activeTab = request()->query('tab', 'board');
        if (! in_array($activeTab, ['board', 'list'], true)) {
            $activeTab = 'board';
        }
    @endphp

    <div class="space-y-4">
        <div class="tabs tabs-boxed rounded-2xl border border-base-300 bg-base-100 p-1">
            <a role="tab" wire:navigate href="{{ route('tables.index', ['tab' => 'board']) }}"
                class="tab tab-lg rounded-xl {{ $activeTab === 'board' ? 'tab-active bg-primary text-primary-content' : 'text-secondary' }}">
                Drag & Drop Status
            </a>
            <a role="tab" wire:navigate href="{{ route('tables.index', ['tab' => 'list']) }}"
                class="tab tab-lg rounded-xl {{ $activeTab === 'list' ? 'tab-active bg-primary text-primary-content' : 'text-secondary' }}">
                Tabel Data Meja
            </a>
        </div>

        @if ($activeTab === 'board')
            <livewire:admin.tables.status-board />
        @else
            <livewire:admin.tables.table />
        @endif
    </div>
</x-app-layout>
