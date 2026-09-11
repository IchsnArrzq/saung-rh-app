{{--
    Obrolan meja tamu. Pesan baru datang lewat siaran ChatMessagePosted (kanal
    publik chat.table.{id}); polling 30 dtk hanya cadangan saat Reverb tidak
    tersambung. Pesan staf (`staff`) tampil sebagai suara restoran.

    Daftar pesan tidak punya tinggi sendiri: panel meja yang menggulir
    ([data-panel-scroll]), dan kolom kirim menempel di bawahnya.
--}}
<div wire:key="table-chat" wire:poll.30s.visible class="space-y-3">
    @if (! $tableId || ! $sessionOpen)
        <x-alert type="info" icon="ri-qr-scan-2-line">Pindai QR di meja Anda untuk membuka obrolan meja.</x-alert>
    @elseif (! $available)
        <x-alert type="warning" icon="ri-chat-off-line">
            Obrolan meja sedang tidak tersedia. Anda tetap bisa memesan menu seperti biasa.
        </x-alert>
    @elseif ($activeType === null)
        {{-- ================= Daftar percakapan ================= --}}
        <form wire:submit="saveName" class="flex items-center gap-2">
            <x-input bare name="senderName" label="Nama tampilan" icon="ri-user-smile-line" maxlength="24"
                placeholder="Nama tampilan (opsional)" wire:model="senderName" class="min-h-11 grow" />
            <x-button type="submit" variant="ghost" shape="square" icon="ri-check-line" label="Simpan nama tampilan"
                loading="saveName" class="min-h-11 min-w-11" />
        </form>

        <div wire:loading.block wire:target="openRoom, openDm">
            <x-skeleton :rows="3" height="h-14" />
        </div>

        <div wire:loading.remove wire:target="openRoom, openDm" class="space-y-2">
            <button type="button" wire:click="openRoom"
                class="flex min-h-14 w-full items-center gap-3 rounded-xl bg-base-200 p-3 text-left transition-colors hover:bg-base-300">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/15 text-primary"
                    aria-hidden="true">
                    <i class="ri-group-line text-lg"></i>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="flex items-center justify-between gap-2">
                        <span class="truncate text-sm font-semibold">Meja saya ({{ $tableCode }})</span>
                        @if ($roomPreview)
                            <time class="shrink-0 text-xs tabular-nums text-base-content/50">
                                {{ \Illuminate\Support\Carbon::parse($roomPreview['at'])->format('H:i') }}
                            </time>
                        @endif
                    </span>
                    <span class="block truncate text-sm text-base-content/60">
                        @if ($roomPreview && ! empty($roomPreview['staff']))
                            {{ $roomPreview['sender_name'] ?? 'Staf' }}:
                        @endif
                        {{ $roomPreview['body'] ?? 'Obrolan teman semeja dan staf restoran' }}
                    </span>
                </span>
            </button>

            <h3 class="px-1 pt-2 text-sm font-semibold text-base-content/70">Meja lain yang terisi</h3>

            @forelse ($conversations as $c)
                <button type="button" wire:click="openDm('{{ $c['id'] }}')" wire:key="conversation-{{ $c['id'] }}"
                    class="flex min-h-14 w-full items-center gap-3 rounded-xl bg-base-200 p-3 text-left transition-colors hover:bg-base-300">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-base-100 text-sm font-bold text-base-content/70"
                        aria-hidden="true">
                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($c['code'], 0, 2)) }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center justify-between gap-2">
                            <span class="truncate text-sm font-semibold">Meja {{ $c['code'] }}</span>
                            @if ($c['last'])
                                <time class="shrink-0 text-xs tabular-nums text-base-content/50">
                                    {{ \Illuminate\Support\Carbon::parse($c['last']['at'])->format('H:i') }}
                                </time>
                            @endif
                        </span>
                        <span class="block truncate text-sm text-base-content/60">
                            @if ($c['last'])
                                {{ ($c['last']['table_id'] ?? null) === $tableId ? 'Anda: ' : '' }}{{ $c['last']['body'] }}
                            @else
                                Ketuk untuk mulai mengobrol
                            @endif
                        </span>
                    </span>
                </button>
            @empty
                <x-empty-state icon="ri-cup-line" title="Belum ada meja lain yang terisi"
                    description="Obrolan antar-meja muncul di sini begitu ada tamu lain yang check-in." />
            @endforelse
        </div>
    @else
        {{-- ================= Tampilan percakapan ================= --}}
        <div class="flex items-center gap-2">
            <x-button variant="ghost" shape="circle" icon="ri-arrow-left-line text-lg" label="Kembali ke daftar obrolan"
                wire:click="backToList" loading="backToList" class="min-h-11 min-w-11" />
            <span @class([
                'flex h-9 w-9 shrink-0 items-center justify-center rounded-full',
                'bg-primary/15 text-primary' => $activeType === 'room',
                'bg-base-200 text-base-content/70' => $activeType !== 'room',
            ]) aria-hidden="true">
                <i class="{{ $activeType === 'room' ? 'ri-group-line' : 'ri-chat-1-line' }}"></i>
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold">{{ $activeHeader }}</p>
                <p class="text-xs text-base-content/60">
                    {{ $activeType === 'room' ? 'Teman semeja dan staf restoran' : 'Obrolan privat antar-meja' }}
                </p>
            </div>
        </div>

        <ol class="space-y-2 rounded-xl bg-base-200 p-3" aria-label="Pesan"
            x-data
            x-init="
                const scroller = $el.closest('[data-panel-scroll]');
                const stick = () => { if (scroller) scroller.scrollTop = scroller.scrollHeight };
                $nextTick(stick);
                new MutationObserver(stick).observe($el, { childList: true, subtree: true });
            ">
            @forelse ($messages as $m)
                @php
                    $fromStaff = (bool) ($m['staff'] ?? false);
                    $mine = ! $fromStaff && ($activeType === 'room'
                        ? ($m['sender_id'] ?? null) === $senderId
                        : ($m['table_id'] ?? null) === $tableId);
                    $who = match (true) {
                        $fromStaff => $m['sender_name'] ?? 'Staf',
                        $activeType === 'room' => $m['sender_name'] ?? ($mine ? 'Anda' : 'Tamu'),
                        default => ($mine ? 'Anda' : 'Meja '.($m['table_code'] ?? '?'))
                            .(! empty($m['sender_name']) ? ' · '.$m['sender_name'] : ''),
                    };
                    $sentAt = isset($m['at']) ? \Illuminate\Support\Carbon::parse($m['at'])->format('H:i') : '';
                @endphp
                <li wire:key="chat-message-{{ $m['id'] ?? $loop->index }}" class="chat {{ $mine ? 'chat-end' : 'chat-start' }}">
                    <div class="chat-header text-xs text-base-content/60">
                        {{ $who }}
                        <time class="tabular-nums">{{ $sentAt }}</time>
                    </div>
                    <div @class([
                        'chat-bubble text-sm',
                        'chat-bubble-primary' => $mine,
                        'chat-bubble-info' => $fromStaff,
                    ])>{{ $m['body'] ?? '' }}</div>
                </li>
            @empty
                <li class="py-6 text-center text-sm text-base-content/60">
                    {{ $activeType === 'room' ? 'Belum ada obrolan. Mulai sapa teman semeja Anda.' : 'Belum ada obrolan. Sapa meja ini lebih dulu.' }}
                </li>
            @endforelse
        </ol>

        {{-- Menempel di dasar panel meja selama pesannya digulir. --}}
        <form wire:submit="send" class="sticky -bottom-4 -mx-4 flex items-start gap-2 bg-base-100 px-4 pb-4 pt-3">
            <div class="grow">
                <x-input bare name="body" label="Tulis pesan" maxlength="280" placeholder="Tulis pesan..."
                    wire:model="body" class="min-h-11 w-full" />
                @error('body')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>
            <x-button type="submit" variant="primary" shape="square" icon="ri-send-plane-2-line" label="Kirim pesan"
                loading="send" class="min-h-11 min-w-11" />
        </form>
    @endif
</div>
