<x-customer-layout>
    @include('menus.partials.show-content', [
        'variant' => 'customer',
        'menu' => $menu,
        'table' => $table,
        'relatedMenus' => $relatedMenus,
        'cartCount' => $cartCount,
        'cartSubtotal' => $cartSubtotal,
    ])
</x-customer-layout>
