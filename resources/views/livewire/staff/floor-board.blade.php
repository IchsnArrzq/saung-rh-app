{{--
    Panel meja staf (App\Livewire\Staff\FloorBoard).

    Kartu per meja; cincin kuning = ada permintaan terbuka atau pesan tamu yang
    belum dibaca. Perubahan datang lewat siaran FloorActivity di kanal private
    `floor`; polling 20 detik hanya cadangan saat Reverb tidak tersambung, dan
    `.visible` membuatnya diam saat tab browser tidak terlihat.
--}}
<div wire:poll.20s.visible class="space-y-4"
    x-data="{
        toasts: [],
        push(detail) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, ...detail });
            if (detail.tableId && navigator.vibrate) navigator.vibrate(150);
            setTimeout(() => this.dismiss(id), 6000);
        },
        dismiss(id) {
            this.toasts = this.toasts.filter((toast) => toast.id !== id);
        },
    }"
    x-on:floor-toast.window="push($event.detail)">

    {{-- Bilah kontrol: cari, saring, dan status sambungan langsung. --}}
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <x-search-input wire:model.live.debounce.300ms="search" placeholder="Cari kode atau nama meja"
            label="Cari meja" class="lg:max-w-xs" />

        <div role="radiogroup" aria-label="Saring meja" class="grid grid-cols-3 gap-1 rounded-lg bg-base-200 p-1">
            @foreach (['all' => 'Semua', 'attention' => 'Perlu tindakan', 'occupied' => 'Terisi'] as $key => $label)
                <button type="button" role="radio" aria-checked="{{ $filter === $key ? 'true' : 'false' }}"
                    wire:click="$set('filter', '{{ $key }}')" wire:loading.attr="disabled" wire:target="filter"
                    @class([
                        'flex min-h-11 items-center justify-center gap-1.5 rounded-md px-3 text-sm font-semibold transition-colors',
                        'bg-base-100 text-base-content' => $filter === $key,
                        'text-base-content/60 hover:text-base-content' => $filter !== $key,
                    ])>
                    <span>{{ $label }}</span>
                    <span @class([
                        'tabular-nums',
                        'text-warning' => $key === 'attention' && $counts[$key] > 0,
                        'text-base-content/60' => ! ($key === 'attention' && $counts[$key] > 0),
                    ])>{{ $counts[$key] }}</span>
                </button>
            @endforeach
        </div>

        {{-- wire:ignore: teksnya milik Alpine; morph Livewire akan mengembalikannya ke teks awal. --}}
        <p wire:ignore class="inline-flex items-center gap-2 text-sm text-base-content/70 lg:ml-auto"
            x-data="{ live: false }"
            x-init="
                const connection = window.Echo?.connector?.pusher?.connection;
                if (connection) {
                    live = connection.state === 'connected';
                    connection.bind('state_change', (states) => live = states.current === 'connected');
                }
            ">
            <span class="status" :class="live ? 'status-success' : 'status-warning'" aria-hidden="true"></span>
            <span x-text="live ? 'Tersambung langsung' : 'Diperbarui tiap 20 detik'">Diperbarui tiap 20 detik</span>
        </p>
    </div>

    {{-- Loading: kerangka berbentuk kartu meja selama pencarian/saringan diproses. --}}
    <div wire:loading.grid wire:target="search, filter, resetFilters"
        class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-6" aria-hidden="true">
        @for ($i = 0; $i < 8; $i++)
            <div class="skeleton h-36 rounded-xl"></div>
        @endfor
    </div>

    <div wire:loading.remove wire:target="search, filter, resetFilters">
        @if ($totalTables === 0)
            <x-empty-state icon="ri-layout-grid-line" title="Belum ada meja"
                description="Meja yang didaftarkan di menu Meja muncul di sini sebagai kartu.">
                @can('create', \App\Models\Table::class)
                    <x-slot:actions>
                        <x-button :href="route('tables.create')" wire:navigate icon="ri-add-line">Tambah meja</x-button>
                    </x-slot:actions>
                @endcan
            </x-empty-state>
        @elseif ($tiles->isEmpty())
            <x-empty-state icon="ri-filter-off-line" title="Tidak ada meja yang cocok"
                description="Tidak ada meja yang sesuai dengan pencarian atau saringan ini.">
                <x-slot:actions>
                    <x-button variant="outline" icon="ri-refresh-line" wire:click="resetFilters" loading="resetFilters">
                        Tampilkan semua meja
                    </x-button>
                </x-slot:actions>
            </x-empty-state>
        @else
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-6">
                @foreach ($tiles as $tile)
                    @php
                        $table = $tile['table'];
                        $session = $tile['session'];
                        $tableStatus = \App\Domains\Table\Enums\TableStatus::tryFrom((string) $table->status);
                        $isSelected = $selectedTableId === $table->id;
                    @endphp

                    <button type="button" wire:key="floor-tile-{{ $table->id }}"
                        wire:click="open('{{ $table->id }}', '{{ $tile['default_tab'] }}')"
                        wire:loading.attr="disabled" wire:target="open"
                        aria-label="Buka panel Meja {{ $table->code }}{{ $tile['attention'] ? ' — ada yang perlu ditangani' : '' }}"
                        @class([
                            'relative flex min-h-36 flex-col rounded-xl bg-base-100 p-3 text-left transition-colors hover:bg-base-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary',
                            'ring-2 ring-warning' => $tile['attention'] && ! $isSelected,
                            'ring-2 ring-primary' => $isSelected,
                        ])>
                        @if ($tile['attention'])
                            <span class="status status-warning status-lg absolute -right-1 -top-1" aria-hidden="true"></span>
                        @endif

                        <span class="flex items-start justify-between gap-2">
                            <span class="text-2xl font-bold leading-none tabular-nums">{{ $table->code }}</span>
                            @if ($tableStatus)
                                <x-status-badge :status="$tableStatus" size="sm" class="shrink-0" />
                            @endif
                        </span>

                        <span class="mt-1 truncate text-xs text-base-content/60">{{ $table->name }}</span>

                        <span class="mt-2 text-sm text-base-content/70">
                            @if ($session)
                                Sesi sejak <span class="tabular-nums">{{ $session->started_at?->format('H:i') ?? '—' }}</span>
                                @if ($session->pax)
                                    · <span class="tabular-nums">{{ $session->pax }}</span> tamu
                                @endif
                            @else
                                Belum ada sesi QR
                            @endif
                        </span>

                        <span class="mt-auto flex flex-wrap gap-1.5 pt-3">
                            @if ($tile['open_requests'] > 0)
                                <x-badge color="warning" size="sm" icon="ri-service-line" class="tabular-nums">
                                    {{ $tile['open_requests'] }} permintaan
                                </x-badge>
                            @endif
                            @if ($tile['active_songs'] > 0)
                                <x-badge color="ghost" size="sm" icon="ri-music-2-line" class="tabular-nums">
                                    {{ $tile['active_songs'] }} lagu
                                </x-badge>
                            @endif
                            @if ($tile['unread_chat'])
                                <x-badge color="info" size="sm" icon="ri-chat-3-line">Pesan baru</x-badge>
                            @endif
                        </span>
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ================= Panel satu meja ================= --}}
    @if ($detail)
        @php
            $dt = $detail['table'];
            $dtSession = $detail['session'];
            $dtStatus = \App\Domains\Table\Enums\TableStatus::tryFrom((string) $dt->status);
            $panelTabs = [
                'requests' => ['label' => 'Permintaan', 'icon' => 'ri-service-line', 'count' => $detail['open_requests']->count(), 'color' => 'warning'],
                'songs' => ['label' => 'Lagu', 'icon' => 'ri-music-2-line', 'count' => $detail['active_songs']->count(), 'color' => 'ghost'],
                'chat' => ['label' => 'Obrolan', 'icon' => 'ri-chat-3-line', 'count' => 0, 'color' => 'ghost'],
            ];
        @endphp

        <div class="fixed inset-0 z-50 flex items-end md:items-stretch md:justify-end"
            role="dialog" aria-modal="true" aria-labelledby="floor-panel-title"
            wire:key="floor-panel-{{ $dt->id }}"
            x-data="{
                init() {
                    document.documentElement.classList.add('overflow-hidden');
                    this.$nextTick(() => this.$refs.closePanel?.focus());
                },
                destroy() {
                    document.documentElement.classList.remove('overflow-hidden');
                },
            }"
            x-on:keydown.escape.window="$wire.close()">
            <div class="absolute inset-0 bg-base-content/40" wire:click="close" aria-hidden="true"></div>

            {{-- check-ui-allow: panel benar-benar melayang di atas halaman — salah satu tempat shadow sah. --}}
            <section class="relative flex max-h-[92dvh] w-full flex-col rounded-t-xl bg-base-100 shadow-xl md:h-full md:max-h-none md:w-lg md:rounded-none md:rounded-l-xl">
                <header class="flex items-start gap-3 p-4">
                    <div class="min-w-0 flex-1">
                        <h2 id="floor-panel-title" class="flex flex-wrap items-center gap-2 text-lg font-semibold">
                            <span>Meja <span class="tabular-nums">{{ $dt->code }}</span></span>
                            @if ($dtStatus)
                                <x-status-badge :status="$dtStatus" size="sm" />
                            @endif
                        </h2>
                        <p class="truncate text-sm text-base-content/60">
                            {{ $dt->name }}
                            @if ($dtSession)
                                · sesi sejak <span class="tabular-nums">{{ $dtSession->started_at?->format('H:i') ?? '—' }}</span>
                            @else
                                · belum ada sesi QR
                            @endif
                        </p>
                    </div>
                    <x-button variant="ghost" shape="circle" icon="ri-close-line text-xl" label="Tutup panel meja"
                        wire:click="close" x-ref="closePanel" class="min-h-11 min-w-11 shrink-0" />
                </header>

                <div role="tablist" aria-label="Isi panel meja" class="mx-4 grid grid-cols-3 gap-1 rounded-lg bg-base-200 p-1">
                    @foreach ($panelTabs as $key => $panelTab)
                        <button type="button" role="tab" aria-selected="{{ $tab === $key ? 'true' : 'false' }}"
                            wire:click="setTab('{{ $key }}')" wire:loading.attr="disabled" wire:target="setTab"
                            @class([
                                'flex min-h-11 items-center justify-center gap-1.5 rounded-md px-2 text-sm font-semibold transition-colors',
                                'bg-base-100 text-base-content' => $tab === $key,
                                'text-base-content/60 hover:text-base-content' => $tab !== $key,
                            ])>
                            <i class="{{ $panelTab['icon'] }} text-base" aria-hidden="true"></i>
                            <span>{{ $panelTab['label'] }}</span>
                            @if ($panelTab['count'] > 0)
                                <x-badge :color="$panelTab['color']" size="xs" class="tabular-nums">{{ $panelTab['count'] }}</x-badge>
                            @endif
                            @if ($key === 'chat' && $unreadChat && $tab !== 'chat')
                                <span class="status status-info" aria-hidden="true"></span>
                                <span class="sr-only">Ada pesan baru</span>
                            @endif
                        </button>
                    @endforeach
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-4"
                    x-data
                    x-init="
                        const stick = () => { if ($wire.tab === 'chat') $el.scrollTop = $el.scrollHeight };
                        stick();
                        new MutationObserver(stick).observe($el, { childList: true, subtree: true });
                    ">
                    <div wire:loading.block wire:target="setTab">
                        <x-skeleton :rows="4" height="h-16" />
                    </div>

                    <div wire:loading.remove wire:target="setTab" class="space-y-3">
                        @error('panel')
                            <x-alert type="error">{{ $message }}</x-alert>
                        @enderror

                        @if ($tab === 'requests')
                            @forelse ($detail['open_requests'] as $specialRequest)
                                @php
                                    $waited = (int) floor($specialRequest->created_at->diffInMinutes(now(), true));
                                    $isNoting = $notingRequestId === $specialRequest->id;
                                @endphp

                                <article wire:key="floor-request-{{ $specialRequest->id }}" class="space-y-3 rounded-xl bg-base-200 p-3">
                                    <header class="flex items-start justify-between gap-2">
                                        <p class="flex items-center gap-2 text-sm font-semibold">
                                            <i class="{{ $specialRequest->category?->iconClass() ?? \App\Models\SpecialRequestCategory::DEFAULT_ICON }} text-lg text-base-content/70"
                                                aria-hidden="true"></i>
                                            {{ $specialRequest->category?->name ?? 'Tanpa kategori' }}
                                        </p>
                                        <x-status-badge :status="$specialRequest->status" size="sm" class="shrink-0" />
                                    </header>

                                    <p class="text-base leading-snug">{{ $specialRequest->description }}</p>

                                    <p class="text-xs text-base-content/60">
                                        {{ $specialRequest->requested_by ?: 'Tamu' }} ·
                                        <span class="tabular-nums">{{ $specialRequest->created_at->format('H:i') }}</span> ·
                                        menunggu <span class="tabular-nums">{{ $waited }}</span> mnt
                                        @if ($specialRequest->assignee)
                                            · ditangani {{ $specialRequest->assignee->name }}
                                        @endif
                                    </p>

                                    @if ($specialRequest->staff_note && ! $isNoting)
                                        <p class="rounded-lg bg-base-100 px-3 py-2 text-sm">
                                            <span class="font-medium">Catatan:</span> {{ $specialRequest->staff_note }}
                                        </p>
                                    @endif

                                    @can('update', $specialRequest)
                                        @if ($isNoting)
                                            <form wire:submit="saveNote" class="space-y-2">
                                                <x-textarea label="Catatan untuk tamu" name="note" :rows="2" maxlength="280"
                                                    placeholder="Mis. kue diantar setelah hidangan utama."
                                                    hint="Tamu melihat catatan ini di panel mejanya." wire:model="note" />

                                                <div class="flex flex-wrap gap-2">
                                                    <x-button type="submit" variant="outline" icon="ri-save-line" loading="saveNote"
                                                        class="min-h-11">
                                                        Simpan catatan
                                                    </x-button>
                                                    <x-button variant="error" outline icon="ri-close-circle-line"
                                                        wire:click="rejectRequest" loading="rejectRequest" class="min-h-11"
                                                        data-confirm="Tolak permintaan ini? Catatan di atas dikirim ke tamu sebagai alasannya."
                                                        data-confirm-title="Tolak permintaan" data-confirm-yes="Ya, tolak"
                                                        data-confirm-variant="danger">
                                                        Tolak dengan catatan
                                                    </x-button>
                                                    <x-button variant="ghost" wire:click="cancelNote" loading="cancelNote" class="min-h-11">
                                                        Batal
                                                    </x-button>
                                                </div>
                                            </form>
                                        @else
                                            <div class="grid grid-cols-3 gap-2">
                                                <x-button variant="success" icon="ri-check-double-line"
                                                    wire:click="completeRequest('{{ $specialRequest->id }}')"
                                                    loading="completeRequest('{{ $specialRequest->id }}')"
                                                    class="min-h-11 {{ $specialRequest->status->isWaiting() ? '' : 'col-span-2' }}">
                                                    Selesai
                                                </x-button>
                                                @if ($specialRequest->status->isWaiting())
                                                    <x-button variant="outline" icon="ri-hand-heart-line"
                                                        wire:click="claimRequest('{{ $specialRequest->id }}')"
                                                        loading="claimRequest('{{ $specialRequest->id }}')" class="min-h-11">
                                                        Tangani
                                                    </x-button>
                                                @endif
                                                <x-button variant="ghost" icon="ri-sticky-note-add-line"
                                                    wire:click="startNote('{{ $specialRequest->id }}')"
                                                    loading="startNote('{{ $specialRequest->id }}')" class="min-h-11">
                                                    Catatan
                                                </x-button>
                                            </div>
                                        @endif
                                    @endcan
                                </article>
                            @empty
                                <x-empty-state icon="ri-checkbox-circle-line" title="Tidak ada permintaan terbuka"
                                    description="Permintaan baru dari tamu di meja ini muncul di sini begitu dikirim." />
                            @endforelse

                            @if ($detail['closed_requests']->isNotEmpty())
                                <section class="pt-2">
                                    <h3 class="mb-2 text-sm font-semibold text-base-content/70">Baru ditangani</h3>
                                    <ul class="divide-y divide-base-300 rounded-xl bg-base-200">
                                        @foreach ($detail['closed_requests'] as $closed)
                                            <li wire:key="floor-closed-{{ $closed->id }}" class="flex items-start justify-between gap-3 px-3 py-2">
                                                <div class="min-w-0 text-sm">
                                                    <p class="truncate">{{ $closed->description }}</p>
                                                    <p class="text-xs text-base-content/60">
                                                        {{ $closed->assignee?->name ?? 'Staf' }} ·
                                                        <span class="tabular-nums">{{ $closed->handled_at?->format('H:i') ?? '—' }}</span>
                                                    </p>
                                                    @if ($closed->staff_note)
                                                        <p class="mt-0.5 text-sm text-base-content/70">Catatan: {{ $closed->staff_note }}</p>
                                                    @endif
                                                </div>
                                                <x-status-badge :status="$closed->status" size="sm" class="shrink-0" />
                                            </li>
                                        @endforeach
                                    </ul>
                                </section>
                            @endif
                        @elseif ($tab === 'songs')
                            @forelse ($detail['active_songs'] as $song)
                                @php
                                    $isPlaying = $song->status === \App\Domains\Social\Enums\SongStatus::Playing;
                                @endphp

                                <article wire:key="floor-song-{{ $song->id }}" class="space-y-3 rounded-xl bg-base-200 p-3">
                                    <div class="flex items-start gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-base-100 text-lg {{ $isPlaying ? 'text-success' : 'text-base-content/60' }}"
                                            aria-hidden="true">
                                            <i class="{{ $isPlaying ? 'ri-volume-up-line' : 'ri-music-2-line' }}"></i>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate font-medium">{{ $song->title }}</p>
                                            <p class="truncate text-xs text-base-content/60">
                                                {{ $song->artist ?: 'Artis tidak disebut' }} ·
                                                {{ $song->requested_by ?: 'Tamu' }} ·
                                                <span class="tabular-nums">{{ $song->created_at?->format('H:i') }}</span>
                                            </p>
                                        </div>
                                        <x-status-badge :status="$song->status" size="sm" class="shrink-0" />
                                    </div>

                                    @can('update', $song)
                                        <div class="grid grid-cols-2 gap-2">
                                            <x-button :variant="$isPlaying ? 'outline' : 'success'"
                                                :icon="$isPlaying ? 'ri-stop-circle-line' : 'ri-play-circle-line'"
                                                wire:click="advanceSong('{{ $song->id }}')"
                                                loading="advanceSong('{{ $song->id }}')" class="min-h-11">
                                                {{ $isPlaying ? 'Selesai diputar' : 'Putar sekarang' }}
                                            </x-button>
                                            <x-button variant="ghost" icon="ri-close-line"
                                                wire:click="rejectSong('{{ $song->id }}')"
                                                loading="rejectSong('{{ $song->id }}')" class="min-h-11"
                                                data-confirm="Keluarkan lagu {{ $song->title }} dari antrean?"
                                                data-confirm-title="Tolak lagu" data-confirm-yes="Ya, keluarkan">
                                                Tolak
                                            </x-button>
                                        </div>
                                    @endcan
                                </article>
                            @empty
                                <x-empty-state icon="ri-music-2-line" title="Tidak ada lagu antre dari meja ini"
                                    description="Lagu yang diminta tamu lewat panel mejanya muncul di sini." />
                            @endforelse

                            @if ($detail['finished_songs']->isNotEmpty())
                                <section class="pt-2">
                                    <h3 class="mb-2 text-sm font-semibold text-base-content/70">Baru diputar atau ditolak</h3>
                                    <ul class="divide-y divide-base-300 rounded-xl bg-base-200">
                                        @foreach ($detail['finished_songs'] as $finished)
                                            <li wire:key="floor-finished-{{ $finished->id }}" class="flex items-center justify-between gap-3 px-3 py-2 text-sm">
                                                <span class="min-w-0 truncate">{{ $finished->title }}</span>
                                                <x-status-badge :status="$finished->status" size="sm" class="shrink-0" />
                                            </li>
                                        @endforeach
                                    </ul>
                                </section>
                            @endif

                            @can('viewAny', \App\Models\SongRequest::class)
                                <x-button variant="ghost" size="sm" icon="ri-play-list-2-line" :href="route('songs.queue')" wire:navigate>
                                    Buka antrean lagu semua meja
                                </x-button>
                            @endcan
                        @else
                            @if (! $detail['chat_available'])
                                <x-alert type="warning">
                                    Obrolan tidak bisa dimuat karena server obrolan (Redis) tidak terhubung.
                                    Permintaan dan lagu tetap berjalan seperti biasa.
                                </x-alert>
                            @elseif (count($detail['messages']) === 0)
                                <x-empty-state icon="ri-chat-3-line" title="Belum ada obrolan"
                                    description="Pesan tamu di meja ini muncul di sini. Anda juga bisa menyapa lebih dulu dari kolom di bawah." />
                            @else
                                <ol class="space-y-2" aria-label="Obrolan Meja {{ $dt->code }}">
                                    @foreach ($detail['messages'] as $chatMessage)
                                        @php
                                            $fromStaff = (bool) ($chatMessage['staff'] ?? false);
                                            $sentAt = isset($chatMessage['at']) ? \Illuminate\Support\Carbon::parse($chatMessage['at'])->format('H:i') : '';
                                        @endphp
                                        <li wire:key="floor-message-{{ $chatMessage['id'] ?? $loop->index }}"
                                            class="chat {{ $fromStaff ? 'chat-end' : 'chat-start' }}">
                                            <div class="chat-header text-xs text-base-content/60">
                                                {{ $chatMessage['sender_name'] ?? ($fromStaff ? 'Staf' : 'Tamu') }}
                                                <time class="tabular-nums">{{ $sentAt }}</time>
                                            </div>
                                            <div class="chat-bubble text-sm {{ $fromStaff ? 'chat-bubble-info' : '' }}">{{ $chatMessage['body'] ?? '' }}</div>
                                        </li>
                                    @endforeach
                                </ol>
                            @endif
                        @endif
                    </div>
                </div>

                @if ($tab === 'chat' && $detail['chat_available'])
                    @can('moderateChat', $dt)
                        <footer class="space-y-2 bg-base-200 p-3">
                            <form wire:submit="sendReply" class="flex items-start gap-2">
                                <div class="grow">
                                    <x-input bare name="reply" label="Balas tamu di meja ini" maxlength="280"
                                        placeholder="Tulis balasan untuk meja ini" wire:model="reply" class="w-full" />
                                    @error('reply')
                                        <p class="mt-1 text-xs text-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <x-button type="submit" variant="primary" shape="square" icon="ri-send-plane-2-line"
                                    label="Kirim balasan" loading="sendReply" />
                            </form>

                            @if (count($detail['messages']) > 0)
                                <x-button variant="error" outline size="sm" :block="true" icon="ri-delete-bin-line"
                                    wire:click="clearChat" loading="clearChat"
                                    data-confirm="Hapus semua pesan di obrolan Meja {{ $dt->code }}, termasuk obrolan antar-mejanya? Tamu tidak bisa membacanya lagi."
                                    data-confirm-title="Bersihkan obrolan" data-confirm-yes="Ya, bersihkan">
                                    Bersihkan obrolan
                                </x-button>
                            @endif
                        </footer>
                    @endcan
                @endif
            </section>
        </div>
    @endif

    {{-- Notifikasi. wire:ignore: morph Livewire tidak boleh menyentuh daftar milik Alpine. --}}
    <div wire:ignore class="toast toast-end toast-bottom z-60" aria-live="polite">
        <template x-for="toast in toasts" :key="toast.id">
            {{-- check-ui-allow: toast melayang di atas halaman — salah satu tempat shadow sah. --}}
            <div role="status" class="alert shadow-lg" x-transition.opacity.duration.200ms
                :class="toast.tone === 'attention' ? 'alert-warning' : (toast.tone === 'error' ? 'alert-error' : 'alert-success')">
                <i class="text-lg" :class="toast.tone === 'attention' ? 'ri-notification-3-line' : 'ri-checkbox-circle-line'"
                    aria-hidden="true"></i>
                <span x-text="toast.message"></span>
                <template x-if="toast.tableId">
                    <x-button size="sm" variant="neutral" x-on:click="$wire.open(toast.tableId, 'requests'); dismiss(toast.id)">
                        Buka
                    </x-button>
                </template>
            </div>
        </template>
    </div>
</div>
