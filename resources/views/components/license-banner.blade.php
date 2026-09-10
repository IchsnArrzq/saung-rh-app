{{--
    Dirender hanya lewat App\View\Components\LicenseBanner, yang sudah menyaring
    peran dan keadaan lisensinya. Padding dipegang komponen ini sendiri supaya
    tidak ada sisa jarak kosong di halaman yang tidak menampilkannya.
--}}

<div class="px-4 pt-5 md:px-6">
    <x-alert :type="$summary['state'] === 'expired' ? 'error' : 'warning'" :title="$summary['label']">
        <span class="block">
            @if ($summary['state'] === 'expired')
                Masa berlakunya sudah lewat. Perbarui datanya di pengaturan lisensi
                setelah perpanjangan diurus.
            @else
                Perpanjang sebelum tanggalnya lewat, lalu perbarui datanya di
                pengaturan lisensi.
            @endif

            @if ($summary['plan'])
                Paket saat ini: {{ $summary['plan'] }}.
            @endif
        </span>

        <x-button variant="ghost" size="sm" class="mt-2" icon="ri-key-2-line"
            :href="route('system.license')" wire:navigate>
            Buka pengaturan lisensi
        </x-button>
    </x-alert>
</div>
