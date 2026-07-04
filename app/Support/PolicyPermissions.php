<?php

namespace App\Support;

use App\Models\AppSetting;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuIngredient;
use App\Models\MenuStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\Permission;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Role;
use App\Models\ServiceLog;
use App\Models\Shift;
use App\Models\SongRequest;
use App\Models\SpecialRequest;
use App\Models\StockOpname;
use App\Models\Subscription;
use App\Models\Table;
use App\Models\TableCategory;
use App\Models\TableSession;
use App\Models\TableStatus;
use App\Models\Tip;
use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Support\Str;

/**
 * Central list of models and Laravel's default policy ability names used to
 * generate/display the "{model}.{ability}" permissions managed from the
 * Roles & Permissions screen. Shared by PolicyPermissionSeeder and
 * RolePermissionController so both stay in sync.
 */
class PolicyPermissions
{
    public static array $models = [
        AppSetting::class,
        Ingredient::class,
        Menu::class,
        MenuCategory::class,
        MenuIngredient::class,
        MenuStatus::class,
        Order::class,
        OrderItem::class,
        OrderNote::class,
        OrderStatusLog::class,
        Payment::class,
        PaymentAccount::class,
        Permission::class,
        Reservation::class,
        ReservationItem::class,
        Role::class,
        ServiceLog::class,
        Shift::class,
        SongRequest::class,
        SpecialRequest::class,
        StockOpname::class,
        Subscription::class,
        Table::class,
        TableCategory::class,
        TableSession::class,
        TableStatus::class,
        Tip::class,
        User::class,
        VisitorLog::class,
    ];

    public static array $abilities = [
        'viewAny',
        'view',
        'create',
        'update',
        'delete',
        'restore',
        'forceDelete',
    ];

    public static function modelSlug(string $model): string
    {
        return (string) Str::of(class_basename($model))->snake();
    }

    public static function modelLabel(string $model): string
    {
        return trim((string) preg_replace('/(?<!^)[A-Z]/', ' $0', class_basename($model)));
    }

    /**
     * @return array<int, array{slug: string, label: string, abilities: array<int, string>}>
     */
    public static function groups(): array
    {
        return array_map(fn (string $model) => [
            'slug' => static::modelSlug($model),
            'label' => static::modelLabel($model),
            'abilities' => static::$abilities,
        ], static::$models);
    }

    /**
     * @return array<int, string>
     */
    public static function names(): array
    {
        $names = [];

        foreach (static::$models as $model) {
            $slug = static::modelSlug($model);

            foreach (static::$abilities as $ability) {
                $names[] = "{$slug}.{$ability}";
            }
        }

        return $names;
    }
}
