<?php

namespace App\Http\Controllers\KDS;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Thin wrapper — the Kitchen Display System lives entirely in the
 * Livewire\Kds\Board component, which reads and writes through the Order
 * domain's UseCases.
 */
class KdsController extends Controller
{
    public function index(): View
    {
        return view('kds.index');
    }
}
