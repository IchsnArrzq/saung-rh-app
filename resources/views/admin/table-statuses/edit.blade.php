<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Edit Status Meja</h2>
    </x-slot>

    @include('admin.partials.flash')

    <div class="rounded-2xl border border-stone-200 bg-white p-5">
        <form action="{{ route('table-statuses.update', $tableStatus) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')
            @include('admin.table-statuses._form')

            <div class="flex gap-2">
                <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">Update</button>
                <a href="{{ route('table-statuses.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
