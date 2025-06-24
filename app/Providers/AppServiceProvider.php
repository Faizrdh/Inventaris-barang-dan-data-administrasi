<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\LeaveApplication;
use App\Models\Item;
use App\Models\GoodsIn;
use App\Models\GoodsOut;
use App\Observers\LeaveApplicationObserver;
use App\Observers\ItemObserver;
use App\Observers\GoodsInObserver;
use App\Observers\GoodsOutObserver;
use App\Services\StockService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register StockService as singleton
        $this->app->singleton(StockService::class, function ($app) {
            return new StockService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers untuk real-time notifications dan stock updates
        LeaveApplication::observe(LeaveApplicationObserver::class);
        Item::observe(ItemObserver::class);
        GoodsIn::observe(GoodsInObserver::class);
        GoodsOut::observe(GoodsOutObserver::class);
    }
}