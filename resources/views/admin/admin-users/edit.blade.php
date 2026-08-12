<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Edit Admin: {{ $admin_user->name }}</h2>
    </x-slot>

    @include('admin.partials.flash')

    <x-card class="mt-5">
        <form method="POST" action="{{ route('admin-users.update', $admin_user) }}" class="space-y-5">
            @csrf
            @method('PUT')
            
            @include('admin.admin-users._form')

            <x-form-actions submit-label="Update Admin" :cancel-href="route('admin-users.index')" />
        </form>
    </x-card>
</x-admin-layout>
