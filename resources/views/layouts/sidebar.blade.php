@php
    $sidebarUser = Auth::user();
    $canUseCompanyModules = $sidebarUser && $sidebarUser->company_id !== null;
    $usesItemVariants = $canUseCompanyModules && $sidebarUser->company?->usesItemVariants();
    $branchLinks = collect([
        ['label' => __('Branches'), 'route' => 'branches.index', 'active' => 'branches.*'],
    ])->filter(fn ($link) => \Illuminate\Support\Facades\Route::has($link['route']) && $canUseCompanyModules && $sidebarUser->hasPermission('manage_branches'));
    $salesLinks = collect([
        ['label' => __('Sales'), 'route' => 'sales.index', 'active' => 'sales.*'],
    ])->filter(fn ($link) => \Illuminate\Support\Facades\Route::has($link['route']) && $canUseCompanyModules && $sidebarUser->canAccessModule('sales') && $sidebarUser->hasPermission('manage_sales'));
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

@if ($branchLinks->isNotEmpty() || $salesLinks->isNotEmpty() || $customerLinks->isNotEmpty() || $inventoryLinks->isNotEmpty())
    <aside class="hidden w-64 shrink-0 border-r border-gray-200 bg-white lg:block">
        <div class="sticky top-0 space-y-6 p-6">
            @if ($branchLinks->isNotEmpty())
                <nav>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Operations') }}</p>
                    <div class="mt-3 space-y-1">
                        @foreach ($branchLinks as $link)
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
