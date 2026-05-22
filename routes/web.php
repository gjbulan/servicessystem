<?php

use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CompanyModuleController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Inventory\ItemBrandController;
use App\Http\Controllers\Inventory\ItemCategoryController;
use App\Http\Controllers\Inventory\ItemController;
use App\Http\Controllers\Inventory\ItemVariantController;
use App\Http\Controllers\Inventory\StockInController;
use App\Http\Controllers\InventorySettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Sales\SaleController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'company.access', 'permission:manage_settings'])
    ->prefix('settings')
    ->name('settings.')
    ->group(function () {
        Route::get('/inventory', [InventorySettingController::class, 'index'])->name('inventory.index');
        Route::patch('/inventory/{inventorySetting}', [InventorySettingController::class, 'update'])->name('inventory.update');
        Route::get('/modules', [CompanyModuleController::class, 'index'])->name('modules.index');
        Route::patch('/modules/{companyModule}', [CompanyModuleController::class, 'update'])->name('modules.update');
    });

Route::middleware(['auth', 'verified', 'permission:manage_companies'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/companies/{company}/users', [AdminCompanyController::class, 'users'])->name('companies.users');
        Route::post('/companies/{company}/users/assign', [AdminCompanyController::class, 'assignUser'])->name('companies.users.assign');
        Route::resource('companies', AdminCompanyController::class);
    });

Route::middleware(['auth', 'verified', 'role:Super Admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('users', AdminUserController::class);
    });

Route::middleware(['auth', 'verified', 'company.access'])->group(function () {
    Route::middleware('permission:manage_users')
        ->resource('staff', StaffController::class)
        ->parameters(['staff' => 'user']);

    Route::middleware('permission:manage_branches')
        ->resource('branches', BranchController::class);

    Route::middleware(['module:customers', 'permission:manage_customers'])
        ->resource('customers', CustomerController::class);

    Route::middleware(['module:inventory', 'permission:manage_inventory'])
        ->prefix('inventory')
        ->name('inventory.')
        ->group(function () {
            Route::resource('categories', ItemCategoryController::class)
                ->parameters(['categories' => 'category']);
            Route::resource('brands', ItemBrandController::class)
                ->parameters(['brands' => 'brand']);
            Route::resource('items', ItemController::class);
            Route::resource('variants', ItemVariantController::class)
                ->parameters(['variants' => 'variant']);
            Route::get('/stock-in', [StockInController::class, 'create'])->name('stock-in.create');
            Route::post('/stock-in', [StockInController::class, 'store'])->name('stock-in.store');
        });

    Route::middleware(['module:sales', 'permission:manage_sales'])
        ->prefix('sales')
        ->name('sales.')
        ->group(function () {
            Route::get('/', [SaleController::class, 'index'])->name('index');
            Route::get('/create', [SaleController::class, 'create'])->name('create');
            Route::post('/', [SaleController::class, 'store'])->name('store');
            Route::get('/{sale}/payments', [SaleController::class, 'payments'])->name('payments');
            Route::post('/{sale}/payments', [SaleController::class, 'storePayment'])->name('payments.store');
            Route::get('/{sale}/print', [SaleController::class, 'printView'])->name('print');
            Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
            Route::get('/{sale}/edit', [SaleController::class, 'edit'])->name('edit');
            Route::match(['put', 'patch'], '/{sale}', [SaleController::class, 'update'])->name('update');
        });
});

require __DIR__.'/auth.php';
