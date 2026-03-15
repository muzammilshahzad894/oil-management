<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminLoginController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\CustomerController;
use App\Http\Controllers\admin\SaleController;
use App\Http\Controllers\admin\ProfileController;
use App\Http\Controllers\admin\ReportController;
use App\Http\Controllers\admin\SettingsController;
use App\Http\Controllers\admin\ExtraPaidController;
use App\Http\Controllers\admin\LedgerCustomerController;
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
    Route::get('/admin/brands/{brand}/stock/create', [BrandController::class, 'stockCreate'])->name('admin.brands.stock.create');
    Route::post('/admin/brands/{brand}/stock', [BrandController::class, 'stockStore'])->name('admin.brands.stock.store');
    Route::get('/admin/brands/{brand}/stock/archived', [BrandController::class, 'stockArchived'])->name('admin.brands.stock.archived');
    Route::post('/admin/brands/{brand}/stock/{batch}/restore', [BrandController::class, 'stockRestore'])->name('admin.brands.stock.restore');
    Route::get('/admin/brands/{brand}/stock/{batch}/edit', [BrandController::class, 'stockEdit'])->name('admin.brands.stock.edit');
    Route::put('/admin/brands/{brand}/stock/{batch}', [BrandController::class, 'stockUpdate'])->name('admin.brands.stock.update');
    Route::delete('/admin/brands/{brand}/stock/{batch}', [BrandController::class, 'stockDestroy'])->name('admin.brands.stock.destroy');
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
    Route::get('/admin/customers/{customer}/extra-paid/balance', [ExtraPaidController::class, 'balance'])->name('admin.customers.extra-paid.balance');
    Route::post('/admin/customers/{customer}/extra-paid', [ExtraPaidController::class, 'store'])->name('admin.customers.extra-paid.store');
    
    // Settings
    Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
    Route::put('/admin/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
    
    // Sales
    Route::get('/admin/sales/search-customers', [SaleController::class, 'searchCustomers'])->name('admin.sales.search-customers');
    Route::get('/admin/sales/suggested-price', [SaleController::class, 'suggestedPrice'])->name('admin.sales.suggested-price');
    Route::get('/admin/sales/{id}/receipt', [SaleController::class, 'receipt'])->name('admin.sales.receipt');
    Route::post('/admin/sales/{sale}/payments', [SaleController::class, 'storePayment'])->name('admin.sales.payments.store');
    Route::post('/admin/sales/{sale}/payments/from-extra-paid', [ExtraPaidController::class, 'useForSalePayment'])->name('admin.sales.payments.from-extra-paid');
    Route::delete('/admin/sales/{sale}/payments/{payment}', [SaleController::class, 'destroyPayment'])->name('admin.sales.payments.destroy');
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
    Route::get('/admin/reports/profit-loss', [ReportController::class, 'profitLoss'])->name('admin.reports.profit-loss');

    // Ledger (digital khatam)
    Route::prefix('admin/ledger')->name('admin.ledger.')->group(function () {
        Route::get('customers/{customer}/export', [LedgerCustomerController::class, 'export'])->name('customers.export');
        Route::get('customers/{customer}/export-pdf', [LedgerCustomerController::class, 'exportPdf'])->name('customers.export-pdf');
        Route::post('customers/{customer}/transactions', [LedgerCustomerController::class, 'storeTransaction'])->name('customers.transactions.store');
        Route::put('customers/{customer}/transactions/{transaction}', [LedgerCustomerController::class, 'updateTransaction'])->name('customers.transactions.update');
        Route::delete('customers/{customer}/transactions/{transaction}', [LedgerCustomerController::class, 'destroyTransaction'])->name('customers.transactions.destroy');
        Route::resource('customers', LedgerCustomerController::class)->names([
            'index' => 'customers.index',
            'create' => 'customers.create',
            'store' => 'customers.store',
            'show' => 'customers.show',
            'edit' => 'customers.edit',
            'update' => 'customers.update',
            'destroy' => 'customers.destroy',
        ]);
    });
});

// migrate fresh commands
Route::get('/migrate-fresh', function () {
    Artisan::call('migrate:fresh');
    Artisan::call('db:seed');
    dd('done');
});