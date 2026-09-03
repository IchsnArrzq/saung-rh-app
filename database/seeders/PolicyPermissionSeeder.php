<?php

namespace Database\Seeders;

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
use App\Support\PolicyPermissions;
use Illuminate\Database\Seeder;

/**
 * Mints the "{model}.{ability}" permissions the Policies check, and hands each
 * role its starting set.
 *
 * These are *defaults*, not the source of truth: the Roles & Permissions screen
 * (RolePermissionController) owns the matrix from here on, and a re-run only
 * adds back what it grants — it never revokes what an admin changed there.
 */
class PolicyPermissionSeeder extends Seeder
{
    /**
     * Ability bundles, named after what a role is actually allowed to do.
     *
     * `restore` and `forceDelete` are deliberately absent from every bundle
     * below: permanently destroying records stays with superadmin, who passes
     * through Gate::before without needing the permission at all.
     */
    private const ABILITIES = [
        'view' => ['viewAny', 'view'],
        'edit' => ['viewAny', 'view', 'update'],
        'book' => ['viewAny', 'view', 'create'],
        'manage' => ['viewAny', 'view', 'create', 'update', 'delete'],
    ];

    /**
     * Role → bundle → models.
     *
     * Mirrors the feature permissions in PermissionSeeder ('menus.manage',
     * 'kitchen.view', …) so switching a screen over to policy checks does not
     * silently widen or narrow what a role could already reach. Superadmin is
     * absent on purpose — it is granted everything below.
     */
    private const MATRIX = [
        'admin' => [
            'manage' => [
                Menu::class, MenuCategory::class, MenuIngredient::class, Ingredient::class,
                Media::class, Table::class, TableCategory::class, Order::class,
                OrderItem::class, OrderNote::class, Payment::class, PaymentAccount::class,
                Reservation::class, ReservationItem::class, Purchase::class, PurchaseItem::class,
                Sale::class, SaleItem::class, StockOpname::class, StockOpnameItem::class,
                Supplier::class, Customer::class, User::class, Shift::class,
            ],
            'view' => [
                OrderStatusLog::class, StockMovement::class, TableSession::class, VisitorLog::class,
                Tip::class, ServiceLog::class, SongRequest::class, SpecialRequest::class,
                Role::class, Permission::class, AppSetting::class, Subscription::class,
            ],
        ],

        'manager' => [
            'manage' => [Reservation::class, ReservationItem::class, Shift::class],
            'view' => [
                Order::class, OrderItem::class, Payment::class, Sale::class, Purchase::class,
                Customer::class, Tip::class, ServiceLog::class, Table::class, TableSession::class,
                VisitorLog::class, Menu::class, User::class,
            ],
        ],

        'receptionist' => [
            'manage' => [Reservation::class, ReservationItem::class, Table::class, TableCategory::class],
            'edit' => [Order::class],
            'view' => [OrderItem::class, TableSession::class, VisitorLog::class, Menu::class, Customer::class],
        ],

        'cashier' => [
            'manage' => [Order::class, OrderItem::class, OrderNote::class, Payment::class],
            'view' => [
                Menu::class, MenuCategory::class, Table::class, Customer::class,
                PaymentAccount::class, OrderStatusLog::class,
            ],
        ],

        'waiter' => [
            'manage' => [Tip::class, ServiceLog::class, SpecialRequest::class, SongRequest::class],
            'edit' => [Table::class, Order::class],
            'view' => [OrderItem::class, Menu::class, TableSession::class],
        ],

        'chef' => [
            'edit' => [Order::class, OrderItem::class],
            'view' => [Menu::class, MenuIngredient::class, Ingredient::class],
        ],

        'ob' => [
            'edit' => [Table::class],
            'view' => [TableSession::class],
        ],

        'customer' => [
            'book' => [Reservation::class],
            'view' => [Menu::class, MenuCategory::class],
        ],
    ];

    public function run(): void
    {
        $all = PolicyPermissions::names();

        foreach ($all as $name) {
            Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $this->grant('superadmin', $all);

        foreach (self::MATRIX as $roleName => $bundles) {
            $this->grant($roleName, $this->permissionsFor($bundles));
        }
    }

    /**
     * Flatten one role's bundle map into permission names.
     *
     * @param  array<string, array<int, class-string>>  $bundles
     * @return array<int, string>
     */
    private function permissionsFor(array $bundles): array
    {
        $names = [];

        foreach ($bundles as $bundle => $models) {
            foreach ($models as $model) {
                $slug = PolicyPermissions::modelSlug($model);

                foreach (self::ABILITIES[$bundle] as $ability) {
                    $names[] = "{$slug}.{$ability}";
                }
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Additive on purpose: PermissionSeeder has already synced the feature-level
     * permissions onto this role, and syncing here would wipe them.
     *
     * @param  array<int, string>  $names
     */
    private function grant(string $roleName, array $names): void
    {
        $role = Role::query()->where('name', $roleName)->first();

        if ($role) {
            $role->givePermissionTo($names);
        }
    }
}
