<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Dashboard Admin</h2>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('menus.index') }}" class="rounded-2xl border border-stone-200 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-stone-500">Master</p>
            <p class="mt-2 text-lg font-semibold text-stone-900">Manajemen Menu</p>
            <p class="mt-1 text-sm text-stone-600">Kelola menu, harga, dan status tersedia.</p>
        </a>

        <a href="{{ route('tables.index') }}" class="rounded-2xl border border-stone-200 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-stone-500">Operasional</p>
            <p class="mt-2 text-lg font-semibold text-stone-900">Manajemen Meja</p>
            <p class="mt-1 text-sm text-stone-600">Atur status meja dan kapasitas.</p>
        </a>

        <a href="{{ route('orders.index') }}" class="rounded-2xl border border-stone-200 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-stone-500">Transaksi</p>
            <p class="mt-2 text-lg font-semibold text-stone-900">Pemesanan Digital</p>
            <p class="mt-1 text-sm text-stone-600">Input order dan item pesanan pelanggan.</p>
        </a>

        <a href="{{ route('payments.index') }}" class="rounded-2xl border border-stone-200 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-stone-500">Kasir</p>
            <p class="mt-2 text-lg font-semibold text-stone-900">Pembayaran</p>
            <p class="mt-1 text-sm text-stone-600">Catat pembayaran order secara penuh.</p>
        </a>
    </div>

    <div class="mt-6 rounded-2xl border border-stone-200 bg-white p-5">
        <h3 class="text-lg font-semibold text-stone-900">Modul Admin Aktif</h3>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('menu-categories.index') }}" class="badge badge-outline p-3">Kategori Menu</a>
            <a href="{{ route('table-categories.index') }}" class="badge badge-outline p-3">Kategori Meja</a>
            <a href="{{ route('table-statuses.index') }}" class="badge badge-outline p-3">Status Meja</a>
            <a href="{{ route('reservations.index') }}" class="badge badge-outline p-3">Reservasi</a>
        </div>
    </div>
</x-admin-layout>
