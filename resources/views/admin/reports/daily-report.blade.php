<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-stone-800">
            {{ __('Laporan Harian') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @livewire('admin.reports.daily-report-board')
        </div>
    </div>
</x-admin-layout>
