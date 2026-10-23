<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Pengaturan situs tersedia di semua view sebagai $site, dan logo penyelenggara sebagai $sitePartners.
        View::composer('*', fn ($view) => $view->with([
            'site' => SiteSetting::current(),
            'sitePartners' => SiteSetting::partners(),
        ]));
    }
}
