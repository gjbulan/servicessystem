<?php

use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\CompanyModuleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
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

require __DIR__.'/auth.php';
