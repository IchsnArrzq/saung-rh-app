@props([
    'accounts',
    'expected' => false,
])

{{--
    Rekening tujuan untuk metode bayar yang sedang dipilih.

    Isinya datang dari GetPaymentAccountsQueryUseCase, jadi yang tampil hanya
    rekening yang benar-benar ada barisnya di database — jangan menambahkan
    nomor contoh atau instruksi karangan di sini.

    Tunai dan kartu tidak punya rekening tujuan, jadi tidak merender apa pun.
--}}

@if ($accounts->isNotEmpty())
    <div class="rounded-xl border border-base-300 bg-base-200/50 p-4">
        <p class="text-sm font-medium">Rekening tujuan</p>

        <ul class="mt-2 divide-y divide-base-300">
            @foreach ($accounts as $account)
                <li class="py-2 first:pt-0 last:pb-0">
                    <div class="flex items-baseline justify-between gap-2">
                        <span class="text-sm font-medium">{{ $account->label }}</span>
                        @if ($account->provider)
                            <span class="text-sm text-base-content/60">{{ $account->provider }}</span>
                        @endif
                    </div>

                    @if ($account->account_number)
                        <p class="text-sm tabular-nums">{{ $account->account_number }}</p>
                    @endif

                    @if ($account->account_holder)
                        <p class="text-sm text-base-content/60">a.n. {{ $account->account_holder }}</p>
                    @endif

                    @if ($account->instructions)
                        <p class="text-sm text-base-content/60">{{ $account->instructions }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@elseif ($expected)
    {{-- Tanpa tautan: rute pengaturan rekening hanya terbuka untuk admin, dan
         yang membaca layar ini kasir atau resepsionis. --}}
    <x-alert type="warning">
        Belum ada rekening aktif untuk metode ini. Minta admin menambahkannya di
        Pengaturan sistem sebelum menerima pembayaran.
    </x-alert>
@endif
