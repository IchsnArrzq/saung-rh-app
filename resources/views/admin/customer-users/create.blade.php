<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Tambah Customer Baru</h2>
    </x-slot>

    @include('admin.partials.flash')

    <div class="rounded-2xl border border-stone-200 bg-white p-5 mt-5">
        <form method="POST" action="{{ route('customer-users.store') }}" class="space-y-5">
            @csrf
            
            @include('admin.customer-users._form')

            <div class="flex gap-2 pt-4">
                <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">Simpan Customer</button>
                <a href="{{ route('customer-users.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</x-admin-layout>
