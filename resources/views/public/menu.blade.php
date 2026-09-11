<x-guest-layout>
    @if ($tableSession?->isPending())
        {{-- Sesi menunggu kasir: menu boleh dilihat, panel meja belum dibuka. --}}
        <x-alert type="warning" class="mb-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <span>
                    Meja {{ $tableSession->table->code }} menunggu konfirmasi kasir. Pesanan, chat, dan permintaan
                    terbuka setelah disetujui.
                </span>
                <x-button variant="ghost" size="sm" icon="ri-time-line"
                    :href="route('checkin.show', ['token' => $tableSession->table->qr_token])">
                    Lihat status
                </x-button>
            </div>
        </x-alert>
    @endif

    <div>
        <livewire:frontend.menu-catalog />
    </div>

    @if ($tableSession?->isActive())
        @php $tableCode = $tableSession->table->code; @endphp

        <style>[x-cloak]{display:none!important}</style>

        <div x-data="{ open: false, tab: 'status' }" x-cloak>
            {{-- Tombol mengambang (FAB) untuk membuka panel meja.
                 check-ui-allow: FAB benar-benar melayang di atas halaman. --}}
            <x-button class="fixed bottom-5 right-5 z-40 h-14 gap-2 rounded-full pl-4 pr-5 shadow-lg"
                variant="primary" x-on:click="open = true" label="Buka panel meja"
                icon="ri-apps-2-line text-2xl">
                <span class="hidden font-semibold sm:inline">Panel meja</span>
            </x-button>

            {{-- Overlay --}}
            <div x-show="open" x-transition.opacity @click="open = false"
                {{-- check-ui-allow: scrim gelap di belakang bottom sheet, bukan permukaan tema. --}}
                class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"></div>

            {{-- Drawer bawah (bottom sheet) --}}
            <div x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
                @keydown.escape.window="open = false"
                class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full max-w-[1560px]">
                {{-- check-ui-allow: bottom sheet melayang di atas halaman. --}}
                <div class="flex max-h-[85vh] flex-col rounded-t-xl border border-base-300 bg-base-100 shadow-2xl">
                    {{-- Handle + header --}}
                    <div class="relative shrink-0 px-4 pt-3">
                        <div class="mx-auto mb-2 h-1.5 w-12 rounded-full bg-base-300"></div>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-base font-bold">
                                    <i class="ri-sparkling-2-line text-primary" aria-hidden="true"></i> Panel meja
                                    <x-badge color="primary" size="sm" class="align-middle">Meja {{ $tableCode }}</x-badge>
                                </h2>
                                <p class="mt-1 text-sm text-base-content/70">
                                    Kode gabung untuk teman satu meja:
                                    <span class="font-semibold tabular-nums tracking-widest text-base-content">{{ $tableSession->join_code }}</span>
                                </p>
                            </div>
                            <x-button variant="ghost" size="sm" shape="circle" icon="ri-close-line text-lg"
                                label="Tutup panel" x-on:click="open = false" />
                        </div>
                    </div>

                    {{-- Bilah tab ber-icon --}}
                    <div role="tablist"
                        class="mt-3 flex shrink-0 gap-1 overflow-x-auto border-b border-base-300 px-2">
                        @php
                            $tabs = [
                                ['key' => 'status', 'icon' => 'ri-timer-flash-line', 'label' => 'Status'],
                                ['key' => 'chat', 'icon' => 'ri-chat-3-line', 'label' => 'Chat'],
                                ['key' => 'lagu', 'icon' => 'ri-music-2-line', 'label' => 'Lagu'],
                                ['key' => 'permintaan', 'icon' => 'ri-service-line', 'label' => 'Permintaan'],
                            ];
                        @endphp
                        @foreach ($tabs as $t)
                            <button type="button" role="tab" @click="tab = '{{ $t['key'] }}'"
                                :class="tab === '{{ $t['key'] }}'
                                    ? 'border-primary text-primary'
                                    : 'border-transparent text-base-content/60 hover:text-base-content'"
                                class="flex shrink-0 items-center gap-1.5 border-b-2 px-3 py-2.5 text-sm font-semibold transition-colors">
                                <i class="{{ $t['icon'] }} text-lg"></i>
                                <span>{{ $t['label'] }}</span>
                            </button>
                        @endforeach
                        {{-- Tab "Menu" menutup panel dan kembali ke katalog --}}
                        <button type="button" role="tab" @click="open = false"
                            class="ml-auto flex shrink-0 items-center gap-1.5 border-b-2 border-transparent px-3 py-2.5 text-sm font-semibold text-base-content/60 transition-colors hover:text-base-content">
                            <i class="ri-restaurant-2-line text-lg"></i>
                            <span>Menu</span>
                        </button>
                    </div>

                    {{-- Isi panel --}}
                    <div class="min-h-0 flex-1 overflow-y-auto p-4">
                        <div x-show="tab === 'status'">
                            <livewire:frontend.order-status />
                        </div>
                        <div x-show="tab === 'chat'">
                            <livewire:frontend.table-chat />
                        </div>
                        <div x-show="tab === 'lagu'">
                            <livewire:frontend.song-request />
                        </div>
                        <div x-show="tab === 'permintaan'">
                            <livewire:frontend.special-request-form />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-guest-layout>
