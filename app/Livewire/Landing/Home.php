<?php

namespace App\Livewire\Landing;

use App\Models\Menu;
use App\Services\Landing\PublicHomeService;
use App\Support\RestaurantCart;
use Livewire\Component;

class Home extends Component
{
    public function quickAdd(string $menuId)
    {
        $menu = Menu::query()->where('is_available', true)->find($menuId);

        if (! $menu) {
            $this->addError('cart', 'Menu sedang tidak tersedia.');
            return;
        }

        RestaurantCart::addItem($menu, 1);

        session()->flash('success', $menu->name . ' berhasil ditambahkan ke cart.');

        return $this->redirectRoute('public.home', navigate: true);
    }

    public function render(PublicHomeService $publicHomeService)
    {
        return view('livewire.landing.home', [
            'menus' => $publicHomeService->featuredMenus(4),
        ]);
    }
}
