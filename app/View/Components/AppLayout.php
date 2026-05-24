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
        $this->portal = auth()->user()->roles->first()->name;

        return view('layouts.app');
    }
}
