<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\ImportProcessor;
use App\Services\ImportService;
use App\Services\PropertySearchService;
use App\Services\ReservationService;
use App\Services\SupplierService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SupplierService::class, SupplierService::class);
        $this->app->bind(ImportProcessor::class, ImportProcessor::class);
        $this->app->bind(ImportService::class, ImportService::class);
        $this->app->bind(PropertySearchService::class, PropertySearchService::class);
        $this->app->bind(ReservationService::class, ReservationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Date::use(CarbonImmutable::class);

        Model::shouldBeStrict(!$this->app->isProduction());
    }
}
