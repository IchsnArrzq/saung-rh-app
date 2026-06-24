<div wire:poll.15s class="space-y-4">
    @if (session('success'))
        <div class="alert alert-success py-2 text-sm">
            <i class="ri-checkbox-circle-line"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <label class="input input-bordered flex items-center gap-2 w-full sm:max-w-xs">
            <i class="ri-search-line text-secondary"></i>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari meja / kode..." class="grow">
        </label>
        <span class="text-xs text-secondary">{{ $tables->count() }} meja</span>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($tables as $table)
            @php $key = $table->tableStatus?->key; @endphp
            <div class="card border border-base-300 bg-base-100 rounded-xl p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-bold text-base-content">{{ $table->name }}</h3>
                        <p class="text-xs text-secondary">{{ $table->code }} · {{ $table->capacity }} kursi</p>
                    </div>
                    <span class="badge badge-sm font-semibold whitespace-nowrap"
                        style="{{ $table->tableStatus?->color ? 'background-color:'.$table->tableStatus->color.';border-color:'.$table->tableStatus->color.';color:#fff;' : '' }}">
                        {{ $table->tableStatus?->name ?? '—' }}
                    </span>
                </div>

                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach ($statuses as $status)
                        <button
                            wire:click="updateStatus('{{ $table->id }}', '{{ $status->id }}')"
                            @disabled($status->id === $table->table_status_id)
                            class="btn btn-xs {{ $status->id === $table->table_status_id ? 'btn-primary' : 'btn-outline' }}">
                            {{ $status->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-10 text-secondary text-sm">Tidak ada meja yang cocok.</div>
        @endforelse
    </div>
</div>
