<?php

namespace App\Providers;

use App\Domains\Order\Events\OrderPlaced;
use App\Domains\Order\Events\TableBillsCleared;
use App\Domains\Table\Listeners\ClaimTableOnOrderPlaced;
use App\Domains\Table\Listeners\ReleaseTableOnBillsCleared;
use App\Models\Payment;
use App\Observers\PaymentObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * Repositories are plain concrete classes with one implementation each, so
     * the container resolves them by type-hint without a binding here.
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
