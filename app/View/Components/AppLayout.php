<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public $portal;

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        // Pengguna tanpa peran (mis. akun baru yang perannya belum di-set) tidak boleh
        // meruntuhkan seluruh kerangka halaman — layouts.app sudah punya fallback portal.
        $this->portal = auth()->user()?->roles->first()?->name;

        return view('layouts.app');
    }
}
