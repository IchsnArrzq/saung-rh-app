<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Pengaturan Aplikasi</h2>
    </x-slot>

    <div class="space-y-6">
        <livewire:admin.system.brand-assets-manager />

        <livewire:admin.system.app-settings-manager />
    </div>
</x-admin-layout>
