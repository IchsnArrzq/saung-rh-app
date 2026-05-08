<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Component;

new class extends Component {
    public string $dashboardUrl = '#';
    public string $menuUrl = '#';
    public string $categoryUrl = '#';
    public string $tableStatusUrl = '#';
    public string $tableCategoryUrl = '#';
    public string $tableUrl = '#';
    public string $orderUrl = '#';
    public string $paymentUrl = '#';
    public string $reservationUrl = '#';
    public string $dailyReportUrl = '#';
    public string $monthlyReportUrl = '#';

    public bool $masterOpen = false;
    public bool $transactionOpen = false;
    public bool $reportOpen = false;

    public function mount(): void
    {
        $this->dashboardUrl = Route::has('dashboard') ? route('dashboard') : '#';
        $this->menuUrl = Route::has('menus.index') ? route('menus.index') : '#';
        $this->categoryUrl = Route::has('menu-categories.index') ? route('menu-categories.index') : '#';
        $this->tableStatusUrl = Route::has('table-statuses.index') ? route('table-statuses.index') : '#';
        $this->tableCategoryUrl = Route::has('table-categories.index') ? route('table-categories.index') : '#';
        $this->tableUrl = Route::has('tables.index') ? route('tables.index') : '#';
        $this->orderUrl = Route::has('orders.index') ? route('orders.index') : '#';
        $this->paymentUrl = Route::has('payments.index') ? route('payments.index') : '#';
        $this->reservationUrl = Route::has('reservations.index') ? route('reservations.index') : '#';
        $this->dailyReportUrl = Route::has('reports.daily') ? route('reports.daily') : '#';
        $this->monthlyReportUrl = Route::has('reports.monthly') ? route('reports.monthly') : '#';

        $this->masterOpen = request()->routeIs('menus.*')
            || request()->routeIs('menu-categories.*')
            || request()->routeIs('table-statuses.*')
            || request()->routeIs('table-categories.*')
            || request()->routeIs('tables.*');

        $this->transactionOpen = request()->routeIs('orders.*')
            || request()->routeIs('payments.*')
            || request()->routeIs('reservations.*');

        $this->reportOpen = request()->routeIs('reports.*');
    }
}; ?>
<div class="drawer-side h-[calc(100vh-0.1rem)]">
    <label for="admin-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
    <aside
        class="flex min-h-full max-w-[85vw] flex-col overflow-y-auto overflow-x-auto border-r border-base-300 bg-base-200 py-5 transition-all duration-1000 is-drawer-close:w-16 is-drawer-open:w-72">
        <a href="{{ $dashboardUrl }}" class="inline-flex items-center gap-3 p-2">
            <img src="{{ asset('assets/logo-cr-mark.png') }}" alt="CR Cafe & Resto logo mark"
                class="h-11 w-11 shrink-0 rounded-xl border border-base-300 bg-base-100 p-1 object-contain transition-all duration-300 is-drawer-open:h-14 is-drawer-open:w-14">
            <span
                class="overflow-hidden transition-all duration-300 is-drawer-close:max-w-0 is-drawer-close:opacity-0 is-drawer-open:max-w-xs is-drawer-open:opacity-100">
                <span class="block text-xs font-semibold uppercase tracking-[0.2em] text-secondary whitespace-nowrap">
                    CR Cafe & Resto
                </span>
                <span
                    class="block text-2xl font-semibold text-primary whitespace-nowrap"
                    style="font-family: 'Playfair Display', serif;">Admin Panel</span>
            </span>
        </a>
        <div class="divider"></div>
        <nav class="grow">
            <ul class="menu w-full gap-1 rounded-2xl bg-base-100 p-2">
                <li>
                    <a href="{{ $dashboardUrl }}" data-tip="Dashboard"
                        class="{{ request()->routeIs('dashboard') ? 'bg-primary text-primary-content hover:bg-neutral' : 'text-stone-700 hover:bg-base-300' }}">
                        <i class="ri-dashboard-line text-lg"></i><span class="is-drawer-close:hidden">Dashboard</span>
                    </a>
                </li>
                <li>
                    <details @if ($masterOpen) open @endif>
                        <summary data-tip="Master Data"
                            class="is-drawer-close:tooltip {{ $masterOpen ? 'bg-base-300 text-primary font-semibold' : 'text-stone-700 hover:bg-base-300' }}">
                            <i class="ri-restaurant-line text-lg"></i>
                            <span class="is-drawer-close:hidden">Master Data</span>
                        </summary>
                        <ul class="ms-2 border-l border-base-300 is-drawer-close:hidden">
                            <li>
                                <a href="{{ $menuUrl }}"
                                    class="{{ request()->routeIs('menus.*') ? 'text-primary font-semibold' : 'text-stone-700' }}">
                                    <i class="ri-bowl-line"></i>
                                    Menu
                                </a>
                            </li>
                            <li>
                                <a href="{{ $categoryUrl }}"
                                    class="{{ request()->routeIs('menu-categories.*') ? 'text-primary font-semibold' : 'text-stone-700' }}">
                                    <i class="ri-price-tag-3-line"></i>
                                    Menu Categories
                                </a>
                            </li>
                            <li>
                                <a href="{{ $tableCategoryUrl }}"
                                    class="{{ request()->routeIs('table-categories.*') ? 'text-primary font-semibold' : 'text-stone-700' }}">
                                    <i class="ri-layout-2-line"></i>
                                    Table Categories
                                </a>
                            </li>
                            <li>
                                <a href="{{ $tableStatusUrl }}"
                                    class="{{ request()->routeIs('table-statuses.*') ? 'text-primary font-semibold' : 'text-stone-700' }}">
                                    <i class="ri-flag-line"></i>
                                    Table Statuses
                                </a>
                            </li>
                            <li>
                                <a href="{{ $tableUrl }}"
                                    class="{{ request()->routeIs('tables.*') ? 'text-primary font-semibold' : 'text-stone-700' }}">
                                    <i class="ri-layout-grid-line"></i>
                                    Tables
                                </a>
                            </li>
                        </ul>
                    </details>
                </li>

                <li>
                    <details @if ($transactionOpen) open @endif>
                        <summary data-tip="Transactions"
                            class="is-drawer-close:tooltip {{ $transactionOpen ? 'bg-base-300 text-primary font-semibold' : 'text-stone-700 hover:bg-base-300' }}">
                            <i class="ri-shopping-bag-3-line text-lg"></i>
                            <span class="is-drawer-close:hidden">Transactions</span>
                        </summary>
                        <ul class="ms-2 border-l border-base-300 is-drawer-close:hidden">
                            <li>
                                <a href="{{ $orderUrl }}"
                                    class="{{ request()->routeIs('orders.*') ? 'text-primary font-semibold' : 'text-stone-700' }}">
                                    <i class="ri-file-list-3-line"></i>
                                    Orders
                                </a>
                            </li>
                            <li>
                                <a href="{{ $paymentUrl }}"
                                    class="{{ request()->routeIs('payments.*') ? 'text-primary font-semibold' : 'text-stone-700' }}">
                                    <i class="ri-wallet-3-line"></i>
                                    Payments
                                </a>
                            </li>
                            <li>
                                <a href="{{ $reservationUrl }}"
                                    class="{{ request()->routeIs('reservations.*') ? 'text-primary font-semibold' : 'text-stone-700' }}">
                                    <i class="ri-calendar-check-line"></i>
                                    Reservations
                                </a>
                            </li>
                        </ul>
                    </details>
                </li>

                <li>
                    <details @if ($reportOpen) open @endif>
                        <summary data-tip="Reports"
                            class="is-drawer-close:tooltip {{ $reportOpen ? 'bg-base-300 text-primary font-semibold' : 'text-stone-700 hover:bg-base-300' }}">
                            <i class="ri-bar-chart-box-line text-lg"></i>
                            <span class="is-drawer-close:hidden">Reports</span>
                        </summary>
                        <ul class="ms-2 border-l border-base-300 is-drawer-close:hidden">
                            <li>
                                <a href="{{ $dailyReportUrl }}">
                                    <i class="ri-calendar-check-line"></i>
                                    Daily Report
                                </a>
                            </li>
                            <li>
                                <a href="{{ $monthlyReportUrl }}">
                                    <i class="ri-calendar-2-line"></i>
                                    Monthly Report
                                </a>
                            </li>
                        </ul>
                    </details>
                </li>
            </ul>
        </nav>
    </aside>
</div>
