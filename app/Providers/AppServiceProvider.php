<?php

namespace App\Providers;

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
        // Prefer project root `lang/` even if `resources/lang` exists (avoids empty/wrong translations).
        if (is_dir($this->app->basePath('lang'))) {
            $this->app->useLangPath($this->app->basePath('lang'));
        }
    }
}
