<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

        View::composer('admin.*', function ($view) {
            $view->with('showPurchasePrice', Schema::hasTable('settings')
                ? \App\Models\Setting::getBool('show_purchase_price', true)
                : true);
        });

        }
}
