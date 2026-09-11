<?php

/*
|--------------------------------------------------------------------------
| Navigasi samping per peran bawaan
|--------------------------------------------------------------------------
|
| App\Support\SidebarNavigation menyaring SETIAP item lewat
| App\Support\RouteAccess (middleware rute itu sendiri), jadi item yang
| berakhir di 403 tidak pernah tampil. Karena itu susunan superadmin dan admin
| boleh sama — yang tidak boleh dibuka admin hilang dengan sendirinya.
|
| Peran buatan layar Peran & hak akses tidak punya susunan sendiri: ia memakai
| 'superadmin' (terlengkap) yang disaring dengan cara yang sama.
*/

$panelMeja = ['label' => 'Panel meja', 'icon' => 'ri-layout-grid-line', 'route' => 'floor.index', 'active' => ['floor.index']];
$antreanLagu = ['label' => 'Antrean lagu', 'icon' => 'ri-music-2-line', 'route' => 'songs.queue', 'active' => ['songs.queue']];
$kategoriPermintaan = ['label' => 'Kategori permintaan', 'icon' => 'ri-price-tag-3-line', 'route' => 'special-request-categories.index', 'active' => ['special-request-categories.*']];
$layarDapur = ['label' => 'Layar dapur (KDS)', 'icon' => 'ri-radar-line', 'route' => 'kds.index', 'active' => ['kds.*']];
$pengaturanNavigasi = ['label' => 'Pengaturan navigasi', 'icon' => 'ri-layout-top-line', 'route' => 'settings.navigation', 'active' => ['settings.navigation']];

$lengkap = [
    [
        'label' => 'Dashboard',
        'icon' => 'ri-dashboard-line',
        'items' => [
            ['label' => 'Dashboard', 'icon' => 'ri-dashboard-line', 'route' => 'dashboard', 'active' => ['dashboard']],
        ],
    ],
    [
        'label' => 'Kasir & pesanan',
        'icon' => 'ri-restaurant-line',
        'items' => [
            ['label' => 'Kasir (POS)', 'icon' => 'ri-shopping-basket-line', 'route' => 'pos.order.index', 'active' => ['pos.order.*'], 'badge' => ['type' => 'dynamic', 'resolver' => 'active_orders']],
            ['label' => 'Tagihan meja', 'icon' => 'ri-cash-line', 'route' => 'pos.bills', 'active' => ['pos.bills']],
            ['label' => 'Pesanan', 'icon' => 'ri-file-list-3-line', 'route' => 'orders.index', 'active' => ['orders.*']],
            ['label' => 'Pembayaran', 'icon' => 'ri-wallet-3-line', 'route' => 'payments.index', 'active' => ['payments.*']],
            ['label' => 'Reservasi', 'icon' => 'ri-calendar-check-line', 'route' => 'reservations.index', 'active' => ['reservations.*']],
        ],
    ],
    [
        'label' => 'Layanan tamu',
        'icon' => 'ri-service-line',
        'items' => [$panelMeja, $antreanLagu, $kategoriPermintaan],
    ],
    [
        'label' => 'Dapur',
        'icon' => 'ri-knife-blood-line',
        'items' => [$layarDapur],
    ],
    [
        'label' => 'Portal manajer',
        'icon' => 'ri-briefcase-line',
        'items' => [
            ['label' => 'Beranda manajer', 'icon' => 'ri-dashboard-line', 'route' => 'manager.dashboard', 'active' => ['manager.dashboard']],
            ['label' => 'Jadwal shift', 'icon' => 'ri-calendar-schedule-line', 'route' => 'manager.shifts', 'active' => ['manager.shifts']],
            ['label' => 'KPI pegawai', 'icon' => 'ri-trophy-line', 'route' => 'manager.kpi', 'active' => ['manager.kpi']],
            ['label' => 'Pelanggan paling sering datang', 'icon' => 'ri-vip-crown-line', 'route' => 'manager.top-customers', 'active' => ['manager.top-customers']],
        ],
    ],
    [
        'label' => 'Portal resepsionis',
        'icon' => 'ri-customer-service-2-line',
        'items' => [
            ['label' => 'Beranda resepsionis', 'icon' => 'ri-dashboard-line', 'route' => 'receptionist.dashboard', 'active' => ['receptionist.dashboard']],
            ['label' => 'Peta meja', 'icon' => 'ri-map-2-line', 'route' => 'receptionist.table-map', 'active' => ['receptionist.table-map']],
            ['label' => 'Kelola reservasi', 'icon' => 'ri-calendar-check-line', 'route' => 'receptionist.bookings', 'active' => ['receptionist.bookings']],
            ['label' => 'Hitung pengunjung', 'icon' => 'ri-group-2-line', 'route' => 'receptionist.visitors', 'active' => ['receptionist.visitors']],
            ['label' => 'Menu paling laku', 'icon' => 'ri-bar-chart-box-line', 'route' => 'receptionist.analytics', 'active' => ['receptionist.analytics']],
        ],
    ],
    [
        'label' => 'Portal pelayan & OB',
        'icon' => 'ri-walk-line',
        'items' => [
            ['label' => 'Beranda pelayan', 'icon' => 'ri-walk-line', 'route' => 'waiter.dashboard', 'active' => ['waiter.dashboard']],
            ['label' => 'Ubah status meja', 'icon' => 'ri-refresh-line', 'route' => 'waiter.tables', 'active' => ['waiter.tables']],
            ['label' => 'Tip & layanan', 'icon' => 'ri-hand-coin-line', 'route' => 'waiter.tips', 'active' => ['waiter.tips']],
            ['label' => 'Beranda OB', 'icon' => 'ri-brush-line', 'route' => 'ob.dashboard', 'active' => ['ob.dashboard']],
            ['label' => 'Pembersihan meja', 'icon' => 'ri-brush-2-line', 'route' => 'ob.tables', 'active' => ['ob.tables']],
        ],
    ],
    [
        'label' => 'Meja',
        'icon' => 'ri-layout-2-line',
        'items' => [
            ['label' => 'Meja', 'icon' => 'ri-layout-grid-line', 'route' => 'tables.index', 'active' => ['tables.*']],
            ['label' => 'Kategori meja', 'icon' => 'ri-layout-2-line', 'route' => 'table-categories.index', 'active' => ['table-categories.*']],
            ['label' => 'Sesi meja', 'icon' => 'ri-qr-scan-2-line', 'route' => 'table-sessions.index', 'active' => ['table-sessions.*']],
        ],
    ],
    [
        'label' => 'Menu',
        'icon' => 'ri-restaurant-2-line',
        'items' => [
            ['label' => 'Menu', 'icon' => 'ri-bowl-line', 'route' => 'menus.index', 'active' => ['menus.*']],
            ['label' => 'Kategori menu', 'icon' => 'ri-price-tag-3-line', 'route' => 'menu-categories.index', 'active' => ['menu-categories.*']],
        ],
    ],
    [
        'label' => 'Inventori',
        'icon' => 'ri-stack-line',
        'items' => [
            ['label' => 'Bahan makanan', 'icon' => 'ri-leaf-line', 'route' => 'ingredients.index', 'active' => ['ingredients.*']],
            ['label' => 'Stock opname', 'icon' => 'ri-archive-stack-line', 'route' => 'stock-opnames.index', 'active' => ['stock-opnames.*']],
            ['label' => 'Riwayat stok', 'icon' => 'ri-history-line', 'route' => 'stock-movements.index', 'active' => ['stock-movements.*']],
        ],
    ],
    [
        'label' => 'Kontak',
        'icon' => 'ri-contacts-book-line',
        'items' => [
            ['label' => 'Supplier', 'icon' => 'ri-truck-line', 'route' => 'suppliers.index', 'active' => ['suppliers.*']],
            ['label' => 'Pelanggan', 'icon' => 'ri-user-heart-line', 'route' => 'customers.index', 'active' => ['customers.*']],
        ],
    ],
    [
        'label' => 'Pembelian & penjualan',
        'icon' => 'ri-exchange-box-line',
        'items' => [
            ['label' => 'Pembelian', 'icon' => 'ri-shopping-cart-2-line', 'route' => 'purchases.index', 'active' => ['purchases.*']],
            ['label' => 'Penjualan', 'icon' => 'ri-hand-coin-line', 'route' => 'sales.index', 'active' => ['sales.*']],
            ['label' => 'Stok', 'icon' => 'ri-database-2-line', 'route' => 'stock.index', 'active' => ['stock.index']],
        ],
    ],
    [
        'label' => 'Laporan',
        'icon' => 'ri-bar-chart-box-line',
        'items' => [
            ['label' => 'Laporan', 'icon' => 'ri-file-chart-line', 'route' => 'reports.index', 'active' => ['reports.index']],
        ],
    ],
    [
        'label' => 'Halaman publik',
        'icon' => 'ri-global-line',
        'items' => [
            ['label' => 'Beranda situs', 'icon' => 'ri-home-4-line', 'route' => 'public.home', 'active' => ['public.home']],
            ['label' => 'Katalog menu publik', 'icon' => 'ri-book-open-line', 'route' => 'public.menu', 'active' => ['public.menu', 'public.menu.show']],
            ['label' => 'Keranjang publik', 'icon' => 'ri-shopping-cart-line', 'route' => 'public.cart.index', 'active' => ['public.cart.index']],
        ],
    ],
    [
        // Dulu "User Management → Admin & Kasir": sekarang semua akun staf restoran
        // (apa pun perannya) ada di Karyawan, dan perannya diatur di sebelahnya.
        'label' => 'Pengguna',
        'icon' => 'ri-group-line',
        'items' => [
            ['label' => 'Karyawan', 'icon' => 'ri-team-line', 'route' => 'admin-users.index', 'active' => ['admin-users.*']],
            ['label' => 'Peran & hak akses', 'icon' => 'ri-shield-user-line', 'route' => 'settings.roles-permissions', 'active' => ['settings.roles-permissions*']],
            ['label' => 'Akun pelanggan', 'icon' => 'ri-user-smile-line', 'route' => 'customer-users.index', 'active' => ['customer-users.*']],
        ],
    ],
    [
        'label' => 'Sistem',
        'icon' => 'ri-settings-4-line',
        'items' => [
            ['label' => 'Pengaturan aplikasi', 'icon' => 'ri-settings-3-line', 'route' => 'system.settings', 'active' => ['system.settings']],
            ['label' => 'Akun pembayaran', 'icon' => 'ri-bank-card-line', 'route' => 'system.payment-accounts', 'active' => ['system.payment-accounts']],
            ['label' => 'Lisensi & langganan', 'icon' => 'ri-shield-keyhole-line', 'route' => 'system.license', 'active' => ['system.license']],
            $pengaturanNavigasi,
        ],
    ],
];

return [
    'superadmin' => $lengkap,

    'admin' => $lengkap,

    'cashier' => [
        [
            'label' => 'Dashboard',
            'icon' => 'ri-dashboard-line',
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'ri-dashboard-line', 'route' => 'dashboard', 'active' => ['dashboard']],
            ],
        ],
        [
            'label' => 'Kasir',
            'icon' => 'ri-shopping-basket-line',
            'items' => [
                ['label' => 'Kasir (POS)', 'icon' => 'ri-shopping-basket-line', 'route' => 'pos.order.index', 'active' => ['pos.order.*']],
                ['label' => 'Tagihan meja', 'icon' => 'ri-cash-line', 'route' => 'pos.bills', 'active' => ['pos.bills']],
                ['label' => 'Pesanan', 'icon' => 'ri-file-list-3-line', 'route' => 'orders.index', 'active' => ['orders.*'], 'badge' => ['type' => 'dynamic', 'resolver' => 'active_orders']],
                ['label' => 'Pembayaran', 'icon' => 'ri-wallet-3-line', 'route' => 'payments.index', 'active' => ['payments.*']],
                ['label' => 'Sesi meja', 'icon' => 'ri-qr-scan-2-line', 'route' => 'table-sessions.index', 'active' => ['table-sessions.*']],
            ],
        ],
        [
            'label' => 'Layanan tamu',
            'icon' => 'ri-service-line',
            'items' => [$panelMeja, $antreanLagu],
        ],
        [
            'label' => 'Halaman publik',
            'icon' => 'ri-global-line',
            'items' => [
                ['label' => 'Beranda situs', 'icon' => 'ri-home-4-line', 'route' => 'public.home', 'active' => ['public.home']],
                ['label' => 'Katalog menu publik', 'icon' => 'ri-book-open-line', 'route' => 'public.menu', 'active' => ['public.menu', 'public.menu.show']],
                ['label' => 'Keranjang publik', 'icon' => 'ri-shopping-cart-line', 'route' => 'public.cart.index', 'active' => ['public.cart.index']],
            ],
        ],
        [
            'label' => 'Pengaturan',
            'icon' => 'ri-settings-3-line',
            'items' => [$pengaturanNavigasi],
        ],
    ],

    'manager' => [
        [
            'label' => 'Manajer',
            'icon' => 'ri-briefcase-line',
            'items' => [
                ['label' => 'Beranda manajer', 'icon' => 'ri-dashboard-line', 'route' => 'manager.dashboard', 'active' => ['manager.dashboard']],
                ['label' => 'Jadwal shift', 'icon' => 'ri-calendar-schedule-line', 'route' => 'manager.shifts', 'active' => ['manager.shifts']],
                ['label' => 'KPI pegawai', 'icon' => 'ri-trophy-line', 'route' => 'manager.kpi', 'active' => ['manager.kpi']],
                ['label' => 'Pelanggan paling sering datang', 'icon' => 'ri-vip-crown-line', 'route' => 'manager.top-customers', 'active' => ['manager.top-customers']],
                ['label' => 'Kelola reservasi', 'icon' => 'ri-calendar-check-line', 'route' => 'receptionist.bookings', 'active' => ['receptionist.bookings']],
                ['label' => 'Menu paling laku', 'icon' => 'ri-bar-chart-box-line', 'route' => 'receptionist.analytics', 'active' => ['receptionist.analytics']],
            ],
        ],
        [
            'label' => 'Layanan tamu',
            'icon' => 'ri-service-line',
            'items' => [$panelMeja, $antreanLagu],
        ],
        [
            'label' => 'Pengaturan',
            'icon' => 'ri-settings-3-line',
            'items' => [$pengaturanNavigasi],
        ],
    ],

    'receptionist' => [
        [
            'label' => 'Resepsionis',
            'icon' => 'ri-customer-service-2-line',
            'items' => [
                ['label' => 'Beranda', 'icon' => 'ri-dashboard-line', 'route' => 'receptionist.dashboard', 'active' => ['receptionist.dashboard']],
                $layarDapur,
                ['label' => 'Peta meja', 'icon' => 'ri-map-2-line', 'route' => 'receptionist.table-map', 'active' => ['receptionist.table-map']],
                ['label' => 'Kelola reservasi', 'icon' => 'ri-calendar-check-line', 'route' => 'receptionist.bookings', 'active' => ['receptionist.bookings']],
                ['label' => 'Hitung pengunjung', 'icon' => 'ri-group-line', 'route' => 'receptionist.visitors', 'active' => ['receptionist.visitors']],
                ['label' => 'Menu paling laku', 'icon' => 'ri-bar-chart-box-line', 'route' => 'receptionist.analytics', 'active' => ['receptionist.analytics']],
            ],
        ],
        [
            'label' => 'Layanan tamu',
            'icon' => 'ri-service-line',
            'items' => [$panelMeja, $antreanLagu],
        ],
        [
            'label' => 'Pengaturan',
            'icon' => 'ri-settings-3-line',
            'items' => [$pengaturanNavigasi],
        ],
    ],

    'waiter' => [
        [
            'label' => 'Pelayan',
            'icon' => 'ri-walk-line',
            'items' => [
                $panelMeja,
                ['label' => 'Beranda pelayan', 'icon' => 'ri-dashboard-line', 'route' => 'waiter.dashboard', 'active' => ['waiter.dashboard']],
                ['label' => 'Ubah status meja', 'icon' => 'ri-refresh-line', 'route' => 'waiter.tables', 'active' => ['waiter.tables']],
                ['label' => 'Tip & layanan', 'icon' => 'ri-hand-coin-line', 'route' => 'waiter.tips', 'active' => ['waiter.tips']],
                $antreanLagu,
                $pengaturanNavigasi,
            ],
        ],
    ],

    'chef' => [
        [
            'label' => 'Dapur',
            'icon' => 'ri-knife-blood-line',
            'items' => [$layarDapur, $pengaturanNavigasi],
        ],
    ],

    'ob' => [
        [
            'label' => 'Kebersihan',
            'icon' => 'ri-brush-line',
            'items' => [
                ['label' => 'Beranda OB', 'icon' => 'ri-dashboard-line', 'route' => 'ob.dashboard', 'active' => ['ob.dashboard']],
                ['label' => 'Pembersihan meja', 'icon' => 'ri-brush-line', 'route' => 'ob.tables', 'active' => ['ob.tables']],
                $pengaturanNavigasi,
            ],
        ],
    ],

    'customer' => [
        [
            'label' => 'Pelanggan',
            'icon' => 'ri-user-3-line',
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'ri-dashboard-line', 'route' => 'customer.dashboard', 'active' => ['customer.dashboard']],
                ['label' => 'Pilih meja', 'icon' => 'ri-layout-grid-line', 'route' => 'customer.menus.tables', 'active' => ['customer.menus.tables']],
                ['label' => 'Pesan menu', 'icon' => 'ri-bowl-line', 'route' => 'customer.menus.index', 'active' => ['customer.menus.*']],
                ['label' => 'Riwayat pesanan', 'icon' => 'ri-receipt-line', 'route' => 'customer.orders.index', 'active' => ['customer.orders.*']],
                ['label' => 'Booking', 'icon' => 'ri-calendar-check-line', 'route' => 'customer.bookings.create', 'active' => ['customer.bookings.*']],
            ],
        ],
    ],
];
