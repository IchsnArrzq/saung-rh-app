<?php

namespace App\Livewire\Landing;

use App\Domains\Menu\QueryUseCases\GetMenuCatalogQueryUseCase;
use App\Support\RestaurantCart;
use Livewire\Component;

class Home extends Component
{
    public function quickAdd(string $menuId, GetMenuCatalogQueryUseCase $catalog)
    {
        $menu = $catalog->find($menuId);

        if (! $menu || ! $menu->is_available) {
            $this->addError('cart', 'Menu sedang tidak tersedia.');

            return null;
        }

        RestaurantCart::addItem($menu, 1);

        session()->flash('success', $menu->name.' berhasil ditambahkan ke cart.');

        return $this->redirectRoute('public.home', navigate: true);
    }

    public function render(GetMenuCatalogQueryUseCase $catalog)
    {
        return view('livewire.landing.home', [
            'menus' => $catalog->featured(4),
        ]);
    }
}
