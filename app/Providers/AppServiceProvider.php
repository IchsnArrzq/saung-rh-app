<?php

namespace App\Providers;

use App\Domains\Menu\Repositories\MenuRepository;
use App\Domains\Menu\Repositories\MenuRepositoryInterface;
use App\Domains\Order\Repositories\OrderRepository;
use App\Domains\Order\Repositories\OrderRepositoryInterface;
use App\Domains\Payment\Repositories\PaymentRepository;
use App\Domains\Payment\Repositories\PaymentRepositoryInterface;
use App\Domains\Table\Repositories\TableRepository;
use App\Domains\Table\Repositories\TableRepositoryInterface;
use App\Models\Payment;
use App\Observers\PaymentObserver;
use App\Repositories\Admin\DashboardRepository;
use App\Repositories\Admin\DashboardRepositoryInterface;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(TableRepositoryInterface::class, TableRepository::class);
        $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Payment::observe(PaymentObserver::class);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
