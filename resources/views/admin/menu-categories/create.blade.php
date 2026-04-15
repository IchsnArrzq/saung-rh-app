<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Tambah Kategori Menu</h2>
    </x-slot>

    @include('admin.partials.flash')

    <div class="rounded-2xl border border-stone-200 bg-white p-5">
        <form action="{{ route('menu-categories.store') }}" method="POST" class="space-y-5">
            @csrf
            @include('admin.menu-categories._form')

            <div class="flex gap-2">
                <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">Simpan</button>
                <a href="{{ route('menu-categories.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
