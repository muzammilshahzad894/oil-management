<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminLoginController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\CustomerController;
use App\Http\Controllers\admin\InventoryController;
use App\Http\Controllers\admin\SaleController;
use App\Http\Controllers\admin\ProfileController;
use App\Http\Controllers\admin\ReportController;

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
    
    // Inventory
    Route::resource('admin/inventory', InventoryController::class)->names([
        'index' => 'admin.inventory.index',
        'create' => 'admin.inventory.create',
        'store' => 'admin.inventory.store',
        'show' => 'admin.inventory.show',
        'edit' => 'admin.inventory.edit',
        'update' => 'admin.inventory.update',
        'destroy' => 'admin.inventory.destroy',
    ]);
    Route::post('/admin/inventory/{id}/add-stock', [InventoryController::class, 'addStock'])->name('admin.inventory.add-stock');
    
    // Sales
    Route::get('/admin/sales/search-customers', [SaleController::class, 'searchCustomers'])->name('admin.sales.search-customers');
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