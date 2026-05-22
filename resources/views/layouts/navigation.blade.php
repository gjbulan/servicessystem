@php
    $navigationUser = Auth::user();
    $canAccessTechnicianIncentives = $navigationUser
        && $navigationUser->canAccessModule('technician_incentives')
        && ($navigationUser->hasPermission('manage_technician_incentives') || $navigationUser->hasRole('Technician') || $navigationUser->isSuperAdmin());
    $moduleNavigationLinks = collect([
        ['module' => 'services', 'label' => __('Services'), 'route' => 'services.index', 'permission' => 'manage_services'],
        ['module' => 'bookings', 'label' => __('Bookings'), 'route' => 'bookings.index', 'permission' => 'manage_bookings'],
        ['module' => 'job_orders', 'label' => __('Job Orders'), 'route' => 'job-orders.index', 'permission' => 'manage_job_orders'],
        ['module' => 'technician_incentives', 'label' => __('Technician Incentives'), 'route' => 'technician-incentives.index', 'enabled' => $canAccessTechnicianIncentives],
        ['module' => 'inventory', 'label' => __('Inventory'), 'route' => 'inventory.index', 'permission' => null],
        ['module' => 'sales', 'label' => __('Sales'), 'route' => 'sales.index', 'permission' => 'manage_sales'],
        ['module' => 'invoices', 'label' => __('Invoices'), 'route' => 'invoices.index', 'permission' => null],
    ])->filter(fn ($link) => \Illuminate\Support\Facades\Route::has($link['route']) && (($link['enabled'] ?? null) || ($navigationUser?->canAccessModule($link['module']) && (! ($link['permission'] ?? null) || $navigationUser?->hasPermission($link['permission'])))));
    $canManageModuleSettings = $navigationUser && ($navigationUser->isSuperAdmin() || $navigationUser->hasPermission('manage_settings'));
    $canManageCompanies = $navigationUser && ($navigationUser->isSuperAdmin() || $navigationUser->hasPermission('manage_companies'));
    $canManagePlatformUsers = $navigationUser && $navigationUser->isSuperAdmin();
    $canUseTenantModules = $navigationUser && $navigationUser->company_id !== null;
    $usesItemVariants = $canUseTenantModules && $navigationUser->company?->usesItemVariants();
    $businessNavigationLinks = collect([
        ['label' => __('Staff'), 'route' => 'staff.index', 'active' => 'staff.*', 'enabled' => $canUseTenantModules && $navigationUser?->hasPermission('manage_users')],
        ['label' => __('Branches'), 'route' => 'branches.index', 'active' => 'branches.*', 'enabled' => $canUseTenantModules && $navigationUser?->hasPermission('manage_branches')],
        ['label' => __('Customers'), 'route' => 'customers.index', 'active' => 'customers.*', 'enabled' => $canUseTenantModules && $navigationUser?->canAccessModule('customers') && $navigationUser?->hasPermission('manage_customers')],
        ['label' => __('Categories'), 'route' => 'inventory.categories.index', 'active' => 'inventory.categories.*', 'enabled' => $canUseTenantModules && $navigationUser?->canAccessModule('inventory') && $navigationUser?->hasPermission('manage_inventory')],
        ['label' => __('Brands'), 'route' => 'inventory.brands.index', 'active' => 'inventory.brands.*', 'enabled' => $canUseTenantModules && $navigationUser?->canAccessModule('inventory') && $navigationUser?->hasPermission('manage_inventory')],
        ['label' => __('Items'), 'route' => 'inventory.items.index', 'active' => 'inventory.items.*', 'enabled' => $canUseTenantModules && $navigationUser?->canAccessModule('inventory') && $navigationUser?->hasPermission('manage_inventory')],
        ['label' => __('Variants'), 'route' => 'inventory.variants.index', 'active' => 'inventory.variants.*', 'enabled' => $canUseTenantModules && $usesItemVariants && $navigationUser?->canAccessModule('inventory') && $navigationUser?->hasPermission('manage_inventory')],
        ['label' => __('Stock In'), 'route' => 'inventory.stock-in.create', 'active' => 'inventory.stock-in.*', 'enabled' => $canUseTenantModules && $navigationUser?->canAccessModule('inventory') && $navigationUser?->hasPermission('manage_inventory')],
        ['label' => __('Asset Types'), 'route' => 'asset-types.index', 'active' => 'asset-types.*', 'enabled' => $canUseTenantModules && $navigationUser?->canAccessModule('services') && $navigationUser?->hasPermission('manage_services')],
        ['label' => __('Customer Assets'), 'route' => 'customer-assets.index', 'active' => 'customer-assets.*', 'enabled' => $canUseTenantModules && $navigationUser?->canAccessModule('services') && $navigationUser?->hasPermission('manage_services')],
        ['label' => __('Service Categories'), 'route' => 'service-categories.index', 'active' => 'service-categories.*', 'enabled' => $canUseTenantModules && $navigationUser?->canAccessModule('services') && $navigationUser?->hasPermission('manage_services')],
        ['label' => __('Services'), 'route' => 'services.index', 'active' => 'services.*', 'enabled' => $canUseTenantModules && $navigationUser?->canAccessModule('services') && $navigationUser?->hasPermission('manage_services')],
        ['label' => __('Public Booking'), 'route' => 'bookings.public-info', 'active' => 'bookings.public-info', 'enabled' => $canUseTenantModules && $navigationUser?->canAccessModule('bookings') && $navigationUser?->hasPermission('manage_bookings')],
        ['label' => __('Bookings'), 'route' => 'bookings.index', 'active' => 'bookings.*', 'enabled' => $canUseTenantModules && $navigationUser?->canAccessModule('bookings') && $navigationUser?->hasPermission('manage_bookings')],
        ['label' => __('Job Orders'), 'route' => 'job-orders.index', 'active' => 'job-orders.*', 'enabled' => $canUseTenantModules && $navigationUser?->canAccessModule('job_orders') && $navigationUser?->hasPermission('manage_job_orders')],
        ['label' => __('Technician Incentives'), 'route' => 'technician-incentives.index', 'active' => 'technician-incentives.*', 'enabled' => $canUseTenantModules && $canAccessTechnicianIncentives],
    ])->filter(fn ($link) => $link['enabled'] && \Illuminate\Support\Facades\Route::has($link['route']));
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if ($canManageCompanies)
                        <x-nav-link :href="route('admin.companies.index')" :active="request()->routeIs('admin.companies.*')">
                            {{ __('Companies') }}
                        </x-nav-link>
                    @endif

                    @if ($canManagePlatformUsers)
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            {{ __('Platform Users') }}
                        </x-nav-link>
                    @endif

                    @foreach ($moduleNavigationLinks as $moduleLink)
                        <x-nav-link :href="route($moduleLink['route'])" :active="request()->routeIs($moduleLink['route'])">
                            {{ $moduleLink['label'] }}
                        </x-nav-link>
                    @endforeach

                    @if ($canManageModuleSettings)
                        <x-nav-link :href="route('settings.modules.index')" :active="request()->routeIs('settings.modules.*')">
                            {{ __('Modules') }}
                        </x-nav-link>
                        <x-nav-link :href="route('settings.inventory.index')" :active="request()->routeIs('settings.inventory.*')">
                            {{ __('Inventory Settings') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if ($canManageCompanies)
                <x-responsive-nav-link :href="route('admin.companies.index')" :active="request()->routeIs('admin.companies.*')">
                    {{ __('Companies') }}
                </x-responsive-nav-link>
            @endif

            @if ($canManagePlatformUsers)
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('Platform Users') }}
                </x-responsive-nav-link>
            @endif

            @foreach ($moduleNavigationLinks as $moduleLink)
                <x-responsive-nav-link :href="route($moduleLink['route'])" :active="request()->routeIs($moduleLink['route'])">
                    {{ $moduleLink['label'] }}
                </x-responsive-nav-link>
            @endforeach

            @foreach ($businessNavigationLinks as $businessLink)
                <x-responsive-nav-link :href="route($businessLink['route'])" :active="request()->routeIs($businessLink['active'])">
                    {{ $businessLink['label'] }}
                </x-responsive-nav-link>
            @endforeach

            @if ($canManageModuleSettings)
                <x-responsive-nav-link :href="route('settings.modules.index')" :active="request()->routeIs('settings.modules.*')">
                    {{ __('Modules') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('settings.inventory.index')" :active="request()->routeIs('settings.inventory.*')">
                    {{ __('Inventory Settings') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
