<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kitchen Display System') }}
            </h2>
            <span class="flex items-center text-sm font-medium text-green-600 bg-green-50 px-3 py-1 rounded-full border border-green-200">
                <span class="animate-pulse h-2.5 w-2.5 bg-green-500 rounded-full mr-2"></span>
                Live
            </span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
            @livewire('kds.board')
        </div>
    </div>
</x-app-layout>
