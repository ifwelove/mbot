<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TelegramService;

class TelegramServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('telegram', function ($app) {
            return new TelegramService();
        });
    }

    public function boot()
    {
        //
    }
}
