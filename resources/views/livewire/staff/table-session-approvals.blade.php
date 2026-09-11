{{-- Poll adalah cadangan: kanal private `table-sessions` yang menyegarkan lebih dulu kalau Reverb hidup. --}}
<div wire:poll.15s @class(['mb-6' => $compact && $pending->isNotEmpty()])>
    @if (! $compact || $pending->isNotEmpty())
        <section class="space-y-3">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">Menunggu konfirmasi meja</h2>
                    <p class="text-sm text-base-content/70">
                        Tamu yang baru scan QR meja. Setujui hanya kalau mereka memang duduk di meja itu.
                    </p>
                </div>

                @if ($pending->isNotEmpty())
                    <x-badge color="warning" class="tabular-nums">{{ $pending->count() }} menunggu</x-badge>
                @endif
            </div>

            @if (session('table_session_status'))
                <x-alert type="success">{{ session('table_session_status') }}</x-alert>
            @endif

            @error('session')
                <x-alert type="error">{{ $message }}</x-alert>
            @enderror

            @if ($pending->isEmpty())
                <x-empty-state icon="ri-qr-scan-2-line" title="Belum ada tamu yang menunggu"
                    description="Saat tamu scan QR di meja, permintaannya muncul di sini untuk disetujui." />
            @else
                <x-card :flush="true">
                    <ul class="divide-y divide-base-300">
                        @foreach ($pending as $session)
                            <li wire:key="pending-{{ $session->id }}" class="flex flex-wrap items-center gap-3 px-4 py-3">
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold">
                                        Meja <span class="tabular-nums">{{ $session->table?->code }}</span>
                                        &middot; {{ $session->customer_name }}
                                    </p>
                                    <p class="text-sm text-base-content/70">
                                        <span class="tabular-nums">{{ $session->pax }}</span> orang &middot;
                                        scan pukul <span class="tabular-nums">{{ $session->started_at?->format('H:i') }}</span>
                                    </p>
                                </div>

                                @can('update', $session)
                                    <div class="flex gap-2">
                                        <x-button variant="ghost" size="sm" wire:click="reject('{{ $session->id }}')"
                                            loading="reject('{{ $session->id }}')"
                                            data-confirm="Tolak permintaan Meja {{ $session->table?->code }}? Tamu harus scan ulang.">
                                            Tolak
                                        </x-button>
                                        <x-button variant="accent" size="sm" icon="ri-check-line"
                                            wire:click="approve('{{ $session->id }}')" loading="approve('{{ $session->id }}')">
                                            Setujui
                                        </x-button>
                                    </div>
                                @endcan
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            @endif
        </section>
    @endif
</div>
