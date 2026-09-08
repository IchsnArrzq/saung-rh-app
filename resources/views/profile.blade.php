<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">Profil</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6">
            <x-card padding="lg">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </x-card>

            <x-card padding="lg">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </x-card>

            <x-card padding="lg">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
