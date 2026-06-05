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
    class="{{ $variant === 'top' ? 'rounded-full px-4 py-2 font-semibold text-rose-600 hover:bg-rose-50' : 'flex flex-col items-center justify-center gap-1 py-3 text-rose-500 w-full bg-transparent border-none' }}"
>
    @if($variant === 'top')
        Logout
    @else
        <i class="ri-logout-box-r-line text-lg"></i>
        <span>Logout</span>
    @endif
</button>