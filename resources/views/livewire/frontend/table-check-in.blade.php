@php
    $description = match ($state) {
        'join' => 'Meja ini sudah dibuka tamu lain.',
        'waiting' => 'Permintaan Anda sedang dicek kasir.',
        'active' => 'Meja siap. Silakan pesan dari menu.',
        default => 'Isi nama untuk membuka meja ini.',
    };

    $ended = $session && ! $session->status->isLive() ? $session->status : null;
@endphp

<div class="mx-auto max-w-md space-y-6">
    <x-page-header :title="'Meja ' . $table->code" :description="$description" />

    @if ($ended === \App\Domains\Table\Enums\TableSessionStatus::Rejected)
        <x-alert type="error">
            Permintaan meja ini ditolak kasir. Kalau Anda memang duduk di meja ini, panggil pelayan lalu coba lagi.
        </x-alert>
    @elseif ($ended)
        <x-alert type="info">Sesi meja Anda sebelumnya sudah berakhir.</x-alert>
    @endif

    @if ($state === 'waiting')
        <div wire:poll.5s="checkStatus" class="space-y-3">
            <x-card>
                <div class="divide-y divide-base-300">
                    <div class="flex flex-col items-center gap-3 pb-5 text-center">
                        <x-spinner size="lg" class="text-primary" label="Menunggu konfirmasi kasir" />
                        <h2 class="text-lg font-semibold">Menunggu konfirmasi kasir</h2>
                        <p class="text-sm text-base-content/70">
                            Kasir atau resepsionis sedang memastikan Anda duduk di Meja {{ $table->code }}.
                            Halaman ini pindah ke menu sendiri setelah disetujui.
                        </p>
                    </div>

                    <div class="pt-5 text-center">
                        <p class="text-sm text-base-content/70">Kode gabung untuk teman satu meja</p>
                        <p class="mt-1 text-3xl font-semibold tabular-nums tracking-widest">{{ $session->join_code }}</p>
                    </div>
                </div>
            </x-card>

            <x-button variant="ghost" size="lg" :block="true" icon="ri-restaurant-2-line" :href="route('public.menu')">
                Lihat menu sambil menunggu
            </x-button>
        </div>
    @elseif ($state === 'active')
        <x-card>
            <div class="flex flex-col items-center gap-3 py-2 text-center">
                <i class="ri-checkbox-circle-line text-4xl text-accent" aria-hidden="true"></i>
                <h2 class="text-lg font-semibold">Meja siap</h2>
                <p class="text-sm text-base-content/70">Sesi Meja {{ $table->code }} sudah dikonfirmasi kasir.</p>
                <x-button variant="primary" size="lg" icon="ri-restaurant-2-line" :href="route('public.menu')">
                    Buka menu
                </x-button>
            </div>
        </x-card>
    @elseif ($state === 'join')
        <x-card title="Gabung ke meja ini"
            description="Minta kode gabung 4 angka dari HP teman yang pertama scan QR ini.">
            <form wire:submit="join" class="space-y-4">
                <x-input label="Kode gabung" name="joinCode" wire:model="joinCode" size="lg" required
                    inputmode="numeric" pattern="[0-9]*" maxlength="4" autocomplete="one-time-code"
                    class="text-center text-2xl font-semibold tabular-nums tracking-widest" />

                <x-button type="submit" variant="primary" size="lg" :block="true" icon="ri-login-circle-line"
                    loading="join">
                    Gabung meja
                </x-button>
            </form>

            <p class="mt-4 text-sm text-base-content/70">
                Tidak ada yang duduk di meja ini? Panggil pelayan — sesi tamu sebelumnya mungkin belum ditutup.
            </p>
        </x-card>
    @else
        <x-card title="Buka meja ini"
            description="Kasir akan memastikan Anda duduk di meja ini sebelum pesanan bisa dikirim.">
            <form wire:submit="start" class="space-y-4">
                <x-input label="Nama pemesan" name="customerName" wire:model="customerName" size="lg" required
                    maxlength="60" autocomplete="name" hint="Kasir memakai nama ini untuk memanggil Anda." />

                <x-input label="Jumlah orang" name="pax" type="number" wire:model="pax" size="lg" required
                    min="1" max="50" inputmode="numeric" />

                <x-button type="submit" variant="primary" size="lg" :block="true" icon="ri-send-plane-2-line"
                    loading="start">
                    Minta buka meja
                </x-button>
            </form>
        </x-card>
    @endif
</div>
