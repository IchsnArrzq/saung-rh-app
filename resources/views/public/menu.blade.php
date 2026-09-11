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
        @php
            $tableCode = $tableSession->table->code;
            $panelTabs = [
                ['key' => 'status', 'icon' => 'ri-timer-flash-line', 'label' => 'Status'],
                ['key' => 'chat', 'icon' => 'ri-chat-3-line', 'label' => 'Obrolan'],
                ['key' => 'lagu', 'icon' => 'ri-music-2-line', 'label' => 'Lagu'],
                ['key' => 'permintaan', 'icon' => 'ri-service-line', 'label' => 'Permintaan'],
            ];
        @endphp

        <style>[x-cloak]{display:none!important}</style>

        {{--
            Panel meja tamu (bottom sheet).
            - Layar penuh lewat tombol di kepala panel, atau geser pegangan: ke atas =
              penuh, ke bawah = kecilkan lalu tutup.
            - Selama panel terbuka halaman menu di belakangnya dikunci (overflow-hidden
              pada <html>) dan isi panel memakai overscroll-contain, jadi menggulir
              panel tidak lagi ikut menggulir menu.
            - Titik pada tab dan FAB: komponen di dalam panel mengirim
              `table-panel-activity` saat siaran untuk meja ini tiba.
        --}}
        <div x-data="{
                open: false,
                full: false,
                tab: 'status',
                dots: { chat: false, lagu: false, permintaan: false },
                dragFrom: null,
                dragged: false,
                show(tab) {
                    this.tab = tab;
                    this.dots[tab] = false;
                    this.$nextTick(() => {
                        const scroller = this.$root.querySelector('[data-panel-scroll]');
                        if (scroller) scroller.scrollTop = tab === 'chat' ? scroller.scrollHeight : 0;
                    });
                },
                openPanel() {
                    this.open = true;
                    this.show(this.tab);
                },
                dragStart(event) {
                    this.dragFrom = event.clientY;
                    this.dragged = false;
                    event.target.setPointerCapture?.(event.pointerId);
                },
                dragEnd(event) {
                    if (this.dragFrom === null) return;
                    const distance = event.clientY - this.dragFrom;
                    this.dragFrom = null;
                    if (Math.abs(distance) < 12) return;
                    this.dragged = true;
                    if (distance < 0) {
                        this.full = true;
                    } else if (this.full) {
                        this.full = false;
                    } else {
                        this.open = false;
                    }
                },
                tapHandle() {
                    if (this.dragged) {
                        this.dragged = false;
                        return;
                    }
                    this.full = ! this.full;
                },
                get hasNews() {
                    return Object.values(this.dots).some(Boolean);
                },
            }"
            x-effect="document.documentElement.classList.toggle('overflow-hidden', open)"
            x-on:table-panel-activity.window="if (! open || tab !== $event.detail.tab) dots[$event.detail.tab] = true"
            x-cloak>

            {{-- check-ui-allow: FAB benar-benar melayang di atas halaman. --}}
            <x-button class="fixed bottom-5 right-5 z-40 h-14 gap-2 rounded-full pl-4 pr-5 shadow-lg"
                variant="primary" x-on:click="openPanel()" label="Buka panel meja" icon="ri-apps-2-line text-2xl">
                <span class="hidden font-semibold sm:inline">Panel meja</span>
                <span x-show="hasNews" class="status status-warning status-lg absolute right-1 top-1" aria-hidden="true"></span>
                <span x-show="hasNews" class="sr-only">Ada kabar baru di panel meja</span>
            </x-button>

            <div x-show="open" x-transition.opacity.duration.200ms x-on:click="open = false"
                class="fixed inset-0 z-40 bg-base-content/40" aria-hidden="true"></div>

            <div x-show="open" role="dialog" aria-modal="true" aria-label="Panel meja"
                x-transition:enter="transition ease-out duration-300 motion-reduce:transition-none"
                x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                x-transition:leave="transition ease-in duration-200 motion-reduce:transition-none"
                x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
                x-on:keydown.escape.window="open = false"
                class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full max-w-[1560px]">
                {{-- check-ui-allow: bottom sheet melayang di atas halaman. --}}
                <div class="flex flex-col bg-base-100 shadow-2xl"
                    :class="full ? 'h-dvh rounded-none' : 'max-h-[85dvh] rounded-t-xl'">
                    <div class="shrink-0 px-4 pt-1">
                        {{-- Pegangan: ketuk untuk layar penuh/kecil, atau geser ke atas/bawah. --}}
                        <button type="button" class="-mb-2 flex h-11 w-full touch-none items-center justify-center"
                            x-on:pointerdown="dragStart($event)" x-on:pointerup="dragEnd($event)"
                            x-on:pointercancel="dragFrom = null" x-on:click="tapHandle()"
                            :aria-label="full ? 'Kecilkan panel' : 'Perbesar panel ke layar penuh'">
                            <span class="h-1.5 w-12 rounded-full bg-base-300" aria-hidden="true"></span>
                        </button>

                        <div class="flex items-center justify-between gap-2">
                            <h2 class="flex items-center gap-2 text-base font-semibold">
                                <i class="ri-sparkling-2-line text-primary" aria-hidden="true"></i>
                                Panel meja
                                @if ($tableCode)
                                    <x-badge color="primary" size="sm">Meja {{ $tableCode }}</x-badge>
                                @endif
                            </h2>

                            <div class="flex items-center gap-1">
                                <x-button variant="ghost" shape="circle" label="Ubah ukuran panel"
                                    x-on:click="full = ! full" class="min-h-11 min-w-11">
                                    <i class="text-lg" :class="full ? 'ri-fullscreen-exit-line' : 'ri-fullscreen-line'"
                                        aria-hidden="true"></i>
                                </x-button>
                                <x-button variant="ghost" shape="circle" icon="ri-close-line text-xl" label="Tutup panel"
                                    x-on:click="open = false" class="min-h-11 min-w-11" />
                            </div>
                        </div>

                        @if ($tableSession->join_code)
                            <p class="mt-0.5 text-sm text-base-content/70">
                                Kode gabung untuk teman satu meja:
                                <span class="font-semibold tabular-nums tracking-widest text-base-content">{{ $tableSession->join_code }}</span>
                            </p>
                        @endif
                    </div>

                    <div role="tablist" aria-label="Isi panel meja" class="mt-1 flex shrink-0 gap-1 overflow-x-auto px-2">
                        @foreach ($panelTabs as $panelTab)
                            <button type="button" role="tab" x-on:click="show('{{ $panelTab['key'] }}')"
                                :aria-selected="tab === '{{ $panelTab['key'] }}'"
                                :class="tab === '{{ $panelTab['key'] }}'
                                    ? 'border-primary text-primary'
                                    : 'border-transparent text-base-content/60 hover:text-base-content'"
                                class="flex min-h-11 shrink-0 items-center gap-1.5 border-b-2 px-3 text-sm font-semibold transition-colors">
                                <i class="{{ $panelTab['icon'] }} text-lg" aria-hidden="true"></i>
                                <span>{{ $panelTab['label'] }}</span>
                                @if ($panelTab['key'] !== 'status')
                                    <span x-show="dots['{{ $panelTab['key'] }}']" class="status status-warning" aria-hidden="true"></span>
                                @endif
                            </button>
                        @endforeach

                        {{-- "Menu" menutup panel dan kembali ke katalog. --}}
                        <button type="button" x-on:click="open = false"
                            class="ml-auto flex min-h-11 shrink-0 items-center gap-1.5 border-b-2 border-transparent px-3 text-sm font-semibold text-base-content/60 transition-colors hover:text-base-content">
                            <i class="ri-restaurant-2-line text-lg" aria-hidden="true"></i>
                            <span>Menu</span>
                        </button>
                    </div>

                    <div data-panel-scroll class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-4">
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
