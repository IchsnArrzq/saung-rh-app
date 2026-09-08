<div wire:poll.15s class="space-y-4">
    @if (session('success'))
        <div class="alert alert-success py-2 text-sm">
            <i class="ri-checkbox-circle-line"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-search-input class="w-full sm:max-w-xs" placeholder="Cari meja / kode..."
            wire:model.live.debounce.300ms="search" />
        <span class="text-xs tabular-nums text-secondary">{{ $tables->count() }} meja</span>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($tables as $table)
            @php($current = \App\Domains\Table\Enums\TableStatus::tryFrom((string) $table->status))
            <div class="card border border-base-300 bg-base-100 rounded-xl p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-bold text-base-content">{{ $table->name }}</h3>
                        <p class="text-xs text-secondary">{{ $table->code }} · {{ $table->capacity }} kursi</p>
                    </div>
                    <x-status-badge :status="$current" size="sm" class="whitespace-nowrap font-semibold" />
                </div>

                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach ($statuses as $status)
                        <x-button size="xs" :variant="$status === $current ? 'primary' : 'outline'"
                            wire:click="updateStatus('{{ $table->id }}', '{{ $status->value }}')"
                            :disabled="$status === $current">
                            {{ $status->label() }}
                        </x-button>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-10 text-secondary text-sm">Tidak ada meja yang cocok.</div>
        @endforelse
    </div>
</div>
