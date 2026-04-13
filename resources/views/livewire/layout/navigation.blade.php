<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    public $initial;
    public $profileUrl;
    public function mount(): void
    {
        $this->initial = strtoupper(substr(auth()->user()->name ?? 'A', 0, 1));
        $this->profileUrl = route('profile');
    }
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>
<header class="border-b border-stone-200 bg-amber-50 px-4 py-4 md:px-6">
    <div class="flex items-center gap-3">

        <label for="admin-drawer" aria-label="open sidebar" class="btn btn-square btn-ghost">
            <!-- Sidebar toggle icon -->
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linejoin="round" stroke-linecap="round"
                stroke-width="2" fill="none" stroke="currentColor" class="my-1.5 inline-block size-4">
                <path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"></path>
                <path d="M9 4v16"></path>
                <path d="M14 10l2 2l-2 2"></path>
            </svg>
        </label>

        <div class="mr-auto">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-stone-500">Control Room</p>
            <p class="text-lg font-semibold text-stone-900">Saung RH Admin</p>
        </div>

        <a href="/"
            class="hidden items-center gap-2 rounded-xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-emerald-700 hover:text-emerald-800 md:inline-flex">
            <i class="ri-external-link-line text-base"></i>
            <span>Public Site</span>
        </a>

        <details class="dropdown dropdown-end">
            <summary
                class="flex cursor-pointer list-none items-center gap-2 rounded-xl border border-stone-200 bg-white px-2 py-1 pr-3">
                <span
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-800 text-sm font-bold text-amber-50">
                    {{ $initial }}
                </span>
                <span class="hidden text-left md:block">
                    <span
                        class="block text-sm font-semibold text-stone-800">{{ auth()->user()->name ?? 'Admin' }}</span>
                    <span class="block text-xs text-stone-500">{{ auth()->user()->email ?? '-' }}</span>
                </span>
                <i class="ri-arrow-down-s-line text-xl text-stone-500"></i>
            </summary>
            <ul class="menu dropdown-content mt-2 w-56 rounded-2xl border border-stone-200 bg-white p-2 shadow-lg">
                <li>
                    <a href="{{ $profileUrl }}" class="font-medium text-stone-700">
                        <i class="ri-user-3-line"></i>
                        Profile
                    </a>
                </li>
                <li>
                    <a href="#" class="font-medium text-stone-700">
                        <i class="ri-settings-3-line"></i>
                        Settings
                    </a>
                </li>
                <li>
                    <button type="button" wire:click="logout"
                        class="flex w-full items-center gap-2 text-left font-medium text-stone-700">
                        <i class="ri-logout-box-r-line"></i>
                        Logout
                    </button>
                </li>
            </ul>
        </details>
    </div>
</header>
