<?php

namespace App\Support;

/**
 * Permission fitur — yang menjaga halaman atau portal, bukan satu model.
 * Namanya dipakai langsung di rute (`can:waiter.operate`, `role_or_permission:…`)
 * dan dibuat oleh PermissionSeeder.
 *
 * Hanya yang benar-benar menjaga sesuatu yang didaftar di sini. Permission lama
 * yang tidak dipakai rute mana pun (menus.manage, users.manage, pos.manage, …)
 * tidak ditampilkan di layar Peran & hak akses dan tidak disentuh saat disimpan.
 */
class FeaturePermissions
{
    /**
     * @var array<string, array{label: string, description: string}>
     */
    public const ALL = [
        'backoffice.access' => [
            'label' => 'Masuk area back-office',
            'description' => 'Syarat membuka halaman di bawah /admin (menu, meja, pesanan, laporan). Tiap halaman tetap mengikuti hak akses datanya.',
        ],
        'dashboard.view' => [
            'label' => 'Dashboard admin',
            'description' => 'Ringkasan penjualan dan pesanan hari ini.',
        ],
        'orders.manage' => [
            'label' => 'Kasir (POS) dan tagihan meja',
            'description' => 'Membuat pesanan di kasir dan melunasi tagihan meja.',
        ],
        'kitchen.view' => [
            'label' => 'Layar dapur (KDS)',
            'description' => 'Antrean masakan dari dapur secara langsung.',
        ],
        'reports.view' => [
            'label' => 'Laporan',
            'description' => 'Laporan penjualan dan operasional.',
        ],
        'manager.dashboard' => [
            'label' => 'Portal manajer',
            'description' => 'Jadwal shift, KPI pegawai, dan pelanggan paling sering datang.',
        ],
        'receptionist.monitor' => [
            'label' => 'Portal resepsionis',
            'description' => 'Peta meja, hitung pengunjung, dan menu paling laku.',
        ],
        'reservations.manage' => [
            'label' => 'Kelola reservasi di portal resepsionis',
            'description' => 'Menerima, mengubah status, dan mencatat DP reservasi.',
        ],
        'waiter.operate' => [
            'label' => 'Portal pelayan',
            'description' => 'Beranda pelayan serta catatan tip dan layanan.',
        ],
        'tables.status.update' => [
            'label' => 'Ubah status meja',
            'description' => 'Menandai meja terisi, kosong, atau perlu dibersihkan.',
        ],
        'table_chat.moderate' => [
            'label' => 'Balas dan bersihkan obrolan meja',
            'description' => 'Dari Panel meja: membalas tamu dan menghapus obrolan meja.',
        ],
    ];

    /**
     * @return array<int, string>
     */
    public static function names(): array
    {
        return array_keys(self::ALL);
    }
}
