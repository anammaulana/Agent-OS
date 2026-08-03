<?php

namespace App\Providers;

use App\Support\TenantContext;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            TenantContext::class,
            fn (): TenantContext => new TenantContext()
        );
    }

    public function boot(): void
    {
        //
    }
}