<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminLoginController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\CustomerController;
use App\Http\Controllers\admin\SaleController;
use App\Http\Controllers\admin\ProfileController;
use App\Http\Controllers\admin\ReportController;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    } else {
        return redirect()->route('admin.login');
    }
});

// check if user is already logged in then redirect to dashboard
Route::middleware(['guest'])->group(function () {
    Route::match(['get', 'post'], '/admin/login', [AdminLoginController::class, 'login'])->name('admin.login');

    // define login route and redirect to admin.login
    Route::get('/login', function () {
        return redirect()->route('admin.login');
    })->name('login');
});

// protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin', function () {
        return redirect()->route('admin.dashboard');
    });
    
    Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
    
    // Profile
    Route::get('/admin/profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('/admin/profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::put('/admin/profile/password', [ProfileController::class, 'updatePassword'])->name('admin.profile.password.update');
    
    // Brands
    Route::resource('admin/brands', BrandController::class)->names([
        'index' => 'admin.brands.index',
        'create' => 'admin.brands.create',
        'store' => 'admin.brands.store',
        'show' => 'admin.brands.show',
        'edit' => 'admin.brands.edit',
        'update' => 'admin.brands.update',
        'destroy' => 'admin.brands.destroy',
    ]);
    
    // Customers
    Route::resource('admin/customers', CustomerController::class)->names([
        'index' => 'admin.customers.index',
        'create' => 'admin.customers.create',
        'store' => 'admin.customers.store',
        'show' => 'admin.customers.show',
        'edit' => 'admin.customers.edit',
        'update' => 'admin.customers.update',
        'destroy' => 'admin.customers.destroy',
    ]);
    Route::post('/admin/customers/search', [CustomerController::class, 'search'])->name('admin.customers.search');
    
    // Sales
    Route::get('/admin/sales/search-customers', [SaleController::class, 'searchCustomers'])->name('admin.sales.search-customers');
    Route::get('/admin/sales/{id}/receipt', [SaleController::class, 'receipt'])->name('admin.sales.receipt');
    Route::resource('admin/sales', SaleController::class)->names([
        'index' => 'admin.sales.index',
        'create' => 'admin.sales.create',
        'store' => 'admin.sales.store',
        'show' => 'admin.sales.show',
        'edit' => 'admin.sales.edit',
        'update' => 'admin.sales.update',
        'destroy' => 'admin.sales.destroy',
    ]);
    
    // Reports
    Route::get('/admin/reports/customer', [ReportController::class, 'customer'])->name('admin.reports.customer');
    Route::get('/admin/reports/customer/export', [ReportController::class, 'exportExcel'])->name('admin.reports.customer.export');
});

// migrate fresh commands
Route::get('/migrate-fresh', function () {
    Artisan::call('migrate:fresh');
    Artisan::call('db:seed');
    dd('done');
});