<x-guest-layout>
    @include('menus.partials.show-content', [
        'variant' => 'public',
        'menu' => $menu,
        'mode' => $mode,
        'tableId' => $tableId,
        'relatedMenus' => $relatedMenus,
    ])
</x-guest-layout>
