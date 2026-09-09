<?php

namespace App\Providers;

use App\Models\Organization;
use App\Services\TenantPortalNoticeService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Organization::created(function (Organization $organization): void {
            app(TenantPortalNoticeService::class)->ensureDefault($organization);
        });
    }
}
