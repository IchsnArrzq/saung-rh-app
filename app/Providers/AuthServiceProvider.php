<?php

namespace App\Providers;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Role;
use App\Models\Table;
use App\Models\TableCategory;
use App\Models\TableStatus;
use App\Models\User;
use App\Policies\AllowAllPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Menu::class => AllowAllPolicy::class,
        MenuCategory::class => AllowAllPolicy::class,
        Order::class => AllowAllPolicy::class,
        OrderItem::class => AllowAllPolicy::class,
        Payment::class => AllowAllPolicy::class,
        Permission::class => AllowAllPolicy::class,
        Reservation::class => AllowAllPolicy::class,
        ReservationItem::class => AllowAllPolicy::class,
        Role::class => AllowAllPolicy::class,
        Table::class => AllowAllPolicy::class,
        TableCategory::class => AllowAllPolicy::class,
        TableStatus::class => AllowAllPolicy::class,
        User::class => AllowAllPolicy::class,
    ];
}

