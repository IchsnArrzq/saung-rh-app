<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    public string $variant = 'top';

    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<button 
    type="button" 
    wire:click="logout" 
    class="@if ($variant === 'sidebar') mt-2 flex w-full items-center gap-3 rounded-box px-3 py-2 text-sm font-semibold text-error hover:bg-base-300 @elseif ($variant === 'menu') flex w-full items-center gap-2 text-left font-medium text-error @elseif ($variant === 'top') rounded-full px-4 py-2 font-semibold text-rose-600 hover:bg-rose-50 @else flex w-full flex-col items-center justify-center gap-1 border-none bg-transparent py-3 text-rose-500 @endif"
>
    @if ($variant === 'sidebar')
        <i class="ri-logout-box-r-line text-lg"></i>
        <span>Logout</span>
    @elseif ($variant === 'menu')
        <i class="ri-logout-box-r-line"></i>
        <span>Logout</span>
    @elseif($variant === 'top')
        Logout
    @else
        <i class="ri-logout-box-r-line text-lg"></i>
        <span>Logout</span>
    @endif
</button>
