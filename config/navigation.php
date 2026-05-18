<?php

return [
    'superadmin' => [
        [
            'label' => 'Dashboard',
            'icon' => 'ri-dashboard-line',
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'ri-dashboard-line', 'route' => 'dashboard', 'active' => ['dashboard']],
            ],
        ],
        [
            'label' => 'Point Of Sale',
            'icon' => 'ri-restaurant-line',
            'open' => true,
            'items' => [
                ['label' => 'POS Order', 'icon' => 'ri-shopping-basket-line', 'route' => 'pos.order.index', 'active' => ['pos.order.*']],
                ['label' => 'Offline / Online Order', 'icon' => 'ri-file-list-3-line', 'route' => 'orders.index', 'active' => ['orders.*']],
                ['label' => 'Payment', 'icon' => 'ri-wallet-3-line', 'route' => 'payments.index', 'active' => ['payments.*']],
                ['label' => 'Reservation', 'icon' => 'ri-calendar-check-line', 'route' => 'reservations.index', 'active' => ['reservations.*']],
            ],
        ],
        [
            'label' => 'Kitchen',
            'icon' => 'ri-knife-blood-line',
            'items' => [
                [
                    'label' => 'KDS Realtime',
                    'icon' => 'ri-radar-line',
                    'route' => 'kds.index',
                    'active' => ['kds.*'],
                    'badge' => ['type' => 'text', 'value' => 'WS'],
                ],
            ],
        ],
        [
            'label' => 'Table Management',
            'icon' => 'ri-layout-grid-line',
            'items' => [
                ['label' => 'Table', 'icon' => 'ri-layout-grid-line', 'route' => 'tables.index', 'active' => ['tables.*']],
                ['label' => 'Table Status', 'icon' => 'ri-flag-line', 'route' => 'table-statuses.index', 'active' => ['table-statuses.*']],
                ['label' => 'Table Category', 'icon' => 'ri-layout-2-line', 'route' => 'table-categories.index', 'active' => ['table-categories.*']],
            ],
        ],
        [
            'label' => 'Menu Management',
            'icon' => 'ri-restaurant-2-line',
            'items' => [
                ['label' => 'Menu', 'icon' => 'ri-bowl-line', 'route' => 'menus.index', 'active' => ['menus.*']],
                ['label' => 'Menu Status', 'icon' => 'ri-checkbox-circle-line', 'route' => 'menu-statuses.index', 'active' => ['menu-statuses.*']],
                ['label' => 'Menu Category', 'icon' => 'ri-price-tag-3-line', 'route' => 'menu-categories.index', 'active' => ['menu-categories.*']],
            ],
        ],
        [
            'label' => 'Reporting',
            'icon' => 'ri-bar-chart-box-line',
            'items' => [
                ['label' => 'Daily Report', 'icon' => 'ri-calendar-check-line', 'route' => 'reports.daily', 'active' => ['reports.daily']],
                ['label' => 'Monthly Report', 'icon' => 'ri-calendar-2-line', 'route' => 'reports.monthly', 'active' => ['reports.monthly']],
            ],
        ],
        [
            'label' => 'Access Control',
            'icon' => 'ri-shield-user-line',
            'items' => [
                ['label' => 'User Role & Permission', 'icon' => 'ri-lock-password-line', 'route' => 'settings.roles-permissions', 'active' => ['settings.roles-permissions']],
            ],
        ],
        [
            'label' => 'User Management',
            'icon' => 'ri-group-line',
            'items' => [
                ['label' => 'User', 'iconri-user-settings-line'],
            ],
        ],
    ],

    'admin' => [
        [
            'label' => 'Dashboard',
            'icon' => 'ri-dashboard-line',
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'ri-dashboard-line', 'route' => 'dashboard', 'active' => ['dashboard']],
            ],
        ],
        [
            'label' => 'Kitchen',
            'icon' => 'ri-knife-blood-line',
            'items' => [
                [
                    'label' => 'KDS Realtime',
                    'icon' => 'ri-radar-line',
                    'route' => 'kds.index',
                    'active' => ['kds.*'],
                    'badge' => ['type' => 'text', 'value' => 'WS'],
                ],
            ],
        ],
        [
            'label' => 'Table Management',
            'icon' => 'ri-layout-grid-line',
            'items' => [
                ['label' => 'Table', 'icon' => 'ri-layout-grid-line', 'route' => 'tables.index', 'active' => ['tables.*']],
                ['label' => 'Table Status', 'icon' => 'ri-flag-line', 'route' => 'table-statuses.index', 'active' => ['table-statuses.*']],
                ['label' => 'Table Category', 'icon' => 'ri-layout-2-line', 'route' => 'table-categories.index', 'active' => ['table-categories.*']],
            ],
        ],
        [
            'label' => 'Menu Management',
            'icon' => 'ri-restaurant-2-line',
            'items' => [
                ['label' => 'Menu', 'icon' => 'ri-bowl-line', 'route' => 'menus.index', 'active' => ['menus.*']],
                ['label' => 'Menu Status', 'icon' => 'ri-checkbox-circle-line', 'route' => 'menu-statuses.index', 'active' => ['menu-statuses.*']],
                ['label' => 'Menu Category', 'icon' => 'ri-price-tag-3-line', 'route' => 'menu-categories.index', 'active' => ['menu-categories.*']],
            ],
        ],
        [
            'label' => 'Reservations & POS',
            'icon' => 'ri-shopping-bag-3-line',
            'open' => true,
            'items' => [
                ['label' => 'Reservation', 'icon' => 'ri-calendar-check-line', 'route' => 'reservations.index', 'active' => ['reservations.*']],
                ['label' => 'POS Order', 'icon' => 'ri-shopping-basket-line', 'route' => 'pos.order.index', 'active' => ['pos.order.*'], 'badge' => ['type' => 'dynamic', 'resolver' => 'active_orders']],
                ['label' => 'Order Offline / Online', 'icon' => 'ri-file-list-3-line', 'route' => 'orders.index', 'active' => ['orders.*']],
                ['label' => 'Payment', 'icon' => 'ri-wallet-3-line', 'route' => 'payments.index', 'active' => ['payments.*']],
            ],
        ],
        [
            'label' => 'Reporting',
            'icon' => 'ri-bar-chart-box-line',
            'items' => [
                ['label' => 'Daily Report', 'icon' => 'ri-calendar-check-line', 'route' => 'reports.daily', 'active' => ['reports.daily']],
                ['label' => 'Monthly Report', 'icon' => 'ri-calendar-2-line', 'route' => 'reports.monthly', 'active' => ['reports.monthly']],
            ],
        ],
    ],

    'cashier' => [
        [
            'label' => 'POS',
            'icon' => 'ri-shopping-basket-line',
            'open' => true,
            'items' => [
                ['label' => 'POS Order', 'icon' => 'ri-shopping-basket-line', 'route' => 'pos.order.index', 'active' => ['pos.order.*']],
                ['label' => 'Order', 'icon' => 'ri-file-list-3-line', 'route' => 'orders.index', 'active' => ['orders.*'], 'badge' => ['type' => 'dynamic', 'resolver' => 'active_orders']],
                ['label' => 'Order Item', 'icon' => 'ri-list-check-2', 'route' => 'orders.index', 'active' => ['orders.*']],
                ['label' => 'Payment', 'icon' => 'ri-wallet-3-line', 'route' => 'payments.index', 'active' => ['payments.*']],
            ],
        ],
    ],

    'customer' => [
        [
            'label' => 'Customer',
            'icon' => 'ri-user-3-line',
            'open' => true,
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'ri-dashboard-line', 'route' => 'customer.dashboard', 'active' => ['customer.dashboard']],
                ['label' => 'Reservation', 'icon' => 'ri-calendar-check-line', 'route' => 'customer.bookings.create', 'active' => ['customer.bookings.*']],
                ['label' => 'Reservation Item', 'icon' => 'ri-list-check-2', 'route' => 'customer.menus.cart.index', 'active' => ['customer.menus.cart.*']],
                ['label' => 'Pilih Meja', 'icon' => 'ri-layout-grid-line', 'route' => 'customer.menus.tables', 'active' => ['customer.menus.tables']],
                ['label' => 'Pilih Menu', 'icon' => 'ri-bowl-line', 'route' => 'customer.menus.index', 'active' => ['customer.menus.*']],
            ],
        ],
    ],
];
