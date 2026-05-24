<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Edit Admin: {{ $admin_user->name }}</h2>
    </x-slot>

    @include('admin.partials.flash')

    <div class="rounded-2xl border border-stone-200 bg-white p-5 mt-5">
        <form method="POST" action="{{ route('admin-users.update', $admin_user) }}" class="space-y-5">
            @csrf
            @method('PUT')
            
            @include('admin.admin-users._form')

            <div class="flex gap-2 pt-4">
                <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">Update Admin</button>
                <a href="{{ route('admin-users.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</x-admin-layout>
