<div>
    @if (session('warning'))
        <x-alert type="warning" class="mb-4">{{ session('warning') }}</x-alert>
    @endif

    @if ($activeTable)
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-primary/30 bg-primary/10 p-4">
            <div class="text-sm">
                <p class="font-semibold"><i class="ri-restaurant-2-line text-primary"></i> Anda sedang di Meja {{ $activeTable->code }}</p>
                <p class="text-base-content/70">Lanjutkan memesan tanpa harus memilih meja lagi.</p>
            </div>
            <x-button variant="primary" size="sm" icon="ri-bowl-line" wire:navigate
                :href="route('customer.menus.index', ['table_id' => $activeTable->id])">
                Lanjut Pesan
            </x-button>
        </div>
    @endif

    <x-page-header title="Pilih Meja" description="Pilih meja yang tersedia untuk mulai memesan menu.">
        <x-search-input class="mt-4 max-w-md" wire:model.live.debounce.300ms="search"
            placeholder="Cari kode meja, status, atau kategori..." label="Cari meja" />
    </x-page-header>

    <section class="mt-5 grid gap-4 xl:grid-cols-4">
        @foreach ($statuses as $status)
            @php $items = $tablesByStatus->get($status->value, collect()); @endphp

            <article class="rounded-xl border border-base-300 bg-base-100 p-3">
                <header class="mb-3 flex items-center justify-between gap-2">
                    <p class="text-sm font-semibold">{{ $status->label() }}</p>
                    <x-badge :color="$status->color()" size="sm">{{ $items->count() }}</x-badge>
                </header>

                <div class="space-y-3 rounded-xl border border-dashed border-base-300 p-2 min-h-[160px]">
                    @forelse ($items as $table)
                        @php $statusKey = (string) $table->status; @endphp
                        <article class="rounded-xl border border-base-300 bg-base-100 p-3 shadow-sm">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="font-semibold">{{ $table->code }}</p>
                                    <p class="text-xs text-base-content/60">{{ $table->name ?: 'Tanpa nama meja' }}</p>
                                </div>
                                <x-badge :color="$status->color()" size="sm" :outline="true">
                                    {{ $status->label() }}
                                </x-badge>
                            </div>

                            <p class="mt-2 text-xs text-base-content/70">
                                Kapasitas {{ $table->capacity }} orang
                                @if ($table->tableCategory)
                                    &middot; {{ $table->tableCategory->name }}
                                @endif
                            </p>

                            <div class="mt-3">
                                @if ($statusKey === 'available')
                                    <x-button variant="primary" size="sm" :block="true"
                                        wire:click="selectTable('{{ $table->id }}')"
                                        loading="selectTable('{{ $table->id }}')">
                                        Pilih Meja
                                    </x-button>
                                @else
                                    <x-button variant="ghost" size="sm" :block="true" disabled>
                                        Tidak Tersedia
                                    </x-button>
                                @endif
                            </div>
                        </article>
                    @empty
                        <p class="rounded-lg bg-base-200/60 px-3 py-4 text-center text-xs text-base-content/50">
                            Tidak ada meja.
                        </p>
                    @endforelse
                </div>
            </article>
        @endforeach

        @if ($unassignedTables->isNotEmpty())
            <article class="rounded-xl border border-base-300 bg-base-100 p-3">
                <header class="mb-3 flex items-center justify-between gap-2">
                    <p class="text-sm font-semibold">Belum Punya Status</p>
                    <x-badge color="ghost" size="sm">{{ $unassignedTables->count() }}</x-badge>
                </header>

                <div class="space-y-3 rounded-xl border border-dashed border-base-300 p-2">
                    @foreach ($unassignedTables as $table)
                        <article class="rounded-xl border border-base-300 bg-base-100 p-3 shadow-sm">
                            <p class="font-semibold">{{ $table->code }}</p>
                            <p class="text-xs text-base-content/60">{{ $table->name ?: 'Tanpa nama meja' }}</p>
                        </article>
                    @endforeach
                </div>
            </article>
        @endif
    </section>
</div>
