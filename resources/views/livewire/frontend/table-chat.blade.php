<div class="flex flex-col h-full" wire:key="table-chat">
    @if (! $tableId)
        <div class="alert alert-info text-sm">
            <i class="ri-qr-scan-2-line"></i>
            <span>Scan QR meja Anda untuk ikut mengobrol antar-meja.</span>
        </div>
    @elseif (! $available)
        <div class="alert alert-warning text-sm">
            <i class="ri-chat-off-line"></i>
            <span>Obrolan antar-meja sedang tidak tersedia. Anda tetap dapat memesan menu seperti biasa.</span>
        </div>
    @else
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold"><i class="ri-chat-3-line text-primary"></i> Obrolan Antar-Meja</span>
            <span class="badge badge-primary badge-sm">Meja {{ $tableCode }}</span>
        </div>

        @if (session('chat_status'))
            <div class="alert alert-success py-1.5 text-xs mb-2"><span>{{ session('chat_status') }}</span></div>
        @endif

        @if ($blocked)
            <div class="alert alert-error py-1.5 text-xs mb-2">
                <i class="ri-forbid-line"></i>
                <span>Meja Anda diblokir dari chat karena laporan berulang.</span>
            </div>
        @endif

        <div class="flex-1 min-h-40 max-h-72 overflow-y-auto space-y-2 rounded-lg bg-base-200/60 p-3" id="chat-scroll">
            @forelse ($messages as $m)
                @php $mine = ($m['table_id'] ?? null) === $tableId; @endphp
                <div class="chat {{ $mine ? 'chat-end' : 'chat-start' }}">
                    <div class="chat-header text-xs opacity-70">
                        Meja {{ $m['table_code'] ?? '?' }}
                        <time class="opacity-50">{{ \Illuminate\Support\Carbon::parse($m['at'])->format('H:i') }}</time>
                    </div>
                    <div class="chat-bubble {{ $mine ? 'chat-bubble-primary' : '' }} text-sm">{{ $m['body'] }}</div>
                    @unless ($mine)
                        <div class="chat-footer">
                            <button wire:click="report('{{ $m['id'] }}')" class="text-[11px] text-error/80 hover:underline">
                                <i class="ri-flag-line"></i> Lapor
                            </button>
                        </div>
                    @endunless
                </div>
            @empty
                <p class="text-center text-xs text-secondary py-6">Belum ada obrolan. Sapa meja lain!</p>
            @endforelse
        </div>

        <form wire:submit="send" class="mt-3 flex items-center gap-2">
            <input type="text" wire:model="body" @disabled($blocked) maxlength="280"
                placeholder="Tulis pesan..." class="input input-bordered input-sm grow">
            <button type="submit" class="btn btn-primary btn-sm" @disabled($blocked)>
                <i class="ri-send-plane-2-line"></i>
            </button>
        </form>
        @error('body') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror

        <div wire:ignore.self x-data x-init="$nextTick(() => { const el = document.getElementById('chat-scroll'); if (el) el.scrollTop = el.scrollHeight; })"></div>
    @endif
</div>
