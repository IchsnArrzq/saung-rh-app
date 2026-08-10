<?php

namespace App\Providers;

use App\Domains\Customer\Repositories\CustomerRepository;
use App\Domains\Customer\Repositories\CustomerRepositoryInterface;
use App\Domains\Employee\Repositories\ShiftRepository;
use App\Domains\Employee\Repositories\ShiftRepositoryInterface;
use App\Domains\Employee\Repositories\StaffActivityRepository;
use App\Domains\Employee\Repositories\StaffActivityRepositoryInterface;
use App\Domains\Inventory\Repositories\IngredientRepository;
use App\Domains\Inventory\Repositories\IngredientRepositoryInterface;
use App\Domains\Menu\Repositories\MenuRepository;
use App\Domains\Menu\Repositories\MenuRepositoryInterface;
use App\Domains\Order\Events\OrderPlaced;
use App\Domains\Order\Events\TableBillsCleared;
use App\Domains\Order\Repositories\OrderRepository;
use App\Domains\Order\Repositories\OrderRepositoryInterface;
use App\Domains\Payment\Repositories\PaymentRepository;
use App\Domains\Payment\Repositories\PaymentRepositoryInterface;
use App\Domains\Reservation\Repositories\ReservationRepository;
use App\Domains\Reservation\Repositories\ReservationRepositoryInterface;
use App\Domains\Social\Repositories\SongRequestRepository;
use App\Domains\Social\Repositories\SongRequestRepositoryInterface;
use App\Domains\Social\Repositories\SpecialRequestRepository;
use App\Domains\Social\Repositories\SpecialRequestRepositoryInterface;
use App\Domains\System\Repositories\AppSettingRepository;
use App\Domains\System\Repositories\AppSettingRepositoryInterface;
use App\Domains\System\Repositories\SubscriptionRepository;
use App\Domains\System\Repositories\SubscriptionRepositoryInterface;
use App\Domains\System\Repositories\UserRepository;
use App\Domains\System\Repositories\UserRepositoryInterface;
use App\Domains\Table\Listeners\ClaimTableOnOrderPlaced;
use App\Domains\Table\Listeners\ReleaseTableOnBillsCleared;
use App\Domains\Table\Repositories\TableRepository;
use App\Domains\Table\Repositories\TableRepositoryInterface;
use App\Models\Payment;
use App\Observers\PaymentObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(TableRepositoryInterface::class, TableRepository::class);
        $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(ReservationRepositoryInterface::class, ReservationRepository::class);
        $this->app->bind(IngredientRepositoryInterface::class, IngredientRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(ShiftRepositoryInterface::class, ShiftRepository::class);
        $this->app->bind(StaffActivityRepositoryInterface::class, StaffActivityRepository::class);
        $this->app->bind(SongRequestRepositoryInterface::class, SongRequestRepository::class);
        $this->app->bind(SpecialRequestRepositoryInterface::class, SpecialRequestRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AppSettingRepositoryInterface::class, AppSettingRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Payment::observe(PaymentObserver::class);
        $this->registerDomainListeners();

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Every cross-domain write reaction, in one readable list.
     *
     * Laravel only auto-discovers listeners under app/Listeners, and these live
     * beside the domain that owns the reaction — but the explicit list is worth
     * having anyway: it is the map of which domain reacts to which, and it
     * fails loudly if a listener is renamed (ARCHITECTURE.md § Domain
     * Dependencies).
     */
    private function registerDomainListeners(): void
    {
        Event::listen(OrderPlaced::class, ClaimTableOnOrderPlaced::class);
        Event::listen(TableBillsCleared::class, ReleaseTableOnBillsCleared::class);
    }
}
