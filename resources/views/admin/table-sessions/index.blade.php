<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Sesi meja</h2>
    </x-slot>

    <div class="space-y-6">
        <livewire:staff.table-session-approvals />
        <livewire:admin.table-sessions.table />
    </div>
</x-admin-layout>
