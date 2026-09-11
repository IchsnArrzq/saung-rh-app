<x-error-page code="401" title="Silakan masuk dulu" icon="ri-user-shared-line" tone="info" :exception="$exception ?? null">
    Halaman ini hanya untuk akun yang sudah masuk. Masuk dengan akun Anda, lalu buka lagi halamannya.

    <x-slot:actions>
        @if (\Illuminate\Support\Facades\Route::has('login'))
            <x-button variant="primary" icon="ri-login-box-line" :href="route('login')">Masuk</x-button>
        @endif
        <x-button variant="ghost" icon="ri-home-4-line" :href="route('public.home')">Ke beranda</x-button>
    </x-slot:actions>
</x-error-page>
