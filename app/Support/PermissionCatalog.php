<?php

namespace App\Support;

use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\Ingredient;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuIngredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\Permission;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ServiceLog;
use App\Models\Shift;
use App\Models\SongRequest;
use App\Models\SpecialRequest;
use App\Models\SpecialRequestCategory;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Subscription;
use App\Models\Supplier;
use App\Models\Table;
use App\Models\TableCategory;
use App\Models\TableSession;
use App\Models\Tip;
use App\Models\User;
use App\Models\VisitorLog;

/**
 * Apa yang ditampilkan dan dikelola layar Peran & hak akses: permission fitur
 * (FeaturePermissions) plus matriks model × kemampuan dari PolicyPermissions,
 * dengan label Indonesia dan dikelompokkan per area kerja.
 *
 * `restore` dan `forceDelete` sengaja tidak ditampilkan: menghapus permanen tetap
 * milik superadmin (Gate::before), dan permission itu dipertahankan apa adanya
 * saat sebuah peran disimpan.
 */
class PermissionCatalog
{
    /**
     * @var array<string, string>
     */
    public const ABILITIES = [
        'viewAny' => 'Lihat daftar',
        'view' => 'Lihat detail',
        'create' => 'Tambah',
        'update' => 'Ubah',
        'delete' => 'Hapus',
    ];

    /**
     * @var array<class-string, string>
     */
    private const LABELS = [
        AppSetting::class => 'Pengaturan aplikasi',
        Customer::class => 'Pelanggan',
        Ingredient::class => 'Bahan makanan',
        Media::class => 'Foto & video menu',
        Menu::class => 'Menu',
        MenuCategory::class => 'Kategori menu',
        MenuIngredient::class => 'Resep menu',
        Order::class => 'Pesanan',
        OrderItem::class => 'Item pesanan',
        OrderNote::class => 'Catatan pesanan',
        OrderStatusLog::class => 'Riwayat status pesanan',
        Payment::class => 'Pembayaran',
        PaymentAccount::class => 'Rekening pembayaran',
        Permission::class => 'Hak akses',
        Purchase::class => 'Pembelian',
        PurchaseItem::class => 'Item pembelian',
        Reservation::class => 'Reservasi',
        ReservationItem::class => 'Item reservasi',
        Role::class => 'Peran',
        Sale::class => 'Penjualan',
        SaleItem::class => 'Item penjualan',
        ServiceLog::class => 'Catatan layanan',
        Shift::class => 'Shift staf',
        SongRequest::class => 'Permintaan lagu',
        SpecialRequest::class => 'Permintaan khusus',
        SpecialRequestCategory::class => 'Kategori permintaan',
        StockMovement::class => 'Riwayat stok',
        StockOpname::class => 'Stock opname',
        StockOpnameItem::class => 'Item stock opname',
        Subscription::class => 'Lisensi',
        Supplier::class => 'Supplier',
        Table::class => 'Meja',
        TableCategory::class => 'Kategori meja',
        TableSession::class => 'Sesi meja (QR)',
        Tip::class => 'Tip',
        User::class => 'Akun pengguna',
        VisitorLog::class => 'Catatan pengunjung',
    ];

    /**
     * @var array<string, array<int, class-string>>
     */
    private const GROUPS = [
        'Menu & dapur' => [Menu::class, MenuCategory::class, MenuIngredient::class, Media::class, Ingredient::class],
        'Meja & layanan tamu' => [
            Table::class, TableCategory::class, TableSession::class, SpecialRequest::class,
            SpecialRequestCategory::class, SongRequest::class, VisitorLog::class,
        ],
        'Pesanan & pembayaran' => [
            Order::class, OrderItem::class, OrderNote::class, OrderStatusLog::class,
            Payment::class, PaymentAccount::class,
        ],
        'Reservasi & pelanggan' => [Reservation::class, ReservationItem::class, Customer::class],
        'Inventori & jual-beli' => [
            Purchase::class, PurchaseItem::class, Sale::class, SaleItem::class,
            StockMovement::class, StockOpname::class, StockOpnameItem::class, Supplier::class,
        ],
        'Staf' => [Shift::class, Tip::class, ServiceLog::class],
        'Pengguna & sistem' => [User::class, Role::class, Permission::class, AppSetting::class, Subscription::class],
    ];

    /**
     * @return array<int, array{label: string, rows: array<int, array{slug: string, label: string, permissions: array<string, string>}>}>
     */
    public static function modelGroups(): array
    {
        $groups = [];
        $placed = [];

        foreach (self::GROUPS as $label => $models) {
            $rows = [];

            foreach ($models as $model) {
                if (in_array($model, PolicyPermissions::$models, true)) {
                    $rows[] = self::row($model);
                    $placed[] = $model;
                }
            }

            if ($rows !== []) {
                $groups[] = ['label' => $label, 'rows' => $rows];
            }
        }

        // Model yang baru ditambahkan ke PolicyPermissions tapi belum dikelompokkan
        // tetap muncul, bukan diam-diam hilang dari layar.
        $rest = array_values(array_diff(PolicyPermissions::$models, $placed));

        if ($rest !== []) {
            $groups[] = ['label' => 'Lainnya', 'rows' => array_map(fn (string $model) => self::row($model), $rest)];
        }

        return $groups;
    }

    /**
     * Semua permission yang dikelola layar ini: fitur + model tanpa restore/forceDelete.
     *
     * @return array<int, string>
     */
    public static function managedNames(): array
    {
        $names = FeaturePermissions::names();

        foreach (PolicyPermissions::$models as $model) {
            $slug = PolicyPermissions::modelSlug($model);

            foreach (array_keys(self::ABILITIES) as $ability) {
                $names[] = "{$slug}.{$ability}";
            }
        }

        return $names;
    }

    /**
     * @return array{slug: string, label: string, permissions: array<string, string>}
     */
    private static function row(string $model): array
    {
        $slug = PolicyPermissions::modelSlug($model);
        $permissions = [];

        foreach (array_keys(self::ABILITIES) as $ability) {
            $permissions[$ability] = "{$slug}.{$ability}";
        }

        return [
            'slug' => $slug,
            'label' => self::LABELS[$model] ?? PolicyPermissions::modelLabel($model),
            'permissions' => $permissions,
        ];
    }
}
