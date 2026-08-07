<div class="flex flex-col h-full" wire:key="table-chat">
    @if (! $tableId)
        <div class="alert alert-info text-sm">
            <i class="ri-qr-scan-2-line"></i>
            <span>Scan QR meja Anda untuk membuka obrolan meja.</span>
        </div>
    @elseif (! $available)
        <div class="alert alert-warning text-sm">
            <i class="ri-chat-off-line"></i>
            <span>Obrolan meja sedang tidak tersedia. Anda tetap dapat memesan menu seperti biasa.</span>
        </div>
    @elseif ($activeType === null)
        {{-- ================= Daftar percakapan (WhatsApp-style) ================= --}}
        <div class="mb-2 flex items-center justify-between">
            <span class="text-sm font-semibold"><i class="ri-chat-3-line text-primary"></i> Obrolan</span>
            <span class="badge badge-primary badge-sm">Meja {{ $tableCode }}</span>
        </div>

        {{-- Nama tampilan (opsional), berlaku untuk semua percakapan --}}
        <form wire:submit="saveName" class="mb-3 flex items-center gap-2">
            <label class="input input-bordered input-xs flex grow items-center gap-2">
                <i class="ri-user-smile-line text-base-content/50"></i>
                <input type="text" wire:model="senderName" maxlength="24" placeholder="Nama tampilan (opsional)"
                    class="grow bg-transparent">
            </label>
            <x-button type="submit" variant="ghost" size="xs" shape="square" icon="ri-check-line" label="Simpan nama" />
        </form>

        <div class="flex-1 min-h-40 max-h-80 space-y-1 overflow-y-auto">
            {{-- Room meja sendiri --}}
            <button type="button" wire:click="openRoom"
                class="flex w-full items-center gap-3 rounded-xl border border-base-200 bg-base-100 p-2.5 text-left transition-colors hover:bg-base-200">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/15 text-primary">
                    <i class="ri-group-line text-lg"></i>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="flex items-center justify-between gap-2">
                        <span class="truncate text-sm font-semibold">Meja Saya ({{ $tableCode }})</span>
                        @if ($roomPreview)
                            <time class="shrink-0 text-[11px] opacity-50">{{ \Illuminate\Support\Carbon::parse($roomPreview['at'])->format('H:i') }}</time>
                        @endif
                    </span>
                    <span class="block truncate text-xs text-base-content/60">
                        {{ $roomPreview['body'] ?? 'Obrolan teman semeja Anda' }}
                    </span>
                </span>
            </button>

            <div class="px-1 pb-1 pt-3 text-[11px] font-semibold uppercase tracking-wider text-base-content/40">
                Meja lain yang terisi
            </div>

            @forelse ($conversations as $c)
                <button type="button" wire:click="openDm('{{ $c['id'] }}')" wire:key="conv-{{ $c['id'] }}"
                    class="flex w-full items-center gap-3 rounded-xl border border-base-200 bg-base-100 p-2.5 text-left transition-colors hover:bg-base-200">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-base-200 font-bold text-base-content/70">
                        {{ Str::upper(Str::substr($c['code'], 0, 2)) }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center justify-between gap-2">
                            <span class="truncate text-sm font-semibold">Meja {{ $c['code'] }}</span>
                            @if ($c['last'])
                                <time class="shrink-0 text-[11px] opacity-50">{{ \Illuminate\Support\Carbon::parse($c['last']['at'])->format('H:i') }}</time>
                            @endif
                        </span>
                        <span class="block truncate text-xs text-base-content/60">
                            @if ($c['last'])
                                {{ ($c['last']['table_id'] ?? null) === $tableId ? 'Anda: ' : '' }}{{ $c['last']['body'] }}
                            @else
                                Ketuk untuk mulai mengobrol
                            @endif
                        </span>
                    </span>
                </button>
            @empty
                <div class="rounded-xl border border-dashed border-base-300 bg-base-200/40 p-6 text-center">
                    <i class="ri-cup-line mb-2 text-3xl text-base-content/30"></i>
                    <p class="text-sm text-base-content/60">Belum ada meja lain yang terisi saat ini.</p>
                </div>
            @endforelse
        </div>
    @else
        {{-- ================= Tampilan percakapan ================= --}}
        <div class="mb-2 flex items-center gap-2">
            <x-button variant="ghost" size="xs" shape="circle" icon="ri-arrow-left-line text-lg" label="Kembali"
                wire:click="backToList" />
            <span class="flex h-9 w-9 items-center justify-center rounded-full {{ $activeType === 'room' ? 'bg-primary/15 text-primary' : 'bg-base-200 text-base-content/70' }}">
                <i class="{{ $activeType === 'room' ? 'ri-group-line' : 'ri-chat-1-line' }}"></i>
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold">{{ $activeHeader }}</p>
                <p class="text-[11px] text-base-content/50">
                    {{ $activeType === 'room' ? 'Obrolan teman semeja' : 'Obrolan privat antar-meja' }}
                </p>
            </div>
        </div>

        <div class="flex-1 min-h-40 max-h-72 overflow-y-auto space-y-2 rounded-lg bg-base-200/60 p-3" id="chat-scroll">
            @forelse ($messages as $m)
                @php
                    $mine = $activeType === 'room'
                        ? ($m['sender_id'] ?? null) === $senderId
                        : ($m['table_id'] ?? null) === $tableId;
                    $who = $activeType === 'room'
                        ? ($m['sender_name'] ?? ($mine ? 'Anda' : 'Tamu'))
                        : (($mine ? 'Anda' : 'Meja '.($m['table_code'] ?? '?')).($m['sender_name'] ? ' · '.$m['sender_name'] : ''));
                @endphp
                <div class="chat {{ $mine ? 'chat-end' : 'chat-start' }}">
                    <div class="chat-header text-xs opacity-70">
                        {{ $who }}
                        <time class="opacity-50">{{ \Illuminate\Support\Carbon::parse($m['at'])->format('H:i') }}</time>
                    </div>
                    <div class="chat-bubble {{ $mine ? 'chat-bubble-primary' : '' }} text-sm">{{ $m['body'] }}</div>
                </div>
            @empty
                <p class="text-center text-xs text-secondary py-6">
                    {{ $activeType === 'room' ? 'Belum ada obrolan. Mulai percakapan dengan teman semeja!' : 'Belum ada obrolan. Sapa meja ini!' }}
                </p>
            @endforelse
        </div>

        <form wire:submit="send" class="mt-3 flex items-center gap-2">
            <input type="text" wire:model="body" maxlength="280"
                placeholder="Tulis pesan..." class="input input-bordered input-sm grow">
            <x-button type="submit" variant="primary" size="sm" shape="square" icon="ri-send-plane-2-line" label="Kirim pesan" />
        </form>
        @error('body') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror

        <div wire:ignore.self x-data x-init="$nextTick(() => { const el = document.getElementById('chat-scroll'); if (el) el.scrollTop = el.scrollHeight; })"></div>
    @endif
</div>
