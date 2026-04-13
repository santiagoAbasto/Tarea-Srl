<?php

namespace App\Providers;

use App\Models\SeoMeta;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\View;
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

        View::composer('web.layouts.app', function ($view): void {
            $view->with('seo', SeoMeta::forCurrentRequest());
        });
    }
}
