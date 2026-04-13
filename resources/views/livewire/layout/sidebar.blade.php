<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Component;

new class extends Component {
    public string $dashboardUrl = '#';
    public string $menuUrl = '#';
    public string $categoryUrl = '#';
    public string $tableUrl = '#';
    public string $orderUrl = '#';
    public string $paymentUrl = '#';
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
        $this->tableUrl = Route::has('tables.index') ? route('tables.index') : '#';
        $this->orderUrl = Route::has('orders.index') ? route('orders.index') : '#';
        $this->paymentUrl = Route::has('payments.index') ? route('payments.index') : '#';
        $this->dailyReportUrl = Route::has('reports.daily') ? route('reports.daily') : '#';
        $this->monthlyReportUrl = Route::has('reports.monthly') ? route('reports.monthly') : '#';

        $this->masterOpen = request()->routeIs('menus.*') || request()->routeIs('menu-categories.*') || request()->routeIs('tables.*');
        $this->transactionOpen = request()->routeIs('orders.*') || request()->routeIs('payments.*');
        $this->reportOpen = request()->routeIs('reports.*');
    }
}; ?>
<div class="drawer-side h-[calc(100vh-1.5rem)]">
    <label for="admin-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
    <aside
        class="flex min-h-full max-w-[85vw] flex-col overflow-y-auto overflow-x-auto border-r border-stone-200 bg-base-200 py-5 transition-all duration-1000 is-drawer-close:w-16 is-drawer-open:w-72">
        <a href="{{ $dashboardUrl }}" class="inline-flex items-center gap-3 p-2">
            <span
                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-emerald-800 text-xl text-amber-50">
                <i class="ri-restaurant-2-line"></i>
            </span>
            <span
                class="overflow-hidden transition-all duration-300 is-drawer-close:max-w-0 is-drawer-close:opacity-0 is-drawer-open:max-w-xs is-drawer-open:opacity-100">
                <span class="block text-2xl font-semibold text-emerald-800 whitespace-nowrap"
                    style="font-family: 'Playfair Display', serif;">
                    Saung RH
                </span>
                <span
                    class="block text-xs font-semibold uppercase tracking-[0.2em] text-stone-500 whitespace-nowrap">Admin
                    Panel</span>
            </span>
        </a>
        <div class="divider"></div>
        <nav class="grow">
            <ul class="menu w-full gap-1 rounded-2xl bg-white/70 p-2">
                <li>
                    <a href="{{ $dashboardUrl }}" data-tip="Dashboard"
                        class="{{ request()->routeIs('dashboard') ? 'bg-emerald-800 text-amber-50 hover:bg-emerald-700' : 'text-stone-700 hover:bg-amber-100' }}">
                        <i class="ri-dashboard-line text-lg"></i><span class="is-drawer-close:hidden">Dashboard</span>
                    </a>
                </li>
                <li>
                    <details @if ($masterOpen) open @endif>
                        <summary data-tip="Master Data"
                            class="is-drawer-close:tooltip {{ $masterOpen ? 'bg-amber-200/80 text-stone-900' : 'text-stone-700 hover:bg-amber-100' }}">
                            <i class="ri-restaurant-line text-lg"></i>
                            <span class="is-drawer-close:hidden">Master Data</span>
                        </summary>
                        <ul class="ms-2 border-l border-stone-200 is-drawer-close:hidden">
                            <li>
                                <a href="{{ $menuUrl }}"
                                    class="{{ request()->routeIs('menus.*') ? 'text-emerald-800 font-semibold' : 'text-stone-700' }}">
                                    <i class="ri-bowl-line"></i>
                                    Menu Items
                                </a>
                            </li>
                            <li>
                                <a href="{{ $categoryUrl }}"
                                    class="{{ request()->routeIs('menu-categories.*') ? 'text-emerald-800 font-semibold' : 'text-stone-700' }}">
                                    <i class="ri-price-tag-3-line"></i>
                                    Categories
                                </a>
                            </li>
                            <li>
                                <a href="{{ $tableUrl }}"
                                    class="{{ request()->routeIs('tables.*') ? 'text-emerald-800 font-semibold' : 'text-stone-700' }}">
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
                            class="is-drawer-close:tooltip {{ $transactionOpen ? 'bg-amber-200/80 text-stone-900' : 'text-stone-700 hover:bg-amber-100' }}">
                            <i class="ri-shopping-bag-3-line text-lg"></i>
                            <span class="is-drawer-close:hidden">Transactions</span>
                        </summary>
                        <ul class="ms-2 border-l border-stone-200 is-drawer-close:hidden">
                            <li>
                                <a href="{{ $orderUrl }}"
                                    class="{{ request()->routeIs('orders.*') ? 'text-emerald-800 font-semibold' : 'text-stone-700' }}">
                                    <i class="ri-file-list-3-line"></i>
                                    Orders
                                </a>
                            </li>
                            <li>
                                <a href="{{ $paymentUrl }}"
                                    class="{{ request()->routeIs('payments.*') ? 'text-emerald-800 font-semibold' : 'text-stone-700' }}">
                                    <i class="ri-wallet-3-line"></i>
                                    Payments
                                </a>
                            </li>
                        </ul>
                    </details>
                </li>

                <li>
                    <details @if ($reportOpen) open @endif>
                        <summary data-tip="Reports"
                            class="is-drawer-close:tooltip {{ $reportOpen ? 'bg-amber-200/80 text-stone-900' : 'text-stone-700 hover:bg-amber-100' }}">
                            <i class="ri-bar-chart-box-line text-lg"></i>
                            <span class="is-drawer-close:hidden">Reports</span>
                        </summary>
                        <ul class="ms-2 border-l border-stone-200 is-drawer-close:hidden">
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

        {{-- <div class="mt-6 rounded-3xl bg-emerald-800 p-5 text-amber-50 is-drawer-close:hidden">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-100">Kitchen Status</p>
            <p class="mt-2 text-2xl leading-tight" style="font-family: 'Playfair Display', serif;">
                Keep service fast and flavours consistent.
            </p>
            <a href="{{ $dashboardUrl }}"
                class="mt-4 inline-flex items-center gap-2 rounded-full bg-amber-300 px-4 py-2 text-sm font-semibold text-stone-900">
                <i class="ri-arrow-right-up-line"></i>
                Open Dashboard
            </a>
        </div> --}}
    </aside>
</div>
