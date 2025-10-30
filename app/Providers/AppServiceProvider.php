<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('APP_ENV') == 'production') {
            URL::forceScheme('https');
            Http::macro('dsoapi', function () {
                return Http::withHeaders([
                    'x-internal-access-token' => config('dsoapi.token'),
                ])->baseUrl(config('dsoapi.url'));
            });
        } else {
            Http::macro('dsoapi', function () {
                return Http::withHeaders([
                    'x-internal-access-token' => config('dsoapi.token'),
                ])->baseUrl(config('dsoapi.url'));
            });
        }
    }
}
