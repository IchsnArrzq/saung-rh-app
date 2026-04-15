<div>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">QR Meja {{ $table->code }}</h2>
            <a href="{{ route('tables.index') }}" class="btn btn-sm btn-ghost">Kembali</a>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[320px_1fr]">
        <div class="rounded-2xl border border-stone-200 bg-white p-4">
            <img src="{{ $qrImageUrl }}" alt="QR Meja {{ $table->code }}" class="mx-auto h-72 w-72 rounded-xl border border-stone-200 object-contain">
            <p class="mt-3 text-center text-sm text-stone-600">Scan QR ini di meja untuk order offline.</p>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-5">
            <h3 class="text-lg font-semibold text-stone-900">URL QR (Berisi Table ID)</h3>
            <p class="mt-1 text-sm text-stone-600">URL ini membuka menu mode offline dengan konteks meja otomatis.</p>

            <div class="mt-4 rounded-xl border border-stone-200 bg-stone-50 p-3 text-sm break-all text-stone-800">
                {{ $menuUrl }}
            </div>

            <div class="mt-4 space-y-2 text-sm text-stone-600">
                <p><span class="font-semibold text-stone-800">Alur Offline:</span> Scan QR -> pilih menu -> tambah ke cart -> kirim pesanan.</p>
                <p><span class="font-semibold text-stone-800">Hasil:</span> Pesanan masuk ke modul admin `Orders` dengan catatan sumber `OFFLINE QR`.</p>
            </div>
        </div>
    </div>
</div>
