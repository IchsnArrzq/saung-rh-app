<?php

namespace App\Providers;

use App\Services\Admin\DashboardServiceImplement;
use App\Services\Admin\DashboardServiceInterface;
use App\Services\Admin\InventoryServiceImplement;
use App\Services\Admin\InventoryServiceInterface;
use App\Services\Admin\MediaServiceImplement;
use App\Services\Admin\MediaServiceInterface;
use App\Services\Admin\PurchaseServiceImplement;
use App\Services\Admin\PurchaseServiceInterface;
use App\Services\Admin\SaleServiceImplement;
use App\Services\Admin\SaleServiceInterface;
use App\Services\Admin\StockOpnameServiceImplement;
use App\Services\Admin\StockOpnameServiceInterface;
use App\Services\Admin\MenuCategoryServiceImplement;
use App\Services\Admin\MenuCategoryServiceInterface;
use App\Services\Admin\MenuServiceImplement;
use App\Services\Admin\MenuServiceInterface;
use App\Services\Admin\OrderServiceImplement;
use App\Services\Admin\OrderServiceInterface;
use App\Services\Admin\PaymentServiceImplement;
use App\Services\Admin\PaymentServiceInterface;
use App\Services\Admin\ReportServiceImplement;
use App\Services\Admin\ReportServiceInterface;
use App\Services\Admin\ReservationServiceImplement;
use App\Services\Admin\ReservationServiceInterface;
use App\Services\Admin\TableCategoryServiceImplement;
use App\Services\Admin\TableCategoryServiceInterface;
use App\Services\Admin\TableServiceImplement;
use App\Services\Admin\TableServiceInterface;
use App\Services\Admin\TableStatusServiceImplement;
use App\Services\Admin\TableStatusServiceInterface;
use App\Services\Chat\ChatServiceImplement;
use App\Services\Chat\ChatServiceInterface;
use App\Services\Customer\BookingServiceImplement;
use App\Services\Customer\BookingServiceInterface;
use App\Services\Customer\CheckInServiceImplement;
use App\Services\Customer\CheckInServiceInterface;
use App\Services\Customer\DashboardServiceImplement as CustomerDashboardServiceImplement;
use App\Services\Customer\DashboardServiceInterface as CustomerDashboardServiceInterface;
use App\Services\Customer\MenuCatalogServiceImplement;
use App\Services\Customer\MenuCatalogServiceInterface;
use App\Services\Customer\OrderCartServiceImplement;
use App\Services\Customer\OrderCartServiceInterface;
use App\Services\Landing\PublicCartServiceImplement;
use App\Services\Landing\PublicCartServiceInterface;
use App\Services\Landing\PublicHomeServiceImplement;
use App\Services\Landing\PublicHomeServiceInterface;
use App\Services\Manager\ManagerAnalyticsServiceImplement;
use App\Services\Manager\ManagerAnalyticsServiceInterface;
use App\Services\Manager\ShiftServiceImplement;
use App\Services\Manager\ShiftServiceInterface;
use App\Services\Pos\BillingServiceImplement;
use App\Services\Pos\BillingServiceInterface;
use App\Services\Reservations\ReservationDepositServiceImplement;
use App\Services\Reservations\ReservationDepositServiceInterface;
use App\Services\Reservations\ReservationReleaseServiceImplement;
use App\Services\Reservations\ReservationReleaseServiceInterface;
use App\Services\Settings\AppSettingsImplement;
use App\Services\Settings\AppSettingsInterface;
use App\Services\Settings\LicenseServiceImplement;
use App\Services\Settings\LicenseServiceInterface;
use App\Services\Songs\SongRequestServiceImplement;
use App\Services\Songs\SongRequestServiceInterface;
use App\Services\SpecialRequests\SpecialRequestServiceImplement;
use App\Services\SpecialRequests\SpecialRequestServiceInterface;
use App\Services\Tables\TableTurnoverServiceImplement;
use App\Services\Tables\TableTurnoverServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Binds every domain service interface to its concrete implementation. Kept
 * separate from AppServiceProvider so the interface/implementation contract
 * list stays easy to scan as new services are added.
 */
class ServiceBindingsProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DashboardServiceInterface::class, DashboardServiceImplement::class);
        $this->app->bind(InventoryServiceInterface::class, InventoryServiceImplement::class);
        $this->app->bind(MediaServiceInterface::class, MediaServiceImplement::class);
        $this->app->bind(StockOpnameServiceInterface::class, StockOpnameServiceImplement::class);
        $this->app->bind(PurchaseServiceInterface::class, PurchaseServiceImplement::class);
        $this->app->bind(SaleServiceInterface::class, SaleServiceImplement::class);
        $this->app->bind(MenuCategoryServiceInterface::class, MenuCategoryServiceImplement::class);
        $this->app->bind(MenuServiceInterface::class, MenuServiceImplement::class);
        $this->app->bind(OrderServiceInterface::class, OrderServiceImplement::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentServiceImplement::class);
        $this->app->bind(ReportServiceInterface::class, ReportServiceImplement::class);
        $this->app->bind(ReservationServiceInterface::class, ReservationServiceImplement::class);
        $this->app->bind(TableCategoryServiceInterface::class, TableCategoryServiceImplement::class);
        $this->app->bind(TableServiceInterface::class, TableServiceImplement::class);
        $this->app->bind(TableStatusServiceInterface::class, TableStatusServiceImplement::class);

        $this->app->bind(ChatServiceInterface::class, ChatServiceImplement::class);

        $this->app->bind(BookingServiceInterface::class, BookingServiceImplement::class);
        $this->app->bind(CheckInServiceInterface::class, CheckInServiceImplement::class);
        $this->app->bind(CustomerDashboardServiceInterface::class, CustomerDashboardServiceImplement::class);
        $this->app->bind(MenuCatalogServiceInterface::class, MenuCatalogServiceImplement::class);
        $this->app->bind(OrderCartServiceInterface::class, OrderCartServiceImplement::class);

        $this->app->bind(PublicCartServiceInterface::class, PublicCartServiceImplement::class);
        $this->app->bind(PublicHomeServiceInterface::class, PublicHomeServiceImplement::class);

        $this->app->bind(ManagerAnalyticsServiceInterface::class, ManagerAnalyticsServiceImplement::class);
        $this->app->bind(ShiftServiceInterface::class, ShiftServiceImplement::class);

        $this->app->bind(BillingServiceInterface::class, BillingServiceImplement::class);

        $this->app->bind(ReservationDepositServiceInterface::class, ReservationDepositServiceImplement::class);
        $this->app->bind(ReservationReleaseServiceInterface::class, ReservationReleaseServiceImplement::class);

        $this->app->bind(AppSettingsInterface::class, AppSettingsImplement::class);
        $this->app->bind(LicenseServiceInterface::class, LicenseServiceImplement::class);

        $this->app->bind(SongRequestServiceInterface::class, SongRequestServiceImplement::class);
        $this->app->bind(SpecialRequestServiceInterface::class, SpecialRequestServiceImplement::class);

        $this->app->bind(TableTurnoverServiceInterface::class, TableTurnoverServiceImplement::class);
    }
}
