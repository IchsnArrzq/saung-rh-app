<?php

use App\Support\SidebarNavigation;
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
    public string $adminUsersUrl = '#';
    public string $customerUsersUrl = '#';

    public bool $masterOpen = false;
    public bool $transactionOpen = false;
    public bool $reportOpen = false;
    public bool $userManagementOpen = false;

    /** @var array<int, array<string, mixed>> */
    public array $groups = [];

    public function mount(SidebarNavigation $navigation): void
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
        $this->adminUsersUrl = Route::has('admin-users.index') ? route('admin-users.index') : '#';
        $this->customerUsersUrl = Route::has('customer-users.index') ? route('customer-users.index') : '#';

        $this->masterOpen = request()->routeIs('menus.*')
            || request()->routeIs('menu-categories.*')
            || request()->routeIs('table-statuses.*')
            || request()->routeIs('table-categories.*')
            || request()->routeIs('tables.*');

        $this->transactionOpen = request()->routeIs('orders.*')
            || request()->routeIs('payments.*')
            || request()->routeIs('reservations.*');

        $this->reportOpen = request()->routeIs('reports.*');
        
        $this->userManagementOpen = request()->routeIs('admin-users.*') 
            || request()->routeIs('customer-users.*');
        $this->groups = $navigation->forCurrentUser();
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
                <span class="block text-2xl font-semibold text-primary whitespace-nowrap"
                    style="font-family: 'Playfair Display', serif;">Admin Panel</span>
            </span>
        </a>

        <div class="divider"></div>

        <nav class="grow">
            <ul class="menu w-full gap-1 rounded-2xl p-2">
                @foreach ($groups as $group)
                    @if (count($group['items']) === 1 && $group['label'] === 'Dashboard')
                        @php($item = $group['items'][0])
                        <li>
                            <a href="{{ $item['url'] }}" data-tip="{{ $item['label'] }}"
                                class="{{ $item['is_active'] ? 'bg-primary text-primary-content hover:bg-neutral' : 'text-stone-700 hover:bg-base-300' }}">
                                <i class="{{ $item['icon'] }} text-lg"></i>
                                <span class="is-drawer-close:hidden">{{ $item['label'] }}</span>
                            </a>
                        </li>
                        @continue
                    @endif

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

                <li>
                    <details @if ($userManagementOpen) open @endif>
                        <summary data-tip="User Management"
                            class="is-drawer-close:tooltip {{ $userManagementOpen ? 'bg-amber-200/80 text-stone-900' : 'text-stone-700 hover:bg-amber-100' }}">
                            <i class="ri-group-line text-lg"></i>
                            <span class="is-drawer-close:hidden">User Management</span>
                        </summary>
                        <ul class="ms-2 border-l border-stone-200 is-drawer-close:hidden">
                            <li>
                                <a href="{{ $adminUsersUrl }}"
                                    class="{{ request()->routeIs('admin-users.*') ? 'text-emerald-800 font-semibold' : 'text-stone-700' }}">
                                    <i class="ri-user-settings-line"></i>
                                    Admin
                                </a>
                            </li>
                            <li>
                                <a href="{{ $customerUsersUrl }}"
                                    class="{{ request()->routeIs('customer-users.*') ? 'text-emerald-800 font-semibold' : 'text-stone-700' }}">
                                    <i class="ri-user-smile-line"></i>
                                    Customer
                                </a>
                            </li>
                        </ul>
                    </details>
                </li>

                    <li>
                        <details @if ($group['is_open']) open @endif>
                            <summary data-tip="{{ $group['label'] }}"
                                class="is-drawer-close:tooltip {{ $group['is_open'] ? 'bg-base-300 text-primary font-semibold' : 'text-stone-700 hover:bg-base-300' }}">
                                <i class="{{ $group['icon'] }} text-lg"></i>
                                <span class="is-drawer-close:hidden">{{ $group['label'] }}</span>
                            </summary>
                            <ul class="ms-2 border-l border-base-300 is-drawer-close:hidden">
                                @foreach ($group['items'] as $item)
                                    <li>
                                        <a href="{{ $item['url'] }}"
                                            class="{{ $item['is_active'] ? 'text-primary font-semibold' : 'text-stone-700' }}">
                                            <i class="{{ $item['icon'] }}"></i>
                                            {{ $item['label'] }}
                                            @if (!empty($item['badge_value']))
                                                <span
                                                    class="badge badge-sm badge-primary">{{ $item['badge_value'] }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    </li>
                @endforeach
            </ul>
        </nav>
    </aside>
</div>
