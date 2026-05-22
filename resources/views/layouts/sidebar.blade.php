@php
    $sidebarUser = Auth::user();
    $canUseCompanyModules = $sidebarUser && $sidebarUser->company_id !== null;
    $usesItemVariants = $canUseCompanyModules && $sidebarUser->company?->usesItemVariants();
    $staffLinks = collect([
        ['label' => __('Staff'), 'route' => 'staff.index', 'active' => 'staff.*'],
    ])->filter(fn ($link) => \Illuminate\Support\Facades\Route::has($link['route']) && $canUseCompanyModules && $sidebarUser->hasPermission('manage_users'));
    $branchLinks = collect([
        ['label' => __('Branches'), 'route' => 'branches.index', 'active' => 'branches.*'],
    ])->filter(fn ($link) => \Illuminate\Support\Facades\Route::has($link['route']) && $canUseCompanyModules && $sidebarUser->hasPermission('manage_branches'));
    $salesLinks = collect([
        ['label' => __('Sales'), 'route' => 'sales.index', 'active' => 'sales.*'],
    ])->filter(fn ($link) => \Illuminate\Support\Facades\Route::has($link['route']) && $canUseCompanyModules && $sidebarUser->canAccessModule('sales') && $sidebarUser->hasPermission('manage_sales'));
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

@if ($staffLinks->isNotEmpty() || $branchLinks->isNotEmpty() || $salesLinks->isNotEmpty() || $serviceLinks->isNotEmpty() || $bookingLinks->isNotEmpty() || $jobOrderLinks->isNotEmpty() || $customerLinks->isNotEmpty() || $inventoryLinks->isNotEmpty())
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
