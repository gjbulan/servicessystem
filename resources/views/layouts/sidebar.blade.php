@php
    $sidebarUser = Auth::user();
    $canUseCompanyModules = $sidebarUser && $sidebarUser->company_id !== null;
    $usesItemVariants = $canUseCompanyModules && $sidebarUser->company?->usesItemVariants();
    $canAccessTechnicianIncentives = $sidebarUser
        && $sidebarUser->canAccessModule('technician_incentives')
        && ($sidebarUser->hasPermission('manage_technician_incentives') || $sidebarUser->hasRole('Technician') || $sidebarUser->isSuperAdmin());
    $staffLinks = collect([
        ['label' => __('Staff'), 'route' => 'staff.index', 'active' => 'staff.*'],
    ])->filter(fn ($link) => \Illuminate\Support\Facades\Route::has($link['route']) && $canUseCompanyModules && $sidebarUser->hasPermission('manage_users'));
    $branchLinks = collect([
        ['label' => __('Branches'), 'route' => 'branches.index', 'active' => 'branches.*'],
    ])->filter(fn ($link) => \Illuminate\Support\Facades\Route::has($link['route']) && $canUseCompanyModules && $sidebarUser->hasPermission('manage_branches'));
    $salesLinks = collect([
        ['label' => __('Sales'), 'route' => 'sales.index', 'active' => 'sales.*'],
    ])->filter(fn ($link) => \Illuminate\Support\Facades\Route::has($link['route']) && $canUseCompanyModules && $sidebarUser->canAccessModule('sales') && $sidebarUser->hasPermission('manage_sales'));
    $accountingLinks = collect([
        ['label' => __('Expense Categories'), 'route' => 'expense-categories.index', 'active' => 'expense-categories.*'],
        ['label' => __('Expenses'), 'route' => 'expenses.index', 'active' => 'expenses.*'],
        ['label' => __('Financial Summary'), 'route' => 'reports.financial-summary', 'active' => 'reports.financial-summary'],
        ['label' => __('Income Statement'), 'route' => 'reports.income-statement', 'active' => 'reports.income-statement'],
        ['label' => __('Branch Profitability'), 'route' => 'reports.branch-profitability', 'active' => 'reports.branch-profitability'],
    ])->filter(fn ($link) => \Illuminate\Support\Facades\Route::has($link['route']) && $canUseCompanyModules && $sidebarUser->canAccessModule('accounting'));
    $serviceLinks = collect([
        ['label' => __('Asset Types'), 'route' => 'asset-types.index', 'active' => 'asset-types.*'],
        ['label' => __('Customer Assets'), 'route' => 'customer-assets.index', 'active' => 'customer-assets.*'],
        ['label' => __('Service Categories'), 'route' => 'service-categories.index', 'active' => 'service-categories.*'],
        ['label' => __('Services'), 'route' => 'services.index', 'active' => 'services.*'],
    ])->filter(fn ($link) => \Illuminate\Support\Facades\Route::has($link['route']) && $canUseCompanyModules && $sidebarUser->canAccessModule('services') && $sidebarUser->hasPermission('manage_services'));
    $bookingLinks = collect([
        ['label' => __('Public Booking'), 'route' => 'bookings.public-info', 'active' => 'bookings.public-info'],
        ['label' => __('Bookings'), 'route' => 'bookings.index', 'active' => 'bookings.*'],
    ])->filter(fn ($link) => \Illuminate\Support\Facades\Route::has($link['route']) && $canUseCompanyModules && $sidebarUser->canAccessModule('bookings') && $sidebarUser->hasPermission('manage_bookings'));
    $jobOrderLinks = collect([
        ['label' => __('Job Orders'), 'route' => 'job-orders.index', 'active' => 'job-orders.*'],
    ])->filter(fn ($link) => \Illuminate\Support\Facades\Route::has($link['route']) && $canUseCompanyModules && $sidebarUser->canAccessModule('job_orders') && $sidebarUser->hasPermission('manage_job_orders'));
    $incentiveLinks = collect([
        ['label' => __('Technician Incentives'), 'route' => 'technician-incentives.index', 'active' => 'technician-incentives.*'],
    ])->filter(fn ($link) => \Illuminate\Support\Facades\Route::has($link['route']) && $canUseCompanyModules && $canAccessTechnicianIncentives);
    $customerLinks = collect([
        ['label' => __('Customers'), 'route' => 'customers.index', 'active' => 'customers.*'],
    ])->filter(fn ($link) => \Illuminate\Support\Facades\Route::has($link['route']) && $canUseCompanyModules && $sidebarUser->canAccessModule('customers') && $sidebarUser->hasPermission('manage_customers'));
    $inventoryLinks = collect([
        ['label' => __('Categories'), 'route' => 'inventory.categories.index', 'active' => 'inventory.categories.*'],
        ['label' => __('Brands'), 'route' => 'inventory.brands.index', 'active' => 'inventory.brands.*'],
        ['label' => __('Items'), 'route' => 'inventory.items.index', 'active' => 'inventory.items.*'],
        ['label' => __('Variants'), 'route' => 'inventory.variants.index', 'active' => 'inventory.variants.*', 'requires_variants' => true],
        ['label' => __('Stock In'), 'route' => 'inventory.stock-in.create', 'active' => 'inventory.stock-in.*'],
    ])->filter(fn ($link) => \Illuminate\Support\Facades\Route::has($link['route']) && $canUseCompanyModules && $sidebarUser->canAccessModule('inventory') && $sidebarUser->hasPermission('manage_inventory') && (! ($link['requires_variants'] ?? false) || $usesItemVariants));
@endphp

@if ($staffLinks->isNotEmpty() || $branchLinks->isNotEmpty() || $salesLinks->isNotEmpty() || $accountingLinks->isNotEmpty() || $serviceLinks->isNotEmpty() || $bookingLinks->isNotEmpty() || $jobOrderLinks->isNotEmpty() || $incentiveLinks->isNotEmpty() || $customerLinks->isNotEmpty() || $inventoryLinks->isNotEmpty())
    <aside class="hidden w-64 shrink-0 border-r border-gray-200 bg-white lg:block">
        <div class="sticky top-0 space-y-6 p-6">
            @if ($branchLinks->isNotEmpty())
                <nav>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Operations') }}</p>
                    <div class="mt-3 space-y-1">
                        @foreach ($staffLinks as $link)
                            <a href="{{ route($link['route']) }}" class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($link['active']) ? 'bg-gray-100 text-gray-950' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-950' }}">{{ $link['label'] }}</a>
                        @endforeach
                        @foreach ($branchLinks as $link)
                            <a href="{{ route($link['route']) }}" class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($link['active']) ? 'bg-gray-100 text-gray-950' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-950' }}">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </nav>
            @elseif ($staffLinks->isNotEmpty())
                <nav>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Operations') }}</p>
                    <div class="mt-3 space-y-1">
                        @foreach ($staffLinks as $link)
                            <a href="{{ route($link['route']) }}" class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($link['active']) ? 'bg-gray-100 text-gray-950' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-950' }}">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </nav>
            @endif

            @if ($salesLinks->isNotEmpty())
                <nav>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Sales') }}</p>
                    <div class="mt-3 space-y-1">
                        @foreach ($salesLinks as $link)
                            <a href="{{ route($link['route']) }}" class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($link['active']) ? 'bg-gray-100 text-gray-950' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-950' }}">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </nav>
            @endif

            @if ($accountingLinks->isNotEmpty())
                <nav>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Accounting') }}</p>
                    <div class="mt-3 space-y-1">
                        @foreach ($accountingLinks as $link)
                            <a href="{{ route($link['route']) }}" class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($link['active']) ? 'bg-gray-100 text-gray-950' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-950' }}">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </nav>
            @endif

            @if ($serviceLinks->isNotEmpty())
                <nav>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Services') }}</p>
                    <div class="mt-3 space-y-1">
                        @foreach ($serviceLinks as $link)
                            <a href="{{ route($link['route']) }}" class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($link['active']) ? 'bg-gray-100 text-gray-950' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-950' }}">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </nav>
            @endif

            @if ($bookingLinks->isNotEmpty())
                <nav>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Bookings') }}</p>
                    <div class="mt-3 space-y-1">
                        @foreach ($bookingLinks as $link)
                            <a href="{{ route($link['route']) }}" class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($link['active']) ? 'bg-gray-100 text-gray-950' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-950' }}">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </nav>
            @endif

            @if ($jobOrderLinks->isNotEmpty())
                <nav>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Job Orders') }}</p>
                    <div class="mt-3 space-y-1">
                        @foreach ($jobOrderLinks as $link)
                            <a href="{{ route($link['route']) }}" class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($link['active']) ? 'bg-gray-100 text-gray-950' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-950' }}">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </nav>
            @endif

            @if ($incentiveLinks->isNotEmpty())
                <nav>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Incentives') }}</p>
                    <div class="mt-3 space-y-1">
                        @foreach ($incentiveLinks as $link)
                            <a href="{{ route($link['route']) }}" class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($link['active']) ? 'bg-gray-100 text-gray-950' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-950' }}">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </nav>
            @endif

            @if ($customerLinks->isNotEmpty())
                <nav>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Customers') }}</p>
                    <div class="mt-3 space-y-1">
                        @foreach ($customerLinks as $link)
                            <a href="{{ route($link['route']) }}" class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($link['active']) ? 'bg-gray-100 text-gray-950' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-950' }}">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </nav>
            @endif

            @if ($inventoryLinks->isNotEmpty())
                <nav>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Inventory') }}</p>
                    <div class="mt-3 space-y-1">
                        @foreach ($inventoryLinks as $link)
                            <a href="{{ route($link['route']) }}" class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($link['active']) ? 'bg-gray-100 text-gray-950' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-950' }}">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </nav>
            @endif
        </div>
    </aside>
@endif
