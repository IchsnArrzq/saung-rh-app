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
            'items' => [
                ['label' => 'POS Order', 'icon' => 'ri-shopping-basket-line', 'route' => 'pos.order.index', 'active' => ['pos.order.*']],
                ['label' => 'Order', 'icon' => 'ri-file-list-3-line', 'route' => 'orders.index', 'active' => ['orders.*']],
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
            'label' => 'Inventory',
            'icon' => 'ri-stack-line',
            'items' => [
                ['label' => 'Bahan Makanan', 'icon' => 'ri-leaf-line', 'route' => 'ingredients.index', 'active' => ['ingredients.*']],
                ['label' => 'Stock Opname', 'icon' => 'ri-archive-stack-line', 'route' => 'stock-opnames.index', 'active' => ['stock-opnames.*']],
            ],
        ],
        [
            'label' => 'Reporting',
            'icon' => 'ri-bar-chart-box-line',
            'items' => [
                ['label' => 'Reports', 'icon' => 'ri-file-chart-line', 'route' => 'reports.index', 'active' => ['reports.index']],
            ],
        ],
        [
            'label' => 'Access Control',
            'icon' => 'ri-shield-user-line',
            'items' => [
                ['label' => 'User Role & Permission', 'icon' => 'ri-lock-password-line', 'route' => 'settings.roles-permissions', 'active' => ['settings.roles-permissions']],
                ['label' => 'Navigation Settings', 'icon' => 'ri-layout-top-line', 'route' => 'settings.navigation', 'active' => ['settings.navigation']],
            ],
        ],
        [
            'label' => 'User Management',
            'icon' => 'ri-group-line',
            'items' => [
                ['label' => 'User', 'icon' => 'ri-user-settings-line', 'route' => 'admin-users.index', 'active' => ['admin-users.index']],
                ['label' => 'Admin', 'icon' => 'ri-user-smile-line', 'route' => 'customer-users.index', 'active' => ['customer-users.index']],
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
            'items' => [
                ['label' => 'Reservation', 'icon' => 'ri-calendar-check-line', 'route' => 'reservations.index', 'active' => ['reservations.*']],
                ['label' => 'POS Order', 'icon' => 'ri-shopping-basket-line', 'route' => 'pos.order.index', 'active' => ['pos.order.*'], 'badge' => ['type' => 'dynamic', 'resolver' => 'active_orders']],
                ['label' => 'Order', 'icon' => 'ri-file-list-3-line', 'route' => 'orders.index', 'active' => ['orders.*']],
                ['label' => 'Payment', 'icon' => 'ri-wallet-3-line', 'route' => 'payments.index', 'active' => ['payments.*']],
            ],
        ],
        [
            'label' => 'Inventory',
            'icon' => 'ri-stack-line',
            'items' => [
                ['label' => 'Bahan Makanan', 'icon' => 'ri-leaf-line', 'route' => 'ingredients.index', 'active' => ['ingredients.*']],
                ['label' => 'Stock Opname', 'icon' => 'ri-archive-stack-line', 'route' => 'stock-opnames.index', 'active' => ['stock-opnames.*']],
            ],
        ],
        [
            'label' => 'Reporting',
            'icon' => 'ri-bar-chart-box-line',
            'items' => [
                ['label' => 'Reports', 'icon' => 'ri-file-chart-line', 'route' => 'reports.index', 'active' => ['reports.index']],
            ],
        ],
        [
            'label' => 'Settings',
            'icon' => 'ri-settings-3-line',
            'items' => [
                ['label' => 'Navigation Settings', 'icon' => 'ri-layout-top-line', 'route' => 'settings.navigation', 'active' => ['settings.navigation']],
            ],
        ],
    ],

    'cashier' => [
        [
            'label' => 'POS',
            'icon' => 'ri-shopping-basket-line',
            'items' => [
                ['label' => 'POS Order', 'icon' => 'ri-shopping-basket-line', 'route' => 'pos.order.index', 'active' => ['pos.order.*']],
                ['label' => 'Order', 'icon' => 'ri-file-list-3-line', 'route' => 'orders.index', 'active' => ['orders.*'], 'badge' => ['type' => 'dynamic', 'resolver' => 'active_orders']],
                ['label' => 'Order Item', 'icon' => 'ri-list-check-2', 'route' => 'orders.index', 'active' => ['orders.*']],
                ['label' => 'Payment', 'icon' => 'ri-wallet-3-line', 'route' => 'payments.index', 'active' => ['payments.*']],
                ['label' => 'Navigation Settings', 'icon' => 'ri-layout-top-line', 'route' => 'settings.navigation', 'active' => ['settings.navigation']],
            ],
        ],
    ],

    'manager' => [
        [
            'label' => 'Manager',
            'icon' => 'ri-bar-chart-box-line',
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'ri-dashboard-line', 'route' => 'manager.dashboard', 'active' => ['manager.dashboard']],
                ['label' => 'Booking Management', 'icon' => 'ri-calendar-check-line', 'route' => 'receptionist.bookings', 'active' => ['receptionist.bookings']],
                ['label' => 'F&B Top Analytics', 'icon' => 'ri-bar-chart-box-line', 'route' => 'receptionist.analytics', 'active' => ['receptionist.analytics']],
                ['label' => 'Navigation Settings', 'icon' => 'ri-layout-top-line', 'route' => 'settings.navigation', 'active' => ['settings.navigation']],
            ],
        ],
    ],

    'receptionist' => [
        [
            'label' => 'Resepsionis',
            'icon' => 'ri-customer-service-2-line',
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'ri-dashboard-line', 'route' => 'receptionist.dashboard', 'active' => ['receptionist.dashboard']],
                ['label' => 'Live Kitchen Monitor', 'icon' => 'ri-radar-line', 'route' => 'kds.index', 'active' => ['kds.*'], 'badge' => ['type' => 'text', 'value' => 'WS']],
                ['label' => 'Table Map', 'icon' => 'ri-layout-grid-line', 'route' => 'receptionist.table-map', 'active' => ['receptionist.table-map']],
                ['label' => 'Booking Management', 'icon' => 'ri-calendar-check-line', 'route' => 'receptionist.bookings', 'active' => ['receptionist.bookings']],
                ['label' => 'Visitor Counter', 'icon' => 'ri-group-line', 'route' => 'receptionist.visitors', 'active' => ['receptionist.visitors']],
                ['label' => 'F&B Top Analytics', 'icon' => 'ri-bar-chart-box-line', 'route' => 'receptionist.analytics', 'active' => ['receptionist.analytics']],
                ['label' => 'Navigation Settings', 'icon' => 'ri-layout-top-line', 'route' => 'settings.navigation', 'active' => ['settings.navigation']],
            ],
        ],
    ],

    'waiter' => [
        [
            'label' => 'Waiter',
            'icon' => 'ri-walk-line',
            'items' => [
                ['label' => 'Portal Waiter', 'icon' => 'ri-dashboard-line', 'route' => 'waiter.dashboard', 'active' => ['waiter.dashboard']],
                ['label' => 'Update Status Meja', 'icon' => 'ri-refresh-line', 'route' => 'waiter.tables', 'active' => ['waiter.tables']],
                ['label' => 'Tips & Service Log', 'icon' => 'ri-hand-coin-line', 'route' => 'waiter.tips', 'active' => ['waiter.tips']],
                ['label' => 'Navigation Settings', 'icon' => 'ri-layout-top-line', 'route' => 'settings.navigation', 'active' => ['settings.navigation']],
            ],
        ],
    ],

    'chef' => [
        [
            'label' => 'Kitchen',
            'icon' => 'ri-knife-blood-line',
            'items' => [
                ['label' => 'KDS Realtime', 'icon' => 'ri-radar-line', 'route' => 'kds.index', 'active' => ['kds.*'], 'badge' => ['type' => 'text', 'value' => 'WS']],
                ['label' => 'Navigation Settings', 'icon' => 'ri-layout-top-line', 'route' => 'settings.navigation', 'active' => ['settings.navigation']],
            ],
        ],
    ],

    'ob' => [
        [
            'label' => 'Office Boy',
            'icon' => 'ri-brush-line',
            'items' => [
                ['label' => 'Portal OB', 'icon' => 'ri-dashboard-line', 'route' => 'ob.dashboard', 'active' => ['ob.dashboard']],
                ['label' => 'Pembersihan Meja', 'icon' => 'ri-brush-line', 'route' => 'ob.tables', 'active' => ['ob.tables']],
                ['label' => 'Navigation Settings', 'icon' => 'ri-layout-top-line', 'route' => 'settings.navigation', 'active' => ['settings.navigation']],
            ],
        ],
    ],

    'customer' => [
        [
            'label' => 'Customer',
            'icon' => 'ri-user-3-line',
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
