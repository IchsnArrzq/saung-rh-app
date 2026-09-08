<div>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">QR meja {{ $table->code }}</h2>
            <x-button variant="ghost" size="sm" :href="route('tables.index')" wire:navigate>Kembali</x-button>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[320px_1fr]">
        <x-card>
            <img src="{{ $qrImageUrl }}" alt="QR meja {{ $table->code }}"
                class="mx-auto h-72 w-72 rounded-xl object-contain">
            <p class="mt-3 text-center text-sm text-base-content/70">
                Tempel QR ini di meja {{ $table->code }}.
            </p>
        </x-card>

        <x-card title="Tautan QR"
            description="Tautan ini membuka daftar menu dengan meja {{ $table->code }} sudah terpilih.">
            <div class="rounded-xl bg-base-200 p-3 text-sm break-all">
                {{ $menuUrl }}
            </div>

            {{-- Penjelasan untuk staf yang memasang QR, bukan catatan implementasi:
                 sumber pesanannya adalah OrderSource::DineInQr. --}}
            <p class="mt-4 text-sm text-base-content/70">
                Pelanggan memindai QR, memilih menu, lalu mengirim pesanan. Pesanannya masuk ke
                halaman Pesanan dengan sumber “QR”.
            </p>
        </x-card>
    </div>
</div>
