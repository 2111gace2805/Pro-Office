<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;

use Illuminate\Mail\MailManager;
use App\Mail\ZeptoMailTransport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        app(MailManager::class)->extend('zeptomail', function () {
        $apiKey = config('services.zeptomail.key');
        return new ZeptoMailTransport($apiKey);
        });
    }
}
