@php
    // Tamu yang belum masuk diarahkan ke halaman masuk; yang sudah masuk ke
    // halaman pertama yang memang boleh dibukanya (PortalHome).
    $signedIn = auth()->check();
    $homeForUser = $signedIn
        ? rescue(fn () => \App\Support\PortalHome::url(auth()->user()), route('public.home'), false)
        : (\Illuminate\Support\Facades\Route::has('login') ? route('login') : route('public.home'));
@endphp

<x-error-page code="403" title="Anda belum punya akses ke halaman ini" icon="ri-lock-2-line" tone="warning"
    :exception="$exception ?? null">
    Peran akun Anda belum diberi izin membuka halaman ini. Kalau menurut Anda ini keliru, minta admin memeriksa
    pengaturannya di Peran &amp; hak akses.

    <x-slot:actions>
        <x-button variant="primary" :icon="$signedIn ? 'ri-dashboard-line' : 'ri-login-box-line'" :href="$homeForUser">
            {{ $signedIn ? 'Ke halaman saya' : 'Masuk' }}
        </x-button>
        <x-button variant="ghost" icon="ri-arrow-left-line" onclick="history.length > 1 ? history.back() : location.assign('/')">
            Kembali
        </x-button>
    </x-slot:actions>
</x-error-page>
