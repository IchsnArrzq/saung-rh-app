<x-app-layout>
    {{-- Tamu yang baru scan QR meja menunggu kasir; bloknya hanya muncul kalau ada yang menunggu. --}}
    @can('viewAny', App\Models\TableSession::class)
        <livewire:staff.table-session-approvals :compact="true" />
    @endcan

    <livewire:pos.table-bills />
</x-app-layout>
