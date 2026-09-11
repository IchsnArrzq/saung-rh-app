<div class="space-y-5">
    @include('admin.partials.flash')

    @error('session')
        <x-alert type="error">{{ $message }}</x-alert>
    @enderror

    <x-card>
        <div class="flex flex-wrap items-center gap-2">
            <x-search-input class="max-w-md" wire:model.live.debounce.300ms="search"
                placeholder="Cari nama pemesan atau kode meja..." label="Cari sesi meja" />

            {{-- check-ui-allow: filter status berupa select; hasilnya memang harus langsung terlihat. --}}
            <x-select wire:model.live="status" :bare="true" class="w-48" label="Filter status"
                placeholder="Semua status" :options="$statusOptions" />

            @if ($search !== '' || $status !== '')
                <x-button variant="ghost" size="sm" wire:click="resetFilters" loading="resetFilters">Reset</x-button>
            @endif
        </div>
    </x-card>

    <x-data-table>
        <x-slot:head>
            <tr>
                <th>Meja</th>
                <th>Nama pemesan</th>
                <th class="text-right">Orang</th>
                <th>Status</th>
                <th>Mulai</th>
                <th>Disetujui oleh</th>
                <th>Selesai</th>
                <th class="text-right">Pesanan</th>
                <th class="w-px text-right">Aksi</th>
            </tr>
        </x-slot:head>

        <tr class="hidden" wire:loading.delay.class.remove="hidden"
            wire:target="search, status, resetFilters, gotoPage, nextPage, previousPage">
            <td colspan="9"><x-skeleton :rows="4" /></td>
        </tr>

        @forelse ($sessions as $session)
            <tr wire:key="table-session-{{ $session->id }}">
                <td class="font-semibold tabular-nums">{{ $session->table?->code ?? '—' }}</td>
                <td>{{ $session->customer_name ?: '—' }}</td>
                <td class="text-right tabular-nums">{{ $session->pax ?? '—' }}</td>
                <td><x-status-badge :status="$session->status" size="sm" /></td>
                <td class="whitespace-nowrap tabular-nums">{{ $session->started_at?->format('d M Y H:i') ?? '—' }}</td>
                <td>{{ $session->approver?->name ?? '—' }}</td>
                <td class="whitespace-nowrap">
                    @if ($session->closed_at)
                        <span class="tabular-nums">{{ $session->closed_at->format('d M H:i') }}</span>
                        <span class="block text-sm text-base-content/70">
                            @if ($session->status === \App\Domains\Table\Enums\TableSessionStatus::Rejected)
                                Ditolak{{ $session->closer ? ' oleh '.$session->closer->name : '' }}
                            @else
                                {{ $session->close_reason?->label() ?? 'Selesai' }}{{ $session->closer ? ' · '.$session->closer->name : '' }}
                            @endif
                        </span>
                    @else
                        —
                    @endif
                </td>
                <td class="text-right tabular-nums">{{ $session->orders_count }}</td>
                <td class="text-right">
                    @if ($session->status->isLive())
                        @can('update', $session)
                            <x-button variant="error" :outline="true" size="sm"
                                wire:click="close('{{ $session->id }}')" loading="close('{{ $session->id }}')"
                                data-confirm="Nonaktifkan sesi Meja {{ $session->table?->code }} atas nama {{ $session->customer_name }}? Semua HP di meja itu tidak bisa memesan lagi sampai scan ulang.">
                                Nonaktifkan sesi
                            </x-button>
                        @endcan
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="py-8 text-center text-base-content/60">
                    @if ($search !== '' || $status !== '')
                        Tidak ada sesi yang cocok dengan pencarian ini.
                    @else
                        Belum ada sesi meja. Sesi muncul di sini setelah tamu scan QR di meja.
                    @endif
                </td>
            </tr>
        @endforelse
    </x-data-table>

    <div>{{ $sessions->links() }}</div>
</div>
